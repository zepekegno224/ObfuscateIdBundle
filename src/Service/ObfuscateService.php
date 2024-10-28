<?php

namespace Zepekegno\ObfuscateIdBundle\Service;

use Random\RandomException;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;

final class ObfuscateService implements ObfuscateIdInterface
{
	private const CIPHER = 'aes-256-cbc';

	public function __construct(private readonly string $secretKey)
	{
		if (strlen($this->secretKey) !== 32) {
			throw new \InvalidArgumentException('The secret key must be 32 bytes long for AES-256 encryption.');
		}
	}

	/**
	 * @throws RandomException
	 */
	public function obfuscate(int $value): string
	{
		$iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
		$encrypted = openssl_encrypt($value, self::CIPHER, $this->secretKey, 0, $iv);
		if ($encrypted === false) {
			throw new \RuntimeException('Failed to encrypt the ID.');
		}
		// Combine IV and encrypted data, then convert to hexadecimal (URL-safe)
		return bin2hex($iv . $encrypted);
	}

	public function deobfuscate(string $value): ?int
	{
		// Validate if the input is a valid hexadecimal string and has an even length
		if (!ctype_xdigit($value) || strlen($value) % 2 !== 0) {
			return null; // Not a valid hex string
		}
		$data = hex2bin($value);
		if ($data === false) {
			return null;
		}
		$ivLength = openssl_cipher_iv_length(self::CIPHER);
		$iv = substr($data, 0, $ivLength);
		$encryptedData = substr($data, $ivLength);
		$decrypted = openssl_decrypt($encryptedData, self::CIPHER, $this->secretKey, 0, $iv);
		if ($decrypted === false) {
			return null;
		}
		return (int)$decrypted;
	}
}
