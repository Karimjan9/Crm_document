<?php

namespace App\Services;

use RuntimeException;

class BackupEncryptionService
{
    private const PREFIX = 'CRM-BACKUP-1';

    public function encryptFile(string $source): string
    {
        $plaintext = file_get_contents($source);
        if ($plaintext === false) {
            throw new RuntimeException('Backup file could not be read for encryption.');
        }

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new RuntimeException('Backup encryption failed.');
        }

        $target = $source.'.enc';
        if (file_put_contents($target, self::PREFIX.$iv.$tag.$ciphertext, LOCK_EX) === false) {
            throw new RuntimeException('Encrypted backup could not be written.');
        }
        chmod($target, 0600);

        return $target;
    }

    public function decryptFile(string $source, string $target): void
    {
        $payload = file_get_contents($source);
        $prefixLength = strlen(self::PREFIX);
        if ($payload === false || ! str_starts_with($payload, self::PREFIX) || strlen($payload) < $prefixLength + 28) {
            throw new RuntimeException('Backup encryption format is invalid.');
        }

        $iv = substr($payload, $prefixLength, 12);
        $tag = substr($payload, $prefixLength + 12, 16);
        $ciphertext = substr($payload, $prefixLength + 28);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plaintext === false || file_put_contents($target, $plaintext, LOCK_EX) === false) {
            throw new RuntimeException('Backup decryption failed.');
        }
        chmod($target, 0600);
    }

    private function key(): string
    {
        $configured = (string) config('security.backups.encryption_key');
        $key = base64_decode(preg_replace('/^base64:/', '', $configured) ?: '', true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY must be a base64-encoded 32-byte key.');
        }

        return $key;
    }
}
