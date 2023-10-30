<?php 

namespace App\Exports;

use App\Models\Manifest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ManifestExport implements FromCollection, WithHeadings
{
    protected $item;
    protected $pallet;

    public function __construct(array $item, array $pallet)
    {
        $this->item = $item;
        $this->pallet = $pallet;
    }

    public function collection()
    {
    	$manifestData = DB::table('manifests')
            ->select('item', 'description', 'msrp', 'pallet')
            ->whereIn('item', $this->item)
            ->whereIn('pallet', $this->pallet)
            ->get();
        return $manifestData;
    }

    public function headings(): array
    {
        return ["Item #", "Item Name", "MSRP", "Pallet #"];
    }
}