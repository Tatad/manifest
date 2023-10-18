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
        ini_set('max_execution_time', 2380);
        // get cURL resource
        $ch = curl_init();
        //$ids = [1660896, 2127647, 1596898,1691718 ,1635441, 2349196, 1891815, 1586356, 619899, 1640734, 2622054, 1627201, 845993, 3272378, 3272379];
        $ids = Manifest::where('item_name', '=', null)->take(15)->get()->groupBy('item');
        
        //dd($ids);
        //$ids = [1660896];
        $results = [];
        foreach($ids as $key => $val){
            // set url
            $extract_rules = urlencode('{
                "title" : {
                    "selector": "h1",
                    "output": "text"
                },
                "images":{
                    "selector":"#productImage",
                    "output":{
                        "src":"img@src",
                    }
                }
            }');

            curl_setopt($ch, CURLOPT_URL, 'https://app.scrapingbee.com/api/v1/?api_key=ACZW8WWZ3SNFB1DYK7RD31I381GEDUXEGHMRBQZC1X90CVLO5H8X2KAT4PI92I1W8TNDAUKCINXLTHHB&url=https://www.costco.ca/CatalogSearch?keyword='.$key.'&extract_rules=' . $extract_rules);

            // set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



            // send the request and save response to $response
            $response = curl_exec($ch);

            // stop if fails
            if (!$response) {
              die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            }

            echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
            echo 'Response Body: ' . $response . PHP_EOL;
            $res = json_decode($response,true);
            
            if($res['title'] != ""){
                Manifest::where('item', $key)->update(['item_name' => $res['title'], 'images' => $res['images']['src']]);
            }else{
                Manifest::where('item', $key)->update(['item_name' => 'not_available', 'images' => 'not_available']);
            }
            $results[] = $res;
            // close curl resource to free up system resources
        }
        curl_close($ch);
        dd($results);
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

