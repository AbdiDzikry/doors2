<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    /**
     * Handle the SSO Login redirect.
     * Route: GET /sso?token=...
     */
    public function login(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            Log::warning('SSO Login Attempt failed: No token provided');
            return redirect()->route('login')->withErrors(['msg' => 'Token SSO tidak ditemukan']);
        }

        // 1. Validasi token ke API SSO (Sesuai Protokol PDF)
        // URL: https://api-sso.dharmap.com/api/check-token
        // Method: GET
        // Param: token
        try {
            $apiUrl = config('services.sso.verify_endpoint', 'https://api-sso.dharmap.com/api/check-token');
            
            $response = Http::get($apiUrl, [
                'token' => $token
            ]);
            
            if ($response->failed()) {
                 Log::error('SSO API Failed', ['status' => $response->status(), 'body' => $response->body()]);
                 return redirect()->route('login')->withErrors(['msg' => 'Gagal menghubungi server SSO']);
            }

            $data = $response->json();
        } catch (\Exception $e) {
            Log::error('SSO Connection Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['msg' => 'Terjadi kesalahan sistem saat validasi SSO']);
        }
        
        // 2. Cek Validitas Response
        if (empty($data['user'])) {
            Log::warning('SSO Token Invalid', ['response' => $data]);
            return redirect()->route('login')->withErrors(['msg' => 'Token SSO tidak valid atau expired']);
        }

        $ssoUser = $data['user'];
        
        // 3. Cari user by NPK (Primary Identifier)
        $user = User::where('npk', $ssoUser['npk'])->first();
        
        if (!$user) {
            // 4. JIT Provisioning (Auto-Create New User)
            try {
                $user = User::create([
                    'npk' => $ssoUser['npk'],
                    'name' => $ssoUser['name'],
                    'email' => $ssoUser['email'],
                    // Password hash dari SSO (sebaiknya di-rehash atau buat random jika format tidak kompatibel)
                    // Untuk keamanan di Doors, kita buat random password saja agar tidak tergantung hash luar.
                    'password' => Hash::make(Str::random(32)), 
                    'sso_id' => $ssoUser['id'] ?? null,
                    'sso_provider' => 'dharmap-sso',
                    'last_sso_login' => now(),
                    'email_verified_at' => now(),
                ]);

                // Assign Default Role: Karyawan
                $user->assignRole('karyawan');
                
            } catch (\Exception $e) {
                Log::error('SSO User Creation Failed: ' . $e->getMessage());
                return redirect()->route('login')->withErrors(['msg' => 'Gagal membuat user baru dari data SSO.']);
            }
        } else {
            // 5. Update Existing User (Sync Data)
            $user->update([
                'sso_id' => $ssoUser['id'] ?? $user->sso_id,
                'sso_provider' => 'dharmap-sso',
                'last_sso_login' => now(),
                // Optional: Sync name/email if desired
                // 'name' => $ssoUser['name'], 
            ]);
        }

        // 6. Force Login
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
