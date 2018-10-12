<?php
namespace michaelmawhinney;

/**
 * Cryptex performs 2-way authenticated encryption using XChaCha20 + Poly1305.
 *
 * This class uses the Sodium crypto library included with PHP 7.2 or newer.
 * A salt value is optional, but highly recommended. When possible, the salt
 * should be randomly generated by a secure function like random_bytes().
 *
 * @category Encryption/Decryption
 * @package Cryptex
 * @author Michael Mawhinney <michael.mawhinney.jr@gmail.com>
 * @copyright 2018
 * @license https://opensource.org/licenses/MIT/ MIT
 * @version Release: 3.0.1
 */
final class Cryptex
{
    /**
     * Encrypt data using XChaCha20 + Poly1305 (from the Sodium crypto library)
     *
     * @param string $plaintext unencrypted data
     * @param string $key       encryption key
     * @param string $salt      salt value (optional)
     * @return string           encrypted data (hex-encoded)
     */
    public static function encrypt(string $plaintext, string $key, string $salt = null): string
    {
        // Generate a derived binary key
        $bin_key = self::genBinKey($key, $salt);

        // Generate a nonce value of the correct size
        $nonce = random_bytes(
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES
        );

        // Encrypt the data, prepend the nonce, and hex encode
        $ciphertext = sodium_bin2hex(
            $nonce .
            sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $plaintext,
                '',
                $nonce,
                $bin_key
            )
        );
        if ($ciphertext === false) {
            throw new Exception('Encoding failure');
        }

        // Wipe sensitive data and return the encrypted data
        sodium_memzero($plaintext);
        sodium_memzero($key);
        $salt === null || sodium_memzero($salt);
        return $ciphertext;
    }

    /**
     * Authenticate and decrypt data encrypted by Cryptex (XChaCha20+Poly1305)
     *
     * @param string $ciphertext    encrypted data
     * @param string $key           encryption key
     * @param string $salt          salt value (if applicable)
     * @return string               unencrypted data
     */
    public static function decrypt(string $ciphertext, string $key, string $salt = null): string
    {
        // Generate a derived binary key
        $bin_key = self::genBinKey($key, $salt);

        // Hex decode
        $decoded = sodium_hex2bin($ciphertext);
        if ($decoded === false) {
            throw new Exception('Decoding failure');
        }

        // Get the nonce value from the decoded data
        $nonce = mb_substr(
            $decoded,
            0,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES,
            '8bit'
        );

        // Get the ciphertext from the decoded data
        $ciphertext = mb_substr(
            $decoded,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES,
            null,
            '8bit'
        );

        // Decrypt the data
        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            '',
            $nonce,
            $bin_key
        );
        if ($plaintext === false) {
            throw new Exception('Decryption failure');
        }

        // Wipe sensitive data and return the decrypted data
        sodium_memzero($key);
        $salt === null || sodium_memzero($salt);
        return $plaintext;
    }

    /**
     * Generate a derived binary key using PBKDF2 with SHA-256
     *
     * @param string $key   encryption key
     * @param string $salt  salt value (optional)
     * @return string       derived binary key
     */
    private static function genBinKey(string $key, ?string $salt): string
    {
        return hash_pbkdf2(
            'sha256',
            $key,
            $salt,
            10000,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES,
            true
        );
    }
}
