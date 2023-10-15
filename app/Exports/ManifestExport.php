<?php 

namespace App\Exports;

use App\Models\Manifest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ManifestExport implements FromCollection, WithHeadings
{
    protected $manifests;

    public function __construct(array $manifests)
    {
        $this->manifests = $manifests;
    }

    public function collection()
    {
    	//$manifestData = Manifest::whereIn('id', $this->manifests)->get();
    	$manifestData = DB::table('manifests')
            ->select('item', 'description', 'msrp', 'pallet')
            ->whereIn('item', $this->manifests)
            ->get();
    	// dd($manifestData);
        return $manifestData;
    }

    public function headings(): array
    {
        return ["Item #", "Item Name", "MSRP", "Pallet #"];
    }
}