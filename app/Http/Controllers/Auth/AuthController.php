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

        // Attempt to authenticate the user
        if (Auth::attempt(['npk' => $request->npk, 'password' => $request->password])) {
            // If authentication is successful, fetch the user info
            $npk = Auth::user()->npk;
            $userInfo = DB::connection('mysql2')->select(
                DB::raw(
                    "
                    SELECT kehadiran2.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr, users.is_admin
                    FROM kehadiran2
                    LEFT JOIN hirarki ON kehadiran2.empno = hirarki.empno
                    LEFT JOIN users ON kehadiran2.empno = users.npk
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiran2.empno = $npk
                    GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr, users.is_admin
                    ORDER BY mutdt DESC LIMIT 1;
                    "
                )
            );

            if (!empty($userInfo)) {
                $isadmin = $userInfo[0]->is_admin;
                $npkDesc = $userInfo[0]->hirar;
                $cleanedString = str_replace(' ', '', $npkDesc);
                $jumlahKarakter = strlen($cleanedString);

                // Determine the role based on the character count
                $roles = ['Karyawan'];
                if ($isadmin == 1) {
                    $roles[] = 'HRD Admin';
                }
                if ($jumlahKarakter == 5) {
                    $roles[] = 'Kepala Department';
                } elseif ($jumlahKarakter == 7) {
                    $roles[] = 'Supervisor';
                } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                    $roles[] = 'General Manager';
                }

                // Store user information and role options in session
                session(['roles' => $roles]);

                // Redirect to role selection page
                return redirect('/select-role');
            }
        }

        // If authentication fails, redirect back with an error message
        return redirect()->back()->withErrors(['login' => 'Invalid NPK or password. Please try again.']);
    }

    public function selectRole()
    {
        return view('auth.select-role');
    }

    public function setRole(Request $request)
    {
        $request->validate([
            'role' => 'required',
        ]);

        // Store the selected role in session
        session(['selected_role' => $request->role]);

        return redirect('/dashboard');
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

        // Periksa apakah NPK ada dalam tabel hirarki.empno
        $isNpkValid = DB::connection('mysql2')->table('hirarki')
            ->where('empno', $request->npk)
            ->exists();

        if (!$isNpkValid) {
            // NPK tidak terdaftar, tampilkan error
            return redirect()->back()->withErrors(['registration' => 'NPK is not registered. Please check your NPK and try again.']);
        }

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
