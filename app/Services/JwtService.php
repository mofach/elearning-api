<?php

namespace App\Services;

use App\Models\User;

class JwtService
{
  /**
   * Helper function untuk Base64Url Encoding
   * Standar Base64 menggunakan +, /, dan = yang bisa rusak saat dikirim via HTTP Header/URL
   */
  private static function base64UrlEncode(string $text)
  {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
  }

  /**
   * Generate JWT token manual
   */
  public static function generateToken(User $user)
  {
    // 1. Buat Header (menentukan algoritma dan tipe token)
    $header = json_encode([
      'typ' => 'JWT',
      'alg' => 'HS256'
    ]);

    // 2. Buat Payload (data user dan klaim waktu)
    $payload = json_encode([
      'user_id' => $user->id,
      'role'    => $user->role,
      'iat'     => time(), // issued at (waktu token dibuat)
      'exp'     => time() + (2 * 60 * 60) // expired time (set 2 jam)
    ]);

    // Encode Header & Payload ke format Base64Url
    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);

    // 3. Buat Signature menggunakan HMAC SHA-256
    $secret = env('JWT_SECRET');
    $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

    // Parameter keempat (true) menghasilkan raw binary data sebelum di-encode
    $signature = hash_hmac('sha256', $signatureInput, $secret, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    // Gabungkan ketiganya menjadi format standar JWT
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
  }
}
