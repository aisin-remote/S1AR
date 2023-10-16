<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function doLogin(Request $request)
    {

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        $user = auth('user')->attempt($credentials);

        if ($user) {
            return redirect('/');
        } else {
            return back();
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

        // dd($request);

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
        auth('user')->logout();

        return redirect('/login');
    }
}
