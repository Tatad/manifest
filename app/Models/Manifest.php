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
        'status'
    ];

}
