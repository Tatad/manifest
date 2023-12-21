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
use DB;
use Carbon\Carbon;

class ManifestController extends Controller
{
    public function add(Request $request){
        $input = $request->all();
        return 'success';
        dd($input);
    }
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
        $manifests = Manifest::where(['status' => 0])->get()->groupBy('pallet');
        $manifestData = collect($manifests)->map(function ($data){
            $collectedData = collect($data)->groupBy('item')->map(function($d){
                $count = collect($d)->groupBy('item')->map->count()->values()->first();
                $itemData = ($d)->first();
                return [
                    'item' => $itemData->item,
                    'quantity' => $count, 
                    'id' => $itemData->id, 
                    'dataId' => $itemData->id, 
                    'pallet' => $itemData->pallet,
                    'description' => $itemData->description,
                    'msrp' => $itemData->msrp,
                    'features' => $itemData->features,
                    'item_name' => $itemData->item_name,
                    'images' => ($itemData->images != 'not_available') ? json_decode($itemData->images,true) : $itemData->images,
                    'costcoUrl' => $itemData->item_name,
                    'totalMsrp' => round(($count * $itemData->msrp),2)
                ]; 
            });
            return collect($collectedData)->values();
        })->values()->toArray();
        $newArray = call_user_func_array('array_merge', $manifestData);

        //get available pallets
        $pallets = DB::table('manifests')
                 ->select('pallet')
                 ->where('status','=',0)
                 ->groupBy('pallet')
                 ->get();

        return Inertia::render('Manifest', [
            'manifests' => collect($newArray)->values()->toArray(),
            'pallets'   => collect($pallets)->filter()->values()->toArray()
        ]);
    }

    public function manifestGroupedView(){
        $manifests = Manifest::where(['status' => 0])->get()->groupBy('pallet');

        $manifestData = [];
        foreach(collect($manifests)->values()->toArray() as $data){
            $sum = 0;
            foreach($data as $val){
                $sum += $val['total'];
            }
            $collectedData = collect($data)->groupBy('item')->map(function($d){
                $count = collect($d)->groupBy('item')->map->count()->values()->first();
                $itemData = ($d)->first();
                return [
                    'item' => $itemData['item'],
                    'quantity' => $count, 
                    'id' => $itemData['id'], 
                    'dataId' => $itemData['id'], 
                    'pallet' => $itemData['pallet'],
                    'description' => $itemData['description'],
                    'msrp' => $itemData['msrp'],
                    'features' => $itemData['features'],
                    'item_name' => $itemData['item_name'],
                    'images' => ($itemData['images'] != 'not_available') ? json_decode($itemData['images'],true) : $itemData['images'],
                    'costcoUrl' => $itemData['item_name'],
                    'total' => round(($count * $itemData['msrp']),2)
                ]; 
            })->values()->toArray();

            $manifestData[] = [
                'group_id' => $data[0]['download_group_id'],
                'downloaded_at' => $data[0]['downloaded_at'],
                'pallet' => $data[0]['pallet'],
                'group_name' => $data[0]['group_name'],
                'sum' => $sum,
                'data' => ($collectedData)
            ];
        }

        return Inertia::render('ManifestGroupedView', [
            //'manifests' => collect($newArray)->values()->toArray(),
            'manifests'  => collect($manifestData)->values()->toArray()
        ]);
    }

    public function manifestSent(Request $request){
        $manifests = Manifest::where(['status' => 1])->get()->groupBy('download_group_id');

        $manifestData = [];
        foreach(collect($manifests)->values()->toArray() as $data){
            $sum = 0;
            foreach($data as $val){
                $sum += $val['total'];
            }
            $collectedData = collect($data)->groupBy('item')->map(function($d){
                $count = collect($d)->groupBy('item')->map->count()->values()->first();
                $itemData = ($d)->first();
                return [
                    'item' => $itemData['item'],
                    'quantity' => $count, 
                    'id' => $itemData['id'], 
                    'dataId' => $itemData['id'], 
                    'pallet' => $itemData['pallet'],
                    'description' => $itemData['description'],
                    'msrp' => $itemData['msrp'],
                    'features' => $itemData['features'],
                    'item_name' => $itemData['item_name'],
                    'images' => ($itemData['images'] != 'not_available') ? json_decode($itemData['images'],true) : $itemData['images'],
                    'costcoUrl' => $itemData['item_name'],
                    'total' => round(($count * $itemData['msrp']),2)
                ]; 
            })->values()->toArray();

            $manifestData[] = [
                'group_id' => $data[0]['download_group_id'],
                'downloaded_at' => $data[0]['downloaded_at'],
                'group_name' => $data[0]['group_name'],
                'sum' => $sum,
                'data' => ($collectedData)
            ];
        }

        return Inertia::render('ManifestSent', [
            //'manifests' => collect($newArray)->values()->toArray(),
            'manifests'  => collect($manifestData)->values()->toArray()
        ]);
    }

    public function pdfManifest(Request $request){
        $input = $request->all()['form'];
        $downloadId = DB::table('manifests')->latest('download_group_id')->first();

        $items = [];
        $pallets = [];
        foreach($input['selected'] as $key => $item){
            $items[] = $item['item'];
            $pallets[] = $item['pallet'];
        }
        $manifests = Manifest::whereIn('item', $items)->whereIn('pallet', $pallets)->get()->groupBy('pallet');
        $manifestData = collect($manifests)->map(function ($data){
            //dd(collect($data)->groupBy('item'));
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
                    'item_name' => $itemData->item_name,
                    'images' => $itemData->images,
                    'costcoUrl' => $itemData->item_name,
                    'totalMsrp' => round(($count * $itemData->msrp),2)
                ]; 
            });
            return collect($collectedData)->values();
        })->values()->toArray();
        $newArray = call_user_func_array('array_merge', $manifestData);
        $pdf = \Pdf::loadView('pdf.download', ['data' => collect($newArray)->values()->toArray()]);

        //update manifest
        foreach($input['selected'] as $selected){
            $manifest = Manifest::where(['item' => $selected['item'], 'pallet' => $selected['pallet']])->update(['status' => 1, 'downloaded_at' => Carbon::now() ,'download_group_id' => ($downloadId->download_group_id + 1)]);
        }

        return $pdf->stream('download.pdf');
    }

    public function update(ManifestUpdateRequest $request): RedirectResponse
    {
        $input = $request->all();
        //dd($input);
        $manifests = Manifest::where('item', $input['item'])->get();

        foreach($manifests as $key => $manifest){
            $manifest->description = $input['description'];
            $manifest->images = json_encode($input['images']);
            $manifest->features = ($input['features']);
            $manifest->save();
        }
        
        return Redirect::route('manifest');
    }

    public function send(Request $request){
        $input = $request->all();
       
        $downloadId = DB::table('manifests')->latest('download_group_id')->first();
        foreach($input['selected'] as $selected){
            $manifest = Manifest::where(['item' => $selected['item'], 'pallet' => $selected['pallet']])->update(['status' => 1, 'downloaded_at' => Carbon::now(), 'download_group_id' => ($downloadId->download_group_id + 1)]);
        }

        $items = [];
        $pallets = [];
        foreach($input['selected'] as $key => $item){
            $items[] = $item['item'];
            $pallets[] = $item['pallet'];
        }
        $manifests = Manifest::whereIn('item', $items)->whereIn('pallet', $pallets)->get()->groupBy('pallet');

        $export = new ManifestExport(
            $items,
            $pallets
        );

        return \Excel::download($export, 'manifest.csv', \Maatwebsite\Excel\Excel::CSV, [
          'Content-Type' => 'text/csv',
        ]);
    }

    public function batchDownloadCsv(Request $request){
        $input = $request->all();
        $manifests = Manifest::where('download_group_id', $input['selected'])->get();

        $results = DB::table('manifests')
                 ->select('item','pallet')
                 ->where('download_group_id','=',$input['selected'])
                 ->groupBy('pallet')
                 ->get();
        

        $items = [];
        $pallets = [];
        foreach($manifests as $key => $item){
            $items[] = $item['item'];
            $pallets[] = $item['pallet'];
        }

        $export = new ManifestExport(
            $items,
            $pallets
        );

        return \Excel::download($export, 'manifest.csv');
    }

    public function batchDownloadPdf(Request $request){
        $input = $request->all();
        $downloadId = DB::table('manifests')->latest('download_group_id')->first();

        $items = [];
        $pallets = [];

        $manifests = Manifest::where('download_group_id', $input['selected'])->get()->groupBy('pallet');
        $manifestData = collect($manifests)->map(function ($data){
            //dd(collect($data)->groupBy('item'));
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
                    'item_name' => $itemData->item_name,
                    'images' => $itemData->images,
                    'costcoUrl' => $itemData->item_name,
                    'totalMsrp' => round(($count * $itemData->msrp),2)
                ]; 
            });
            return collect($collectedData)->values();
        })->values()->toArray();
        $newArray = call_user_func_array('array_merge', $manifestData);
        $pdf = \Pdf::loadView('pdf.download', ['data' => collect($newArray)->values()->toArray()]);

        return $pdf->stream('download.pdf');
    }

    public function restore(Request $request){
        $input = $request->all();
        //dd($input);
        $manifest = Manifest::where('download_group_id', $input['selected'])->update(['status' => 0]);
        return 'success';
    }

    public function pallets(){
        $manifests = DB::table('manifests')
                 ->select('pallet')
                 ->where('status','=',0)
                 ->groupBy('pallet')
                 ->get();
        return collect($manifests)->filter()->values()->toArray();
        $manifestData = collect($manifests)->map(function ($data){
            $collectedData = collect($data)->groupBy('item')->map(function($d){
                $count = collect($d)->groupBy('item')->map->count()->values()->first();
                $itemData = ($d)->first();
                return [
                    'pallet' => $itemData->pallet
                ]; 
            });
            return collect($collectedData)->values();
        })->values()->toArray();
        $newArray = call_user_func_array('array_merge', $manifestData);
        return Inertia::render('ManifestSent', [
            'manifests' => collect($newArray)->values()->toArray()
        ]);
    }

    public function addManifestName(Request $request){
        $input = $request->all();
        //dd($input);
        if($input['download_group_id']){
            Manifest::where('download_group_id', $input['download_group_id'])->update(['group_name' => $input['group_name']]);
        }
        return 'success';
    }
}

