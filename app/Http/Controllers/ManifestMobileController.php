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
use App\Imports\ManifestImport;
use App\Imports\PalletImport;
use App\Exports\ManifestExport;
use DB;
use Carbon\Carbon;
use Dcblogdev\Dropbox\Facades\Dropbox;
use Illuminate\Filesystem\Filesystem;

class ManifestMobileController extends Controller
{
    public function add(Request $request){
        $input = $request->all();

        //return $input['image'];
        $manifest = Manifest::where('item')->first();
        if(collect($manifest)->isNOtEmpty()){
            $manifest->upc_code = $input['upc_code'];
            $manifest->msrp = $input['msrp'];
            $manifest->description = ($input['description'] && $input['description'] != "") ? $input['description'] : $maniest->description;
            $manifest->images = $input['image'];
            $manifest->save();
        }else{
            $manifest = new Manifest();
            $manifest->item = $input['item'];
            $manifest->upc_code = $input['upc_code'];
            $manifest->pallet = $input['pallet'];
            $manifest->msrp = $input['msrp'];
            $manifest->total = $input['msrp'];
            $manifest->pallet = $input['pallet'];
            $manifest->description = ($input['description'] && $input['description'] != "") ? $input['description'] : $maniest->description;
            $manifest->images = $input['image'];
            $manifest->quantity = 1;
            $manifest->save();
        }
        return $manifest;
        dd($input);
    }

    public function sendUpc(Request $request){
        $image = public_path('/images/barcode_test.jpg');
        $zbar = new \TarfinLabs\ZbarPhp\Zbar($imagePath);
        dd($zbar);
        // $file = new Filesystem;
        // $storagePathToClear = storage_path('app/images/');
        // $file->cleanDirectory($storagePathToClear);

        // $input = $request->all();
        // $path = $request->file('image')->store('/images');
        // $filename = substr($path, strpos($path, "/") + 1);

        // $storagePath = storage_path('app/images/'.$filename);
        // $result = Dropbox::files()->upload($path = '/scanned_images', $storagePath);

        // exec('C:\\"Program Files (x86)"\\ZBar\\bin\\zbarimg -q '.$storagePath, $result);

        // dd($result);
        // return 'success';
        // dd($storagePath);
        // $input = $request->all();
        // //dd($file);
        // $image = public_path('/images/barcode_test.jpg');

        // // $file = \File::get($image);
        // dd($input);

        // $result = Dropbox::files()->upload($path = '/scanned_images', $image);
        // dd($result);
        // $data = new ScannedItem();
        // $data->upc_code = $input['upc_code'];
        // $data->save();
    }
}

