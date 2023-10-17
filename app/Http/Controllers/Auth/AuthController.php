<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function doLogin(Request $request)
    {
        // Validasi request
        $request->validate([
            'npk' => 'required',
            'password' => 'required',
        ]);

        // Coba melakukan autentikasi user
        if (Auth::guard('web')->attempt(['npk' => $request->npk, 'password' => $request->password])) {
            // Jika autentikasi berhasil, redirect ke halaman yang diinginkan
            return redirect('/')->with('success', 'Login successful! Welcome back, ' . Auth::user()->name . '!');
        } else {
            // Jika autentikasi gagal, redirect kembali ke halaman login dengan pesan error
            return redirect('/login')->withErrors(['login' => 'Invalid NPK or password. Please try again.']);
        }
    }


    public function register()
    {
        return view('auth.register');
    }

    public function doRegister(Request $request)
    {
        // Validasi request
        $request->validate([
            'npk' => 'required|unique:mysql2.users,npk',
            'name' => 'required',
            'password' => 'required|min:6',
        ]);

        // Simpan data user ke dalam tabel users menggunakan koneksi mysql2
        DB::connection('mysql2')->beginTransaction();
        try {
            User::on('mysql2')->create([
                'npk' => $request->npk,
                'name' => $request->name,
                'password' => bcrypt($request->password),
            ]);

            DB::connection('mysql2')->commit();

            // Redirect ke halaman login atau halaman lainnya
            return redirect('/login')->with('success', 'Registration successful! Please login.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::connection('mysql2')->rollBack();

            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->withErrors(['registration' => 'Registration failed. Please try again later.']);
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}
