<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Validator;

class ImageUploadController extends Controller
{
    public function page()
    {
        return Inertia::render('Scanner');
    }
}