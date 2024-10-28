<?php

namespace Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute;

use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Zepekegno\ObfuscateIdBundle\ValueResolver\ObfuscateIdValueResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class ObfuscateId extends ValueResolver
{
	public function __construct(
		public ?string                     $identifierField = 'id',
		public ?string                     $routeParam = 'id',
		public ?string $entity = null,
	)
	{
		parent::__construct(ObfuscateIdValueResolver::class);
	}
}
