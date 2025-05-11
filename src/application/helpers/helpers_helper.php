<?php
if (! function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $arg) {
            $style = 'line-height: 123%; font-size: 9pt';
            if (is_array($arg) || is_object($arg)) {
                $style = 'line-height: 123%; font-size: 9pt; color: #006699; z-index: 999999';
            }
            echo "<pre style='$style'>";
            var_dump($arg);
            echo '</pre>';
        }
        die;
    }
}

if (!function_exists('aes_encrypt_gcm')) {
    function aes_encrypt_gcm($plaintext, $key, $aad = "") {
        $ivlen = 12; // recommended IV length for AES-GCM
        $taglen = 16; // tag length (can be 4, 6, 8, 10, 12, 14, or 16)
        $iv = random_bytes($ivlen);

        $cipher = 'aes-256-gcm';

        $tag = null;
        $ciphertext = openssl_encrypt(
            $plaintext,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad,
            $taglen
        );

        if ($ciphertext === false) {
            return false;
        }

        return base64_encode($iv . $tag . $ciphertext);
    }
}

if (!function_exists('aes_decrypt_gcm')) {
    function aes_decrypt_gcm($encrypted, $key, $aad = '') {
        $cipher = 'aes-256-gcm';
        $ivlen = 12;
        $taglen = 16;

        $data = base64_decode($encrypted);
        $iv = substr($data, 0, $ivlen);
        $tag = substr($data, $ivlen, $taglen);
        $ciphertext = substr($data, $ivlen + $taglen);

        $plaintext = openssl_decrypt(
            $ciphertext,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad
        );

        return $plaintext;
    }
}
