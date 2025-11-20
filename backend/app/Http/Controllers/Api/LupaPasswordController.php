<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LupaPasswordController extends Controller
{
    /**
     * Request OTP untuk lupa password
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;

        // Cek apakah email terdaftar
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar dalam sistem. Silakan gunakan email yang terdaftar.',
            ], 404);
        }

        // Generate OTP (6 digit)
        $kodeOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Set expiration time (15 menit dari sekarang)
        $expiresAt = Carbon::now()->addMinutes(15);

        // Hapus OTP lama untuk email ini
        Otp::where('email', $email)->delete();

        // Simpan OTP baru
        Otp::create([
            'email' => $email,
            'kode_otp' => $kodeOtp,
            'expires_at' => $expiresAt,
        ]);

        // Kirim email OTP
        try {
            Mail::send('emails.otp', [
                'kodeOtp' => $kodeOtp,
                'expiresAt' => $expiresAt->format('d/m/Y H:i'),
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Kode OTP untuk Reset Password - HR Yayasan Darussalam');
            });

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda.',
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error sending OTP email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    /**
     * Verifikasi OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'kode_otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $kodeOtp = $request->kode_otp;

        // Cari OTP terbaru untuk email ini
        $otp = Otp::where('email', $email)
            ->orderBy('id', 'desc')
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan. Silakan request OTP baru.',
            ], 404);
        }

        // Cek apakah OTP sudah expired
        if ($otp->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan request OTP baru.',
            ], 400);
        }

        // Cek apakah kode OTP benar
        if ($otp->kode_otp !== $kodeOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi.',
        ]);
    }

    /**
     * Reset password setelah OTP terverifikasi
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'kode_otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $kodeOtp = $request->kode_otp;
        $password = $request->password;

        // Cari OTP terbaru untuk email ini
        $otp = Otp::where('email', $email)
            ->orderBy('id', 'desc')
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan. Silakan request OTP baru.',
            ], 404);
        }

        // Cek apakah OTP sudah expired
        if ($otp->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan request OTP baru.',
            ], 400);
        }

        // Cek apakah kode OTP benar
        if ($otp->kode_otp !== $kodeOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        // Cari user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Hapus OTP setelah berhasil reset password
        $otp->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
        ]);
    }
}
