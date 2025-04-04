<?php

namespace Zepekegno\ObfuscateIdBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use ReflectionClass;
use ReflectionException;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;
use Zepekegno\ObfuscateIdBundle\Service\ObfuscateService;
use Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute\Obfuscate;

#[AsEntityListener(event: 'postLoad')]
final class ObfuscateIdListener
{
	public function __construct(private readonly ObfuscateIdInterface $obfuscateService)
	{
	}

	/**
	 * @throws ReflectionException
	 */
	public function postLoad(LifecycleEventArgs $args): void
	{
		$entity = $args->getObject();
		$reflection = new ReflectionClass($entity);

		foreach ($reflection->getProperties() as $property) {
			if (!$property->getAttributes(Obfuscate::class)) {
				continue;
			}

			$value = $property->getValue($entity);

			if ($value !== null) {
				$obfuscatedValue = $this->obfuscateService->obfuscate($value);
				$property->setValue($entity, $obfuscatedValue);
			}
		}
	}
}
