<?php

namespace Zepekegno\ObfuscateIdBundle\Contract;

/**
 * Interface ObfuscateIdInterface
 *
 * Defines the contract for ID obfuscation and deobfuscation.
 * This interface provides the necessary methods to secure IDs exposed
 * in URLs and forms.
 */
interface ObfuscateIdInterface
{
	/**
	 * Obfuscates a numeric identifier.
	 *
	 * @param int $value The ID to obfuscate
	 * @return string The obfuscated ID as a hexadecimal string
	 * @throws \RuntimeException If encryption fails
	 */
	public function obfuscate(int $value): string;

	/**
	 * Deobfuscates a previously obfuscated string.
	 *
	 * @param string $value The obfuscated string to decrypt
	 * @return int|null The original ID or null if deobfuscation fails
	 */
	public function deobfuscate(string $value): ?int;

	/**
	 * Checks if a string is a valid obfuscated value.
	 *
	 * @param string $value The string to check
	 * @return bool true if the string is a valid obfuscated value, false otherwise
	 */
	public function isObfuscated(string $value): bool;

}
