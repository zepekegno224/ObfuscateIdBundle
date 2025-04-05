<?php

namespace Zepekegno\ObfuscateIdBundle\ValueResolver;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;
use Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute\ObfuscateId;

final readonly class ObfuscateIdValueResolver implements ValueResolverInterface
{
	public function __construct(
		private readonly ObfuscateIdInterface   $obfuscate,
		private readonly EntityManagerInterface $entityManager
	)
	{
	}

	public function resolve(Request $request, ArgumentMetadata $argument): iterable
	{

		$attribute = $argument->getAttributes(ObfuscateId::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
		$routeParam = $request->attributes->get($argument->getName()) ? $argument->getName() : $attribute?->routeParam ?? 'id';
		$argumentType = $argument->getType();

		// Ensure the request contains a valid parameter
		$obfuscatedValue = $request->attributes->get($routeParam);

		if (!$obfuscatedValue) {
			return [];
		}

		if (!$this->obfuscate->isObfuscated($obfuscatedValue)) {
			return [];
		}

		$deobfuscatedId = $this->obfuscate->deobfuscate($obfuscatedValue);

		// Case 1: The controller expects an integer ID
		if ($argumentType === 'int') {
			return [(int)$deobfuscatedId];
		}

		// Case 2: The controller expects an entity, with or without the #[ObfuscateId] attribute
		$entityClass = $attribute?->entity ?? $argumentType;

		if ($attribute instanceof ObfuscateId) {
			return $this->deObfuscateAndHydrateEntity($deobfuscatedId, $attribute->entity ?? $entityClass, $attribute->identifierField);
		}

		if (class_exists($entityClass)) {
			return $this->deObfuscateAndHydrateEntity($deobfuscatedId, $entityClass, $routeParam);
		}

		return [];
	}


	private function deObfuscateAndHydrateEntity(string $deobfuscatedId, string $entityClass, string $identifierField = 'id'): array
	{

		$repository = $this->entityManager->getRepository($entityClass);
		$entity = $repository->findOneBy([$identifierField => $deobfuscatedId]);

		if (!$entity) {
			return [];
		}

		return [$entity];
	}
}
