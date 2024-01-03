<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PalletItem extends Model
{
    use HasFactory;

    protected $table = 'pallet_items';

    protected $fillable = [
        'item_number', 
        'pallet_number', 
        'quantity'
    ];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'item_number', 'item');
    }

    public function pallet_download()
    {
        return $this->hasOne(PalletDownload::class, 'pallet_item_id', 'id');
    }
}
