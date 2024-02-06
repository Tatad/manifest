<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Manifest;
use App\Models\Project;
use Carbon\Carbon;

class Manifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'item', 
        'description', 
        'quantity',
        'msrp',
        'total',
        'pallet',
        'features',
        'images',
        'status',
        'item_name',
        'pallets',
        'type',
        'upc_code'
    ];

    public function upc()
    {
        return $this->belongsTo(UpcCode::class, 'item', 'item');
    }

    public function palletItems()
    {
        return $this->belongsTo(PalletItem::class, 'item', 'item_number');
    }
}
