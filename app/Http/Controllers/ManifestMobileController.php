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
use App\Models\PalletItem;
use App\Models\PalletDownload;
use App\Imports\ManifestImport;
use App\Imports\PalletImport;
use App\Exports\ManifestExport;
use DB;
use Carbon\Carbon;

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
}

