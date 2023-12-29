<?php

namespace App\Imports;

use App\Models\Manifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ManifestImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $manifest = Manifest::where(['item' => $row['item']])->first();
        if(collect($manifest)->isEmpty()){
            return new Manifest([
                "item" => $row['item'],
                "description" => $row['description'],
                "quantity" => ($row['qty'] == "I") ? 1 :$row['qty'],
                "total" => str_replace(',', '.', $row['msrp_total']),
                "msrp" => $row['msrp_unit'],
                "pallet" => $row['pallet'],
                "type" =>  $row['type'],
                "pallets" => json_encode([$row['pallet'] => $row['qty']])
            ]);
        }else{
            $existingPallet = json_decode($manifest->pallets, true);
            foreach ($existingPallet as $key => $value) {
                if($manifest['item'] == $row['item'] && $key == $row['pallet']){
                    $total = ($value + $row['qty']);
                    Manifest::where(['item' => $row['item']])->update(['pallets' => json_encode([$key => $total])]);
                }

                if($manifest['item'] == $row['item'] && $key != $row['pallet']){
                    array_push($existingPallet, $row['qty']);
                    
                    Manifest::where(['item' => $row['item']])->update(['pallets' => json_encode($existingPallet)]);
                }
            }
            
        }
    }
}
