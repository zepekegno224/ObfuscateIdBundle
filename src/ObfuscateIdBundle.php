<?php

namespace Zepekegno\ObfuscateIdBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zepekegno\ObfuscateIdBundle\DependencyInjection\ObfuscateIdExtension;

class ObfuscateIdBundle extends Bundle
{


	public function getContainerExtension(): ?ExtensionInterface
	{
		if (null === $this->extension) {
			$this->extension = new ObfuscateIdExtension();
		}
		return $this->extension;
	}
}
