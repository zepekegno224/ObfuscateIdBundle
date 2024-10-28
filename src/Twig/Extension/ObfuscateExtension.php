<?php

namespace Zepekegno\ObfuscateIdBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class ObfuscateExtension extends AbstractExtension
{
	/**
	 * Définir les filtres disponibles dans Twig.
	 */
	public function getFilters(): array
	{
		return [
			new TwigFilter('obfuscate', [ObfuscateRuntime::class, 'obfuscate']),
			new TwigFilter('deobfuscate', [ObfuscateRuntime::class, 'deobfuscate']),
		];
	}

}
