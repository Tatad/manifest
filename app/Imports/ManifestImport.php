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
        return new Manifest([
            "item" => $row['item'],
            "description" => $row['description'],
            "quantity" => ($row['qty'] == "I") ? 1 :$row['qty'],
            "total" => str_replace(',', '.', $row['msrp_total']),
            "msrp" => $row['msrp_unit'],
            "pallet" => $row['pallet']
        ]);
    }
}
