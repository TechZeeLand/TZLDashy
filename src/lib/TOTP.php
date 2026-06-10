<?php
declare(strict_types=1);

/**
 * Minimal RFC 6238 TOTP implementation (no third-party deps).
 */
class TOTP {
    public static function generateSecret(): string {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    public static function getCode(string $secret, int $timeSlot = 0): string {
        $secretBytes = self::base32Decode($secret);
        $timestamp   = intdiv(time(), 30) + $timeSlot;
        $msg         = pack('N*', 0) . pack('N*', $timestamp);
        $hash        = hash_hmac('sha1', $msg, $secretBytes, true);
        $offset      = ord($hash[19]) & 0x0f;
        $code        = (
            ((ord($hash[$offset])     & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) <<  8) |
             (ord($hash[$offset + 3]) & 0xff)
        ) % 1_000_000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code): bool {
        // Allow ±1 window (90 seconds drift)
        for ($i = -1; $i <= 1; $i++) {
            if (self::getCode($secret, $i) === $code) return true;
        }
        return false;
    }

    public static function getQRUrl(string $secret, string $email, string $issuer = 'TZLDashy'): string {
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer), rawurlencode($email), $secret, rawurlencode($issuer)
        );
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpauth);
    }

    private static function base32Decode(string $input): string {
        $map    = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $input  = strtoupper($input);
        $buffer = 0; $bitsLeft = 0; $result = '';
        foreach (str_split($input) as $char) {
            if (!isset($map[$char])) continue;
            $buffer   = ($buffer << 5) | $map[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result   .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $result;
    }
}
