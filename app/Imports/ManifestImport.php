<?php

namespace App\Imports;

use App\Models\Manifest;
use App\Models\PalletItem;
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

            $manifest = new Manifest([
                "item" => $row['item'],
                "description" => $row['description'],
                "quantity" => ($row['qty'] == "I") ? 1 :$row['qty'],
                "total" => str_replace(',', '.', $row['msrp_total']),
                "msrp" => $row['msrp_unit'],
                "pallet" => $row['pallet'],
                "type" =>  $row['type'],
                "pallets" => json_encode([$row['pallet'] => $row['qty']])
            ]);

            $manifest->save();

            if($manifest){
                $pallet = new PalletItem();
                $pallet->item_number = $row['item'];
                $pallet->pallet_number = $row['pallet'];
                $pallet->quantity = $row['qty'];
                $pallet->save();
                return $pallet;
            }
            
        }else{
            $pallet = new PalletItem();
            $pallet->item_number = $row['item'];
            $pallet->pallet_number = $row['pallet'];
            $pallet->quantity = $row['qty'];
            $pallet->save();
            return $pallet;
        }
    }
}
