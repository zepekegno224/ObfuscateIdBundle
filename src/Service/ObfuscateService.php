<?php

namespace Zepekegno\ObfuscateIdBundle\Service;

use Random\RandomException;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;

final class ObfuscateService implements ObfuscateIdInterface
{
	private const CIPHER = 'aes-256-cbc';

	public function __construct(private readonly ?string $secretKey = null)
	{
	}

	/**
	 * @throws RandomException
	 */
	public function getSecretKey(): string
	{
		$secretKey = $this->secretKey ?? $this->loadOrGenerateKey();
		return hash('sha256', $secretKey, true);
	}


	/**
	 * @throws RandomException
	 */
	public function obfuscate(int $value): string
	{
		$iv = $this->loadOrGenerateIV();
		$encrypted = openssl_encrypt((string)$value, self::CIPHER, $this->getSecretKey(), 0, $iv);
		if ($encrypted === false) {
			throw new \RuntimeException('Failed to encrypt the ID.');
		}

		return bin2hex($encrypted);
	}

	/**
	 * @throws RandomException
	 */
	public function deobfuscate(string $value): ?int
	{

		if (!$this->isObfuscated($value)) {
			return null;
		}

		$encryptedData = hex2bin($value);
		$iv = $this->loadOrGenerateIV();
		$decrypted = openssl_decrypt($encryptedData, self::CIPHER, $this->getSecretKey(), 0, $iv);
		if ($decrypted !== false) {
			return (int)$decrypted;
		}

		$ivLength = openssl_cipher_iv_length(self::CIPHER);
		$iv = substr($encryptedData, 0, $ivLength);
		$encryptedPayload = substr($encryptedData, $ivLength);
		$key = $this->secretKey ?? $this->loadOrGenerateKey();
		$decryptedOld = openssl_decrypt($encryptedPayload, self::CIPHER, $key, 0, $iv);
		if ($decryptedOld !== false) {
			return (int)$decryptedOld;
		}
		return null;
	}

	public function isObfuscated(string $value): bool
	{
		if (!ctype_xdigit($value) || strlen($value) % 2 !== 0) {
			return false;
		}
		$data = hex2bin($value);
		return $data !== false;
	}

	/**
	 * @throws RandomException
	 */
	private function loadOrGenerateKey(): string
	{
		$keyFile = dirname(__DIR__, 2) . '/var';

		if (!file_exists($keyFile) && !mkdir($keyFile, 0777) && !is_dir($keyFile)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $keyFile));
		}
		if (file_exists($keyFile . '/obfuscate_key.txt')) {
			return trim(file_get_contents($keyFile . '/obfuscate_key.txt'));
		}
		$generatedKey = bin2hex(random_bytes(16));
		file_put_contents($keyFile . '/obfuscate_key.txt', $generatedKey);
		return $generatedKey;
	}

	private function loadOrGenerateIV(): string
	{
		$varDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'var';
		$ivFile = $varDir . DIRECTORY_SEPARATOR . 'obfuscate_iv.bin';

		// Ensure the directory exists and is writable
		if (!is_dir($varDir) && !mkdir($varDir, 0777, true) && !is_dir($varDir)) {
			throw new \RuntimeException(sprintf('Failed to create directory: "%s"', $varDir));
		}

		// Return existing IV if found
		if (is_file($ivFile) && is_readable($ivFile)) {
			return file_get_contents($ivFile) ?: '';
		}

		// Generate a new IV (exact 16 bytes)
		$generatedIV = random_bytes(16); // No bin2hex()

		if (file_put_contents($ivFile, $generatedIV) === false) {
			throw new \RuntimeException(sprintf('Failed to write IV to "%s"', $ivFile));
		}

		return $generatedIV;
	}


}
