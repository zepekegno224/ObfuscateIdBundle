<?php

namespace Zepekegno\ObfuscateIdBundle\ValueResolver;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;
use Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute\ObfuscateId;

/**
 * Resolves controller parameters containing obfuscated IDs.
 *
 * This method analyzes request parameters and attributes to:
 * 1. Detect obfuscated values
 * 2. Deobfuscate them
 * 3. Convert to entities or primitive values based on expected type
 *
 * @param Request $request The current HTTP request
 * @param ArgumentMetadata $argument The metadata of the parameter to resolve
 * @return iterable The resolved value (entity or ID) or an empty array if not resolved
 */
/**
 * Value resolver for obfuscated identifiers in Symfony requests.
 *
 * This class is responsible for:
 * - Automatic resolution of obfuscated IDs in route parameters
 * - Converting deobfuscated IDs into entities or primitive values
 * - Supporting custom attributes for behavior configuration
 */
final readonly class ObfuscateIdValueResolver implements ValueResolverInterface
{
	/**
	 * @param ObfuscateIdInterface $obfuscate Obfuscation service for decrypting IDs
	 * @param EntityManagerInterface $entityManager Doctrine entity manager for hydration
	 */
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


	/**
	 * Hydrates an entity from a deobfuscated ID.
	 *
	 * @param string $deobfuscatedId The deobfuscated ID
	 * @param string $entityClass The entity class to hydrate
	 * @param string $identifierField The identifier field name (defaults to 'id')
	 * @return array An array containing the hydrated entity or empty if not found
	 */
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
