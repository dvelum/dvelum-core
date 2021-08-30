<?php

/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2020  Kirill Yegorov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace Dvelum\Security;

use Dvelum\Config\ConfigInterface;

/**
 * Simple encryption class
 * Uses Base64 storage format for keys and data
 * @package Dvelum\Security
 */
class CryptService implements CryptServiceInterface
{
    /**
     * @var string
     */
    private $chipper = 'aes-256-ctr';
    /**
     * @var string
     */
    private $hash = 'sha256';
    /**
     * @var string|null
     */
    private $privateKey = null;
    /**
     * @var string|null
     */
    private $privateKeyData = null;
    /**
     * @var string
     */
    private $error = '';

    /**
     * @var array|null
     */
    private $privateKeyOptions = null;

    public function __construct(ConfigInterface $config)
    {
        $this->chipper = $config->get('chipper');
        $this->hash = $config->get('hash');
        $this->privateKey = $config->get('key');
    }

    /**
     * Verify that encryption works, all dependencies are installed
     * @return bool
     */
    public function canCrypt(): bool
    {
        if (!extension_loaded('openssl')) {
            $this->error = 'OpenSSL Extesion is not loaded';
            return false;
        }

        if (!in_array($this->chipper, openssl_get_cipher_methods(true), true)) {
            $this->error = 'Unknown cipher algorithm ' . $this->chipper;
            return false;
        }

        if (!in_array($this->hash, openssl_get_md_methods(true), true)) {
            $this->error = 'Unknown hash algorithm ' . $this->hash;
            return false;
        }
        return true;
    }

    /**
     * Get error message
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Set private key generator options
     * @param array $options
     * @return void
     */
    public function setPrivateKeyOptions(array $options): void
    {
        $this->privateKeyOptions = $options;
    }

    /**
     * Generate new private key
     * @return string
     */
    public function createPrivateKey(): string
    {
        // init private key options
        if (empty($this->privateKeyOptions)) {
            $this->privateKeyOptions = [
                "digest_alg" => "sha512",
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
        }
        $res = openssl_pkey_new($this->privateKeyOptions);
        if (empty($res)) {
            throw new \Exception('openssl_pkey_new empty result');
        }
        openssl_pkey_export($res, $key);
        return $key;
    }

    /**
     * Create random initialisation vector
     * return vector as base64 encoded string
     * @return string
     */
    public function createVector(): string
    {
        return base64_encode((string)openssl_random_pseudo_bytes((int)openssl_cipher_iv_length($this->chipper)));
    }

    /**
     * Encrypt a string.
     * @param string $string - string to encrypt.
     * @param string $base64Vector - base64 encoded initialization vector
     * @return string - base64 encoded encryption result
     * @throws \Exception
     */
    public function encrypt(string $string, string $base64Vector): string
    {
        $iv = base64_decode($base64Vector);
        $keyHash = openssl_digest($this->getPrivateKey(), $this->hash, true);

        if (!is_string($keyHash)) {
            throw new \Exception('Encryption failed: openssl_digest ');
        }
        $encrypted = openssl_encrypt($string, $this->chipper, $keyHash, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed: ' . openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    /**
     * Decrypt a string.
     * @param string $string - base64 encoded encrypted string to decrypt.
     * @param string $base64Vector - base64 encoded initialization vector
     * @return string - the decrypted string.
     * @throws \Exception
     */
    public function decrypt(string $string, string $base64Vector): string
    {
        $iv = base64_decode($base64Vector);
        $src = base64_decode($string);
        $keyHash = openssl_digest($this->getPrivateKey(), $this->hash, true);

        if (!is_string($keyHash)) {
            throw new \Exception('Encryption failed: openssl_digest ');
        }

        $res = openssl_decrypt($src, $this->chipper, $keyHash, OPENSSL_RAW_DATA, $iv);

        if ($res === false) {
            throw new \Exception('Decryption failed: ' . openssl_error_string());
        }
        return $res;
    }

    /**
     * Get private key
     * @return string
     * @throws \Exception
     */
    protected function getPrivateKey(): string
    {
        if (is_null($this->privateKeyData)) {
            if (file_exists((string)$this->privateKey)) {
                $this->privateKeyData = (string)file_get_contents((string)$this->privateKey);
            } else {
                throw new \Exception('Private key file is not exists ' . $this->privateKey);
            }
        }
        return $this->privateKeyData;
    }
}
