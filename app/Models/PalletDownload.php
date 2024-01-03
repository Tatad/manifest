<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Manifest;
use App\Models\Project;
use Carbon\Carbon;

class PalletDownload extends Model
{
    use HasFactory;

    protected $table = 'pallet_downloads';

    protected $fillable = [
        'item_number', 
        'pallet_number', 
        'group_name'
    ];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'item_number', 'item');
    }

    public function pallet_item()
    {
        return $this->belongsTo(PalletItem::class, 'pallet_item_id', 'id');
    }
}
