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

class ManifestController extends Controller
{
    public function manifestSentList(Request $request){
        $manifests = PalletItem::with('manifest','pallet_download')->get();
        $manifestData = collect($manifests)->map(function ($data){
            if(($data['manifest']) && isset($data['pallet_download'])){
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
                    //'images' => ($itemData->images != 'not_available') ? json_decode($itemData->images,true) : $itemData->images,
                    'images' => ($data->manifest->type && $data->manifest->type == "Mixed") ? ["https://images.costco-static.com/ImageDelivery/imageService?profileId=12026539&itemId=".$data->manifest->item."-894"] : $data->manifest->images,
                    'costcoUrl' => $data->manifest->item_name,
                    'totalMsrp' => round(($data->quantity * $data->manifest->msrp),2)
                ];
            }
        });
        //get available pallets
        $pallets = collect($manifests)->groupBy('pallet_number')->keys();
        $palletArray = [];
        foreach($pallets as $pallet){
            $palletArray[] = ['pallet' => $pallet];
        }

        return Inertia::render('ManifestSentList', [
            'manifests' => collect($manifestData)->filter()->values()->toArray(),
            'pallets'   => $palletArray
        ]);
    }

    public function manifest(Request $request){
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
                    //'images' => ($itemData->images != 'not_available') ? json_decode($itemData->images,true) : $itemData->images,
                    'images' => ($data->manifest->type && $data->manifest->type == "Mixed") ? ["https://images.costco-static.com/ImageDelivery/imageService?profileId=12026539&itemId=".$data->manifest->item."-894"] : $data->manifest->images,
                    'costcoUrl' => $data->manifest->item_name,
                    'totalMsrp' => round(($data->quantity * $data->manifest->msrp),2)
                ];
            }
        });
        //get available pallets
        $pallets = collect($manifests)->groupBy('pallet_number')->keys();
        $palletArray = [];
        foreach($pallets as $pallet){
            $palletArray[] = ['pallet' => $pallet];
        }

        return Inertia::render('Manifest', [
            'manifests' => collect($manifestData)->filter()->values()->toArray(),
            'pallets'   => $palletArray
        ]);
    }

    public function manifestGroupedView(){
        $manifests = PalletItem::with('manifest','pallet_download')->get()->groupBy('pallet_number');
        
        $manifestDataResult = [];
        foreach($manifests as $key => $val){
            $sum = 0;
            $manifest = [];
            foreach($val as $d){
                if(($d['manifest']) && !isset($d['pallet_download'])){
                    $sum += $d['manifest']['total'];
                    $d['manifest']['pallet'] = $key;
                    $manifest[] = $d['manifest'];
                }
            }

            if($sum != 0){
                $manifestDataResult[] = [
                    'group_id' => null,
                    'downloaded_at' => null,
                    'pallet' => $key,
                    'group_name' => null,
                    'sum' => $sum,
                    'data' => collect($manifest)->values()->toArray()
                ];
            }
        }

        return Inertia::render('ManifestGroupedView', [
            'manifests'  => $manifestDataResult
        ]);
    }

    public function manifestSent(Request $request){
        $manifests = PalletDownload::with('manifest','pallet_item')->get()->groupBy('group_id');

        $manifestDataResult = [];
        foreach($manifests as $key => $val){
            $sum = 0;
            $manifest = [];
            foreach($val as $d){
                $sum += $d['manifest']['total'];
                $manifest[] = $d['manifest'];
            }

            if($sum != 0){
                $manifestDataResult[] = [
                    'group_id' => $val[0]['group_id'],
                    'downloaded_at' => Carbon::parse($val[0]['created_at'])->format('Y-m-d h:i:s'),
                    'pallet' => $key,
                    'group_name' => $val[0]['group_name'],
                    'sum' => $sum,
                    'data' => collect($val)->values()->toArray()
                ];
            }
        }
        return Inertia::render('ManifestSent', [
            'manifests'  => collect($manifestDataResult)->values()->toArray()
        ]);
    }

    public function pdfManifest(Request $request){
        $input = $request->all()['form'];
        $downloadId = DB::table('pallet_downloads')->latest('group_id')->first();
        foreach($input['selected'] as $key => $selected){
            $pallet_item = PalletItem::where(['item_number' => $selected['item'], 'pallet_number' => $selected['pallet']])->first();
            $palletDownload = new PalletDownload();
            $palletDownload->group_id = $downloadId ? ($downloadId->group_id + 1) : 1;
            $palletDownload->pallet_item_id = $pallet_item->id;
            $palletDownload->pallet_number = $selected['pallet'];
            $palletDownload->item_number = $selected['item'];
            $palletDownload->save();
        }
        $manifests = PalletDownload::with('manifest','pallet_item')->where('group_id', $palletDownload->group_id)->get();
        $manifestData = collect($manifests)->map(function ($data){
            return [
                'item' => $data['manifest']['item'],
                'quantity' => $data['pallet_item']['quantity'], 
                'id' => $data['manifest']['id'], 
                'pallet' => $data['manifest']['pallet'],
                'description' => $data['manifest']['description'],
                'msrp' => $data['manifest']['msrp'],
                'features' => $data['manifest']['features'],
                'item_name' => $data['manifest']['item_name'],
                'images' => $data['manifest']['images'],
                'costcoUrl' => $data['manifest']['item_name'],
                'totalMsrp' => round(($data['pallet_item']['quantity'] * $data['manifest']['msrp']),2)
            ]; 
        })->values()->toArray();

        $pdf = \Pdf::loadView('pdf.download', ['data' => collect($manifestData)->values()->toArray()]);

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
        $downloadId = DB::table('pallet_downloads')->latest('group_id')->first();
        foreach($input['selected'] as $key => $selected){
            $pallet_item = PalletItem::where(['item_number' => $selected['item'], 'pallet_number' => $selected['pallet']])->first();
            $palletDownload = new PalletDownload();
            $palletDownload->group_id = $downloadId ? ($downloadId->group_id + 1) : 1;
            $palletDownload->pallet_item_id = $pallet_item->id;
            $palletDownload->pallet_number = $selected['pallet'];
            $palletDownload->item_number = $selected['item'];
            $palletDownload->save();
        }
        $manifests = PalletDownload::with('manifest','pallet_item')->where('group_id', $palletDownload->group_id)->get();
        foreach(collect($manifests)->values()->toArray() as $data){
            $manifestData[] = [
                'item' => $data['manifest']['item'],
                'description' => $data['manifest']['description'],
                'msrp' => $data['manifest']['msrp'],
                'pallet' => $data['pallet_number'],
                'quantity' => $data['pallet_item']['quantity'], 
                'total' => round(($data['pallet_item']['quantity'] * $data['manifest']['msrp']),2)
            ]; 
        }

        $export = new ManifestExport(
            collect($manifestData)->values()->toArray()
        );

        return \Excel::download($export, 'manifest.csv');
    }

    public function batchDownloadCsv(Request $request){
        $input = $request->all();
        $manifests = PalletDownload::with('manifest','pallet_item')->where('group_id', $input['selected'])->get();
        foreach(collect($manifests)->values()->toArray() as $data){
            $manifestData[] = [
                'item' => $data['manifest']['item'],
                'description' => $data['manifest']['description'],
                'msrp' => $data['manifest']['msrp'],
                'pallet' => $data['pallet_number'],
                'quantity' => $data['pallet_item']['quantity'], 
                'total' => round(($data['pallet_item']['quantity'] * $data['manifest']['msrp']),2)
            ]; 
        }

        $export = new ManifestExport(
            collect($manifestData)->values()->toArray()
        );

        return \Excel::download($export, 'manifest.csv');
    }

    public function batchDownloadPdf(Request $request){
        $input = $request->all();

        $manifests = PalletDownload::with('manifest','pallet_item')->where('group_id', $input['selected'])->get();

        $manifestData = collect($manifests)->map(function ($data){
            return [
                'item' => $data['manifest']['item'],
                'quantity' => $data['pallet_item']['quantity'], 
                'id' => $data['manifest']['id'], 
                'pallet' => $data['manifest']['pallet'],
                'description' => $data['manifest']['description'],
                'msrp' => $data['manifest']['msrp'],
                'features' => $data['manifest']['features'],
                'item_name' => $data['manifest']['item_name'],
                'images' => $data['manifest']['images'],
                'costcoUrl' => $data['manifest']['item_name'],
                'totalMsrp' => round(($data['pallet_item']['quantity'] * $data['manifest']['msrp']),2)
            ]; 
        })->values()->toArray();
        $pdf = \Pdf::loadView('pdf.download', ['data' => collect($manifestData)->values()->toArray()]);
        return $pdf->stream('download.pdf');
    }

    public function restore(Request $request){
        $input = $request->all();
        $manifest = PalletDownload::where('group_id', $input['selected'])->delete();
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
        if($input['download_group_id']){
            PalletDownload::where('group_id', $input['download_group_id'])->update(['group_name' => $input['group_name']]);
        }
        return 'success';
    }

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

