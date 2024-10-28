<?php

namespace Zepekegno\ObfuscateIdBundle\Twig\Extension;

use Twig\Extension\RuntimeExtensionInterface;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;

final readonly class ObfuscateRuntime implements RuntimeExtensionInterface
{
	public function __construct(private ObfuscateIdInterface $obfuscateService)
	{
	}

	/**
	 * Méthode associée au filtre "offusquer".
	 */
	public function obfuscate(int $value): string
	{
		return $this->obfuscateService->obfuscate($value);
	}

	/**
	 * Méthode associée au filtre "offusquer".
	 */
	public function deobfuscate(string $value): int
	{
		return $this->obfuscateService->deobfuscate($value);
	}
}
