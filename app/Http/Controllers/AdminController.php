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
use App\Models\ScannedItem;
use App\Models\PalletItem;
use App\Models\PalletDownload;
use App\Models\UpcCode;
use App\Imports\ManifestImport;
use App\Imports\PalletImport;
use App\Exports\ManifestExport;
use DB;
use Carbon\Carbon;
use Dcblogdev\Dropbox\Facades\Dropbox;
use Illuminate\Filesystem\Filesystem;

class AdminController extends Controller
{
    public function index(){
        $manifests = PalletItem::with('manifest','pallet_download')->get();
        $manifestData = collect($manifests)->map(function ($data){
            if(($data['manifest']) && !isset($data['pallet_download'])){
                return [
                    'item' => $data->manifest->item,
                    'quantity' => $data->quantity, 
                    'id' => $data->id, 
                    'dataId' => $data->id, 
                    'pallet' => $data->pallet_number,
                    'description' => $data->manifest->description,
                    'msrp' => $data->manifest->msrp,
                    'features' => $data->manifest->features,
                    'item_name' => $data->manifest->item_name,
                    'images' => ($data->manifest->images != 'not_available') ? json_decode($data->manifest->images,true) : $data->manifest->images,
                    // 'images' => ($data->manifest->type && $data->manifest->type == "Mixed") ? ["https://images.costco-static.com/ImageDelivery/imageService?profileId=12026539&itemId=".$data->manifest->item."-894"] : json_decode($data->manifest->images,true),
                    'costcoUrl' => $data->manifest->item_name,
                    'totalMsrp' => round(($data->quantity * $data->manifest->msrp),2),
                    'type' => $data->manifest->type,
                    'retail_price' => $data->manifest->retail_price,
                    'upc_code' => collect($data->manifest->upcCodes)->isNOtEmpty() ? $data->manifest->upcCodes->pluck('upc_code') : 0,
                    'created_at' => $data->manifest->created_at
                ];
            }
        });
        
        //get available pallets
        $pallets = collect($manifests)->groupBy('pallet_number')->keys();
        $palletArray = [];
        foreach($pallets as $pallet){
            $palletArray[] = ['pallet' => $pallet];
        }

        $scannedItems = ScannedItem::all();
        $upcCodes = UpcCode::all();

        return Inertia::render('Admin', [
            'manifests' => collect($manifestData)->sortByDesc('created_at')->filter()->values()->toArray(),
            'scannedItems' => $scannedItems,
            'upcCodes' => $upcCodes,
            'pallets' => $palletArray
        ]); 
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'item_number' => 'unique:pallet_items|required|integer|min:8',
            'description' => 'required',
            'msrp' => 'required',
            'retail_price' => 'required'
        ]);

        $input = $request->all();

        //dd($input);
        $manifestCheck = Manifest::where('item', $input['item_number'])->first();

        $imageURLArray = [];
        if(collect($manifestCheck)->isEmpty()){

            $manifest = new Manifest();
            $manifest->item = $input['item_number'];
            $manifest->description = $input['description'];
            $manifest->item_name = $input['description'];
            $manifest->quantity = $input['quantity'];
            $manifest->msrp = $input['msrp'];
            $manifest->retail_price = $input['retail_price'];
            $manifest->total = $input['msrp'];
            $manifest->type = $input['type'];
            if($input['image']){
                $file = new Filesystem;
                $storagePathToClear = storage_path('app/images/');
                $file->cleanDirectory($storagePathToClear);

                foreach($request->file('image') as $image){
                    $imagePath = $image->store('/images');
                    $filename = substr($imagePath, strpos($imagePath, "/") + 1);
                    
                    $storagePath = storage_path('app/images/'.$filename);
                    $filename = substr($imagePath, strpos($imagePath, "/") + 1);

                    $dropboxImage = Dropbox::files()->upload($path = '', $storagePath);
                    $decodedImage = json_decode($dropboxImage,true);
                    $imagePath = $decodedImage['path_display'];

                    $token = DB::table('dropbox_tokens')->first();

                    $adapter = \Storage::disk('dropbox')->getAdapter();
                    $client = $adapter->getClient();
                    $client->setAccessToken($token->access_token);

                    $link = $client->createSharedLinkWithSettings($imagePath);
                    $imageURLArray[] = $link['url'].'&raw=1';
                    //$manifest->images = json_encode([$link['url'].'&raw=1']);
                }
            }

            $manifest->images = json_encode($imageURLArray);
            $manifest->save();

            if(collect($input['upc_code'])->isNotEmpty()){
                foreach($input['upc_code'] as $key => $upc){
                    $upc_code = new UpcCode();
                    $upc_code->item = $input['item_number'];
                    $upc_code->upc_code = $upc;
                    $upc_code->save();
                }
            }

            $palletItem = new PalletItem();
            $palletItem->item_number = $input['item_number'];
            $palletItem->pallet_number = $input['pallet'];
            $palletItem->quantity = $input['quantity'];
            $palletItem->save();
        }

        return Redirect::route('admin');
    }

    public function editItem(Request $request)
    {
        $request->validate([
            'item_number' => 'required|integer|min:8',
            'description' => 'required',
            'msrp' => 'required',
            'retail_price' => 'required'
        ]);

        $input = $request->all();
        //dd($input);
        $imageURLArray = [];
        $manifest = Manifest::where('item', $input['item_number'])->first();
        $manifest->item = $input['item_number'];
        $manifest->description = $input['description'];
        $manifest->item_name = $input['description'];
        $manifest->quantity = $input['quantity'];
        $manifest->msrp = $input['msrp'];
        $manifest->retail_price = $input['retail_price'];
        $manifest->total = $input['msrp'];
        $manifest->type = $input['type'];

        if($input['image']){
            $file = new Filesystem;
            $storagePathToClear = storage_path('app/images/');
            $file->cleanDirectory($storagePathToClear);

            foreach($request->file('image') as $image){
                $imagePath = $image->store('/images');
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);
                
                $storagePath = storage_path('app/images/'.$filename);
                $filename = substr($imagePath, strpos($imagePath, "/") + 1);

                $dropboxImage = Dropbox::files()->upload($path = '', $storagePath);
                $decodedImage = json_decode($dropboxImage,true);
                $imagePath = $decodedImage['path_display'];

                $token = DB::table('dropbox_tokens')->first();

                $adapter = \Storage::disk('dropbox')->getAdapter();
                $client = $adapter->getClient();
                $client->setAccessToken($token->access_token);

                $link = $client->createSharedLinkWithSettings($imagePath);
                $imageURLArray[] = $link['url'].'&raw=1';
                //$manifest->images = json_encode([$link['url'].'&raw=1']);
            }
        }else{
            $imageURLArray = json_decode($manifest->images,true);
        }

        $manifest->images = json_encode($imageURLArray);
        $manifest->save();

        UpcCode::where(['item' => $input['item_number']])->delete();
        if(collect($input['upc_code'])->isNotEmpty()){
            foreach($input['upc_code'] as $key => $upc){
                $upc_code = new UpcCode();
                $upc_code->item = $input['item_number'];
                $upc_code->upc_code = $upc;
                $upc_code->save();
            }
        }

        $palletItem = PalletItem::where('item_number',$input['item_number'])->first();
        $palletItem->item_number = $input['item_number'];
        $palletItem->pallet_number = $input['pallet'];
        $palletItem->quantity = ($palletItem->quantity + $input['quantity']);
        $palletItem->save();

        return Redirect::route('admin');
    }

    public function deleteItem(Request $request)
    {
        $input = $request->all();
        $manifest = Manifest::where('item', $input['item_number'])->delete();
        $upcCode = UpcCode::where('item', $input['item_number'])->delete();
        $palletItem = PalletItem::where('item_number', $input['item_number'])->delete();
        return Redirect::route('admin');
    }
}

