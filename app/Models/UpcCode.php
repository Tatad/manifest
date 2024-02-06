<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UpcCode extends Model
{
    use HasFactory;

    protected $table = 'upc_codes';

    protected $fillable = [
        'item', 
        'upc_code'
    ];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'item', 'item');
    }
}
