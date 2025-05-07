<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index()
    {
        return view('register');
    }
    public function register(Request $request)
    {

        // dd($request->all());

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'telepon'    => 'required|string|max:15',
            'password' => 'required|string|min:6'
        ]);

        // Simpan user baru
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'telepon'    => $request->telepon,
            'password' => Hash::make($request->password),
        ]);



        return redirect('/login')->with('success', 'Registrasi berhasil, silakan login.');
    }
}
