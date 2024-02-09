<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManifestUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\PdfToText\Pdf;
use App\Models\Manifest;
use App\Models\UpcCode;
use App\Models\ScannedItem;
use DB;
use Carbon\Carbon;
use Dcblogdev\Dropbox\Facades\Dropbox;
use Illuminate\Filesystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Illuminate\Validation\Rules;

class ScannerController extends Controller
{
    public function index(){
        return Inertia::render('Scanner');
    }

    public function scanUpcCode(Request $request){
        $input = $request->all();

        $file = new Filesystem;
        $storagePathToClear = storage_path('app/images/');
        $file->cleanDirectory($storagePathToClear);
        $imagePath = $request->file('image')->store('/images');
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        
        $storagePath = storage_path('app/images/'.$filename);
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        //exec('C:\\"Program Files (x86)"\\ZBar\\bin\\zbarimg -S enable '.$storagePath, $result);
        exec('zbarimg -S enable '.$storagePath, $result);

        if(collect($result)->isNotEmpty()){
            foreach($result as $data){
                $code = explode(":", $data);
                if($code[0] == "UPC-A"){
                    $code = substr($data, strpos($data, ":") + 1);
                }elseif($code[0] == "EAN-13"){
                    $code = substr($data, strpos($data, ":") + 1);
                }
            }
        }
        return Inertia::render('UPCLookup',['code' => $code]); 
        // dd($code);
    }

    public function scan(Request $request){
        $input = $request->all();
        //dd($input);
        $file = new Filesystem;
        $storagePathToClear = storage_path('app/images/');
        $file->cleanDirectory($storagePathToClear);

        $imagePath = $request->file('image')->store('/images');
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        
        $storagePath = storage_path('app/images/'.$filename);
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        //exec('C:\\"Program Files (x86)"\\ZBar\\bin\\zbarimg -S enable '.$storagePath, $result);
        exec('zbarimg -S enable '.$storagePath, $result);
        //dd($result);
        if(collect($result)->isNotEmpty()){
            foreach($result as $data){
                $code = explode(":", $data);
                if($code[0] == "UPC-A"){
                    $item = substr($data, strpos($data, ":") + 1);
                    
                    $scannedItem = ScannedItem::where('upc_code', $item)->first();
                    $upc_code = UpcCode::where('upc_code', $item)->first();

                    if(collect($scannedItem)->isEmpty() && collect($upc_code)->isEmpty()){
                        $image = Dropbox::files()->upload($path = '', $storagePath);
                        $decodedImage = json_decode($image,true);
                        $imagePath = $decodedImage['path_display'];

                        $token = DB::table('dropbox_tokens')->first();

                        $adapter = \Storage::disk('dropbox')->getAdapter();
                        $client = $adapter->getClient();
                        $client->setAccessToken($token->access_token);

                        $link = $client->createSharedLinkWithSettings($imagePath);
                        $decodedImage = json_decode($image,true);
                        $newItem = new ScannedItem();
                        $newItem->item = null;
                        $newItem->upc_code = $item;
                        $newItem->image_name = $link['url'].'&raw=1';
                        $newItem->save();
                        return Inertia::render('Scanner',['message' => 'UPC code successfully added.', 'status' => 'success']);
                    } else {
                        return Inertia::render('Scanner',['message' => 'UPC code already exists.', 'status' => 'info']);
                    }
                }elseif($code[0] == "EAN-13"){
                    $item = substr($data, strpos($data, ":") + 1);
                    
                    $scannedItem = ScannedItem::where('upc_code', $item)->first();
                    $upc_code = UpcCode::where('upc_code', $item)->first();

                    if(collect($scannedItem)->isEmpty() && collect($upc_code)->isEmpty()){
                        $image = Dropbox::files()->upload($path = '', $storagePath);
                        $decodedImage = json_decode($image,true);
                        $imagePath = $decodedImage['path_display'];

                        $token = DB::table('dropbox_tokens')->first();

                        $adapter = \Storage::disk('dropbox')->getAdapter();
                        $client = $adapter->getClient();
                        $client->setAccessToken($token->access_token);

                        $link = $client->createSharedLinkWithSettings($imagePath);
                        $decodedImage = json_decode($image,true);
                        $newItem = new ScannedItem();
                        $newItem->item = null;
                        $newItem->upc_code = $item;
                        $newItem->image_name = $link['url'].'&raw=1';
                        $newItem->save();
                        return Inertia::render('Scanner',['message' => 'UPC code successfully added.', 'status' => 'success']);
                    } else {
                        return Inertia::render('Scanner',['message' => 'UPC code already exists.', 'status' => 'info']);
                    }
                }
                
            }
        }
        return Inertia::render('Scanner');
    }

    public function scannedList(){
        $lists = ScannedItem::all();

        return Inertia::render('ScannedList', [
            'list'  => $lists
        ]); 
    }

    public function addItemNumber(Request $request){
        // $request->validate([
        //     'item' => 'required|min:8'
        // ]);

        $input = $request->all();
        $item = Manifest::with('palletItems')->where('item', $input['item'])->first();

        if(collect($item)->isEmpty()){
            $item = $input;
            $item['isEmpty'] = 1;
            
        }else{
            $item['isEmpty'] = 0;
            $item['scrapingbee'] = 0;
        }
        //return $item;
        $lists = ScannedItem::all();

        return Inertia::render('ScannedList', [
            'data' => $item,
            'list'  => $lists
        ]); 

    }

    public function lookup(){
        return Inertia::render('Lookup'); 
    }

    public function UpcLookup(){
        return Inertia::render('UPCLookup'); 
    }

    public function lookupUpcCode(Request $request){
        $input = $request->all();
        //$manifest = Manifest::with('upc')->where('item', $input['item'])->first();
        $item = UpcCode::with('manifest')->where('upc_code', $input['upc_code'])->first();
            
        if(collect($item)->isNotEmpty()){
            $item['upc_code'] = $item['upc_code'];
            $item['item'] = $input['item'];
            $item['manifest'] = $item['manifest'];
            $item['isEmpty'] = 0;
        }else{
            $item = $input;
            $item['isEmpty'] = 1;
        }
        
        return Inertia::render('UPCLookup', [
            'data' => $item
        ]); 
    }

    public function lookupItem(Request $request){
        $input = $request->all();
        $item = Manifest::with('upc')->where('item', $input['item'])->first();
        
        if(collect($item)->isNotEmpty()){
            $item['upc_code'] = isset($item['upc']['upc_code']) ? $item['upc']['upc_code'] : '';
            $item['item'] = $input['item'];
            $item['isEmpty'] = 0;
        }else{
            $item = $input;
            $item['isEmpty'] = 1;
        }
        //dd($item);
        return Inertia::render('Lookup', [
            'data' => $item
        ]); 
    }

    public function addItemViaScannedList(Request $request){
        $request->validate([
            //'item' => 'required|integer|min:8',
            'upc_code' => 'required'
        ]);

        $input = $request->all();

        $manifestCheck = Manifest::where('item', $input['item'])->first();

        if(collect($manifestCheck)->isEmpty()){
            $manifest = new Manifest();
            $manifest->item = $input['item'];
            $manifest->description = $input['description'];
            $manifest->item_name = $input['description'];
            $manifest->quantity = 1;
            $manifest->msrp = $input['msrp'];
            $manifest->retail_price = $input['retail_price'];
            $manifest->total = $input['msrp'];
            $manifest->quantity = 1;
            $manifest->type = $input['type'];

            $manifest->save();

            $upcCodeCheck = UpcCode::where(['upc_code' => $input['upc_code'], 'item' => $input['item']])->first();
            if(collect($upcCodeCheck)->isEmpty()){
                $upc_code = new UpcCode();
                $upc_code->item = $input['item'];
                $upc_code->upc_code = $input['upc_code'];
                $upc_code->save();
            }else{
                $upcCodeCheck->item = $input['item'];
                $upcCodeCheck->upc_code = $input['upc_code'];
                $upcCodeCheck->save();
            }
        }else{
            $upcCodeCheck = UpcCode::where(['upc_code' => $input['upc_code'], 'item' => $input['item']])->first();
            //dd($upcCodeCheck);
            if(collect($upcCodeCheck)->isEmpty()){
                $upc_code = new UpcCode();
                $upc_code->item = $input['item'];
                $upc_code->upc_code = $input['upc_code'];
                $upc_code->save();
            }else{
                $upcCodeCheck->item = $input['item'];
                $upcCodeCheck->upc_code = $input['upc_code'];
                $upcCodeCheck->save();
            }

            // $manifestCheck->item = $input['item'];
            // $manifestCheck->description = $input['description'];
            // $manifestCheck->item_name = $input['description'];
            // $manifestCheck->quantity = 1;
            // $manifestCheck->msrp = $input['msrp'];
            // $manifestCheck->retail_price = $input['retail_price'];
            // $manifestCheck->total = $input['msrp'];
            // $manifestCheck->quantity = 1;
            // $manifestCheck->type = $input['type'];
            // $manifestCheck->save();
        }

        $scannedItem = ScannedItem::where('upc_code', $input['upc_code'])->delete();
        //return Inertia::render('ScannedList'); 
        $lists = ScannedItem::all();

        return Inertia::render('ScannedList', [
            'data' => [],
            'list'  => $lists
        ]); 
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'item' => 'required|integer|min:8',
            'description' => 'required',
            'msrp' => 'required',
            'retail_price' => 'required'
        ]);
        $input = $request->all();

        $manifestCheck = Manifest::where('item', $input['item'])->first();

        if(collect($manifestCheck)->isEmpty()){
            $manifest = new Manifest();
            $manifest->item = $input['item'];
            $manifest->description = $input['description'];
            $manifest->item_name = $input['description'];
            $manifest->quantity = 1;
            $manifest->msrp = $input['msrp'];
            $manifest->retail_price = $input['retail_price'];
            $manifest->total = $input['msrp'];
            $manifest->quantity = 1;
            $manifest->type = $input['type'];
            if($input['image']){
                $file = new Filesystem;
                $storagePathToClear = storage_path('app/images/');
                $file->cleanDirectory($storagePathToClear);

                $imagePath = $request->file('image')->store('/images');
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);
                
                $storagePath = storage_path('app/images/'.$filename);
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);

                $image = Dropbox::files()->upload($path = '', $storagePath);
                $decodedImage = json_decode($image,true);
                $imagePath = $decodedImage['path_display'];

                $token = DB::table('dropbox_tokens')->first();

                $adapter = \Storage::disk('dropbox')->getAdapter();
                $client = $adapter->getClient();
                $client->setAccessToken($token->access_token);

                $link = $client->createSharedLinkWithSettings($imagePath);

                $manifest->images = json_encode([$link['url'].'&raw=1']);
            }
            $manifest->save();

            $upcCodeCheck = UpcCode::where(['upc_code' => $input['upc_code'], 'item' => $input['item']])->first();
            if(collect($upcCodeCheck)->isEmpty()){
                $upc_code = new UpcCode();
                $upc_code->item = $input['item'];
                $upc_code->upc_code = $input['upc_code'];
                $upc_code->save();
            }else{
                $upcCodeCheck->item = $input['item'];
                $upcCodeCheck->upc_code = $input['upc_code'];
                $upcCodeCheck->save();
            }
        }else{
            $upcCodeCheck = UpcCode::where(['upc_code' => $input['upc_code']])->first();

            if(collect($upcCodeCheck)->isEmpty()){
                $upc_code = new UpcCode();
                $upc_code->item = $input['item'];
                $upc_code->upc_code = $input['upc_code'];
                $upc_code->save();
            }else{
                $upcCodeCheck->item = $input['item'];
                $upcCodeCheck->upc_code = $input['upc_code'];
                $upcCodeCheck->save();
            }

            $manifestCheck->item = $input['item'];
            $manifestCheck->description = $input['description'];
            $manifestCheck->item_name = $input['description'];
            $manifestCheck->quantity = 1;
            $manifestCheck->msrp = $input['msrp'];
            $manifestCheck->retail_price = $input['retail_price'];
            $manifestCheck->total = $input['msrp'];
            $manifestCheck->quantity = 1;
            $manifestCheck->type = $input['type'];
            if($input['image']){
                $file = new Filesystem;
                $storagePathToClear = storage_path('app/images/');
                $file->cleanDirectory($storagePathToClear);

                $imagePath = $request->file('image')->store('/images');
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);
                
                $storagePath = storage_path('app/images/'.$filename);
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);

                $image = Dropbox::files()->upload($path = '', $storagePath);
                $decodedImage = json_decode($image,true);
                $imagePath = $decodedImage['path_display'];

                $token = DB::table('dropbox_tokens')->first();

                $adapter = \Storage::disk('dropbox')->getAdapter();
                $client = $adapter->getClient();
                $client->setAccessToken($token->access_token);

                $link = $client->createSharedLinkWithSettings($imagePath);
                
                $manifestCheck->images = json_encode([$link['url'].'&raw=1']);
            }
            $manifestCheck->save();
        }

        $scannedItem = ScannedItem::where('upc_code', $input['upc_code'])->delete();

        // $lists = ScannedItem::all();

        // return Inertia::render('ScannedList', [
        //     'list'  => $lists
        // ]); 

        if($input['scanMethod'] == 'item'){
            return Inertia::render('Lookup'); 
        }

        if($input['scanMethod'] == 'upc'){
            return Inertia::render('UPCLookup'); 
        }
    }
}
