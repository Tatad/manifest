<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('registration.create');
    }

    public function store(Request $request)
    {
        // $input = $request->all();
        // dd($input);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ]);

        User::create([
            'name' => 'manifest user',
            'email' => $request->email,
            'faceio_id' => $request->faceio_id,
            'password' => Hash::make('test12345')
        ]);
        $user = User::where('faceio_id', $request->faceio_id)->first();
        Auth::loginUsingId($user->id);
        return redirect()->back()->with('success','Registration completed');
    }

    public function authenticate(Request $request){
        $user = User::where('faceio_id', $request->faceio_id)->first();
        Auth::loginUsingId($user->id);
        return redirect()->back()->with('success','Login completed');
    }
}