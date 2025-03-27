<?php

// JWTService class provides methods for working with JSON Web Tokens (JWT)
class JWTService {

    // Validate the JWT and check if it's correctly signed and not expired
    public static function isValidJWT(string $signKey, string $jwt): bool {
        // Split the JWT into three parts (header, payload, signature)
        $parts = explode('.', $jwt);
        
        // Ensure the JWT has three parts: header, payload, and signature
        if (count($parts) !== 3) {
            return false; // Invalid JWT format (should have three parts)
        }

        // Extract the individual parts: header, payload, and signature
        list($encodedHeader, $encodedPayload, $signature) = $parts;

        // Decode the base64Url-encoded header and payload
        $decodedHeader = json_decode(self::base64UrlDecode($encodedHeader), true);
        $decodedPayload = json_decode(self::base64UrlDecode($encodedPayload), true);

        // Ensure the decoded header and payload are valid JSON
        if ($decodedHeader === null || $decodedPayload === null) {
            return false; // Invalid header or payload format
        }

        // Check if the token is expired by comparing the current time with the expiration timestamp ('exp')
        if (isset($decodedPayload['exp']) && time() >= $decodedPayload['exp']) {
            return false; // Token has expired
        }

        // Recreate the signature by re-signing the header and payload using the same secret key
        $recreatedSignature = self::createSignature($encodedHeader, $encodedPayload, $signKey);

        // Check if the recreated signature matches the original signature from the JWT
        return hash_equals($signature, $recreatedSignature); // Compare the signatures securely
    }

    // Create the JWT using the header, payload, and secret key
    public static function createJWT(string $signKey, int $duration, array $payloadData): string {
        // Define the JWT header with algorithm and type information
        $header = [
            'alg' => 'HS256',  // Signing algorithm (HMAC SHA-256)
            'typ' => 'JWT',    // Type of token
            'iss' => 'my-auth-service'  // Issuer (the service creating the token)
        ];

        // Add expiration time ('exp') to the payload (token expires in the given duration)
        $exp = time() + $duration;
        $payloadData['exp'] = $exp;

        // Base64Url encode the header and payload to prepare them for signing
        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedPayload = self::base64UrlEncode(json_encode($payloadData));

        // Create the signature using the encoded header, payload, and the signing key
        $signature = self::createSignature($encodedHeader, $encodedPayload, $signKey);

        // Return the complete JWT string: header.payload.signature
        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    // Extract and return the payload from a JWT, without verifying the signature
    public static function getJWTPayload(string $signKey, string $jwt): array {
        // Split the JWT into its three parts: header, payload, and signature
        $parts = explode('.', $jwt);
    
        // Ensure the JWT has three parts (valid JWT format)
        if (count($parts) !== 3) {
            return []; // Invalid JWT format
        }
    
        // Extract the payload part (second part)
        $encodedPayload = $parts[1];
    
        // Decode the base64Url-encoded payload to get the payload data
        $decodedPayload = json_decode(self::base64UrlDecode($encodedPayload), true);
    
        // Check if the payload is valid JSON
        if ($decodedPayload === null) {
            return []; // Invalid payload format
        }
    
        // Return the decoded payload as an associative array
        return $decodedPayload;
    }

    // Helper method to Base64Url encode a string (used for encoding JWT header and payload)
    private static function base64UrlEncode(string $data): string {
        // Base64 encode the data and adjust for URL safety (replace '+' and '/' with '-' and '_')
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Helper method to Base64Url decode a string (used for decoding JWT header and payload)
    private static function base64UrlDecode(string $data): string {
        // Base64Url decode the data by reversing the URL-safe encoding
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    // Helper method to create the signature for a JWT by hashing the header and payload with the secret key
    private static function createSignature(string $encodedHeader, string $encodedPayload, string $signKey): string {
        // Concatenate the header and payload (separated by a dot)
        $data = $encodedHeader . '.' . $encodedPayload;

        // Generate the signature using HMAC SHA-256 with the secret key and encode it in Base64Url format
        return self::base64UrlEncode(hash_hmac('sha256', $data, $signKey, true));
    }
}
