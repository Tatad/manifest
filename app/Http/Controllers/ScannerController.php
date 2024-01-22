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

class ScannerController extends Controller
{
    public function index(){
        return Inertia::render('Scanner');
    }

    public function scan(Request $request){
        $input = $request->all();

        $file = new Filesystem;
        $storagePathToClear = storage_path('app/images/');
        $file->cleanDirectory($storagePathToClear);

        $imagePath = $request->file('image')->store('/images');
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        
        $storagePath = storage_path('app/images/'.$filename);
        $filename = substr($imagePath, strpos($imagePath, "/") + 1);
        exec('C:\\"Program Files (x86)"\\ZBar\\bin\\zbarimg -S enable '.$storagePath, $result);
        if(collect($result)->isNotEmpty()){
            foreach($result as $data){
                $code = explode(":", $data);
                if($code[0] == "UPC-A"){
                    $item = ltrim(substr($data, strpos($data, ":") + 1),'0');
                    //dd($item);
                    $scannedItem = ScannedItem::where('item', $item)->first();
                    if(collect($scannedItem)->isEmpty()){
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
        $input = $request->all();

        // $item = UpcCode::where(['item' => $input['item'], 'upc_code' => $input['upc_code']])->first();
        // if(collect($item)->isEmpty()){
        //     $newItem = new UpcCode();
        //     $newItem->item = $input['upc_code'];
        //     $newItem->upc_code = $input['upc_code'];
        //     $newItem->save();
        // }

        $scannedItem = ScannedItem::where('upc_code', $input['upc_code'])->update(['item' => $input['item']]);
        return 'success';

    }

    public function lookup(){
        return Inertia::render('Lookup'); 
    }

    public function lookupItem(Request $request){
        $input = $request->all();
        $item = Manifest::where('item', $input['item'])->get();
        //dd($item);
        //return ['data' => $item];
        return Inertia::render('Lookup', [
            'data' => $item
        ]); 
    }
}
