<?php 

namespace App\Exports;

use App\Models\Manifest;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ManifestExport implements FromArray, WithHeadings
{
    protected $item;

    public function __construct(array $item)
    {
        $this->item = $item;
        //$this->pallet = $pallet;
    }

    public function array(): array
    {
        return $this->item;
    }

    public function headings(): array
    {
        return ["Item #", "Item Name", "MSRP", "Pallet #", "Quantity", "$ Total"];
    }
}