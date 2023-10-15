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
use App\Imports\ManifestImport;
use App\Exports\ManifestExport;

class ManifestController extends Controller
{
    /**
     * Read Manifest PDF file
     */
    public function read(Request $request)
    {
        $file =  base_path().'\manifests.csv';
        //dd($file);
        \Excel::import(new ManifestImport, 'manifest.csv');

        return '123';
    }

    public function updload(Request $request){
        $input = $request->all();

        \Excel::import(new ManifestImport, $input['file']);

        return Redirect::route('manifest');
    }

    public function manifest(Request $request){
        //$manifests = Manifest::all()->groupBy('pallet');
        $manifests = Manifest::where(['status' => 0])->get()->groupBy('pallet');
        //dd($manifests);
        $manifestData = collect($manifests)->map(function ($data){
            //$collectedGroupByData = collect($data)->groupBy('item')->map->count();
            $collectedData = collect($data)->groupBy('item')->map(function($d){
                $count = collect($d)->groupBy('item')->map->count()->values()->first();
                $itemData = ($d)->first();
                return [
                    'item' => $itemData->item,
                    'quantity' => $count, 
                    'id' => $itemData->id, 
                    'pallet' => $itemData->pallet,
                    'description' => $itemData->description,
                    'msrp' => $itemData->msrp,
                    'features' => $itemData->features,
                    'images' => json_decode($itemData->images,true)
                ]; 
            });
            // $mergedData = array_merge_recursive($collectedData,$collectedGroupByData);
            return collect($collectedData)->values();
        })->values()->toArray();
        $newArray = call_user_func_array('array_merge', $manifestData);
        //dd($newArray);
        return Inertia::render('Manifest', [
            'manifests' => collect($newArray)->values()->toArray()
        ]);
    }

    public function update(ManifestUpdateRequest $request): RedirectResponse
    {
        $input = $request->all();
        $manifests = Manifest::where('item', $input['item'])->get();

        foreach($manifests as $key => $manifest){
            $manifest->images = json_encode($input['images']);
            $manifest->features = json_encode($input['features']);
            $manifest->save();
        }
        
        return Redirect::route('manifest');
    }

    public function send(Request $request){
        $input = $request->all();

        $manifest = Manifest::whereIn('item', $input['selected'])->update(['status' => 1]);
        $export = new ManifestExport(
            $input['selected']
        );

        return \Excel::download($export, 'manifest.xlsx');
    }
}

