<?php

class JWTService {

    // Validate the JWT and check if it's correctly signed and not expired
    public static function isValidJWT(string $signKey, string $jwt): bool {
        // Split JWT into three parts
        $parts = explode('.', $jwt);
        
        // Ensure the JWT has three parts (header, payload, signature)
        if (count($parts) !== 3) {
            return false; // Invalid JWT format
        }

        // Extract each part
        list($encodedHeader, $encodedPayload, $signature) = $parts;

        // Decode header and payload
        $decodedHeader = json_decode(self::base64UrlDecode($encodedHeader), true);
        $decodedPayload = json_decode(self::base64UrlDecode($encodedPayload), true);

        // Ensure header and payload are valid JSON
        if ($decodedHeader === null || $decodedPayload === null) {
            return false;
        }

        // Check if the token is expired
        if (isset($decodedPayload['exp']) && time() >= $decodedPayload['exp']) {
            return false; // Token is expired
        }

        // Recreate the signature by re-signing the header and payload with the same key
        $recreatedSignature = self::createSignature($encodedHeader, $encodedPayload, $signKey);

        // Check if the recreated signature matches the signature from the token
        return hash_equals($signature, $recreatedSignature);
    }

    // Create the JWT using header, payload, and secret key
    public static function createJWT(string $signKey, int $duration, array $payloadData): string {
        // Create the header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
            'iss' => 'my-auth-service'
        ];

        // Create the payload with expiration
        $exp = time() + $duration;
        $payloadData['exp'] = $exp;

        // Base64Url encode header and payload
        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedPayload = self::base64UrlEncode(json_encode($payloadData));

        // Create the signature
        $signature = self::createSignature($encodedHeader, $encodedPayload, $signKey);

        // Return the JWT as a string
        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    // Helper method to base64Url encode a string
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Helper method to base64Url decode a string
    private static function base64UrlDecode(string $data): string {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    // Helper method to create the signature for a JWT
    private static function createSignature(string $encodedHeader, string $encodedPayload, string $signKey): string {
        // Create the string to be signed: header + payload
        $data = $encodedHeader . '.' . $encodedPayload;

        // Generate the signature using SHA-256 with the secret key
        return self::base64UrlEncode(hash_hmac('sha256', $data, $signKey, true));
    }
}
