<?php

namespace tests\Service;

use PHPUnit\Framework\TestCase;
use Random\RandomException;
use Zepekegno\ObfuscateIdBundle\Service\ObfuscateService;

class ObfuscateServiceTest extends TestCase
{

	private ObfuscateService $obfuscateService;

	protected function setUp(): void
	{
		$this->obfuscateService = new ObfuscateService('30e5d8b844cdace13dfb87e0ffbe957d');
	}

	/**
	 * @throws RandomException
	 */
	public function testObfuscateAndDeobfuscate(): void
	{
		$originalId = 12345;

		// Test that the obfuscate method works
		$obfuscatedId = $this->obfuscateService->obfuscate($originalId);
		$this->assertNotEmpty($obfuscatedId, 'Obfuscation should return a non-empty string.');

		// Test that the deobfuscate method works and returns the original ID
		$deobfuscatedId = $this->obfuscateService->deobfuscate($obfuscatedId);
		$this->assertSame($originalId, $deobfuscatedId, 'Deobfuscation should return the original ID.');
	}

	public function testInvalidHexStringReturnsNull(): void
	{
		// Test that an invalid hex string returns null
		$invalidHexString = 'invalidhexstring';
		$this->assertNull($this->obfuscateService->deobfuscate($invalidHexString), 'Invalid hex string should return null.');
	}

	public function testDifferentObfuscatedResultsForSameId(): void
	{
		$originalId = 12345;

		// Obfuscating the same ID should return different results due to random IV
		$obfuscatedId1 = $this->obfuscateService->obfuscate($originalId);
		$obfuscatedId2 = $this->obfuscateService->obfuscate($originalId);

		$this->assertNotSame($obfuscatedId1, $obfuscatedId2, 'Obfuscating the same ID should result in different strings.');
	}
}
