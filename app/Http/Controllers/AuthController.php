<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginResources;
use App\Http\Resources\UserResouces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AuthRequest\LoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

// TODO :  THROTTLE (DONE)
//
class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        if (Auth::check()) { // Cek Sudah Login
            if (!isRequestBladeView()) {
                return response()->json(['message' => 'Anda sudah login'], 200);
            } else {
                // Jika bukan permintaan JSON, redirect ke halaman login atau halaman lain yang sesuai
                return response()->json(['message' => 'Anda sudah login'], 200);
            }
        }

        // Cek apakah ada terlalu banyak percobaan login yang gagal dari alamat IP ini
        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        // Coba melakukan autentikasi
        if (Auth::attempt($request->only('username', 'password'))) {
            // Auth berhasil
            $user = Auth::user();

            // Reset percobaan login gagal
            $this->clearLoginAttempts($request);

            // Generate token JWT
            $token = $this->generateSanctumToken($user);

            $user->token = $token;
            if (!isRequestBladeView()) { // Apakah Tidak Request Blade View? (TESTING ONLY)
                return response()->json(new LoginResources($user), 200);
            }
            else {
                session()->flash('alert', [
                    'type' => 'success',
                    'message' => sprintf('Selamat datang, %s (%s). Anda berhasil masuk.', $user->nama, $user->username),
                ]);

                // Jika bukan permintaan JSON, redirect ke halaman yang sesuai
                return view('view.auth.login')->with([
                    'token' => $token,
                    'user' => $user,
                ]);
            }
        }
        else {
            // Auth gagal
            $this->incrementLoginAttempts($request);

            if ($request->wantsJson() || $request->isJson()) {
                return response()->json(['message' => 'Username atau password salah.'], 401);
            } else {
                session()->flash('alert', [
                    'type' => 'danger',
                    'message' => sprintf('Username atau password salah.'),
                ]);
                // Jika bukan permintaan JSON, redirect kembali ke halaman login dengan pesan error
                return view('view.auth.login')->withErrors([
                    'username' => 'Username atau password salah.',
                ]);
            }
        }
    }
    public function logout()
    {
        // Mengecek apakah pengguna sedang autentikasi
        if (Auth::check()) {
            // Menghapus semua token autentikasi pengguna
            Auth::user()->tokens()->delete();

            if (!isRequestBladeView()) {
                return response()->json(['message' => 'Logged out successfully'], 200);
            } else {
                // Jika bukan permintaan JSON, redirect ke halaman login atau halaman lain yang sesuai
                return response()->json(['message' => 'Logged out successfully'], 200);
            }
        }


    }

    protected function hasTooManyLoginAttempts(LoginRequest $request)
    {
        $maxAttempts = 5;
        $lockoutMinutes = 5;

        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request),
            $maxAttempts,
            $lockoutMinutes
        );
    }

    protected function incrementLoginAttempts(LoginRequest $request)
    {
        RateLimiter::hit($this->throttleKey($request));
    }

    protected function clearLoginAttempts(LoginRequest $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    protected function sendLockoutResponse(LoginRequest $request)
    {
        $lockoutMinutes = 5;
        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        return response()->json([
            'message' => 'Too many login attempts. Please try again after ' . $seconds . ' seconds.',
        ], 429);
    }

    protected function throttleKey(LoginRequest $request) // SAMAKAN THROTTLE KEY DI PROVIDER ROUTE
    {
        return Str::lower($request->input('username')) . '|' . $request->ip();
    }

    protected function generateJwtToken($user)
    {
        $token = $user->createToken('MyApp')->plainTextToken;
        return $token;
    }
    protected function generateSanctumToken($user)
    {
        $token = $user->createToken('api-token')->plainTextToken;
        return $token;
    }
}
