<?php

namespace Zepekegno\ObfuscateIdBundle\ValueResolver;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zepekegno\ObfuscateIdBundle\Contract\ObfuscateIdInterface;
use Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute\ObfuscateId;

final readonly class ObfuscateIdValueResolver implements ValueResolverInterface
{
	public function __construct(private readonly ObfuscateIdInterface $obfuscate, private readonly EntityManagerInterface $entityManager)
	{

	}


	/**
	 * @param Request $request
	 * @param ArgumentMetadata $argument
	 * @return iterable
	 */
	public function resolve(Request $request, ArgumentMetadata $argument): iterable
	{

		$attribute = $argument->getAttributes(ObfuscateId::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
		$argumentType = $argument->getType();
		if (!($attribute instanceof ObfuscateId)) {
			return [];
		}
		$attribute->entity = $attribute->entity ?? $argumentType;
		return $this->deObfuscateAndHydrateEntity($request, $attribute);
	}

	private function deobfuscateAndHydrateEntity($request, $attribute): array
	{
		if ($request->attributes->get($attribute->routeParam) === null) {
			throw new \InvalidArgumentException(sprintf('Some mandatory parameters are missing "%s" to deobfuscate', $attribute->routeParam));
		}
		$value = $this->obfuscate->deobfuscate($request->attributes->get($attribute->routeParam));

		if ($value === null) {
			throw new \InvalidArgumentException(sprintf('This value %s cannot be deobfuscated', $request->attributes->get($attribute->routeParam)));
		}
		$entity = $this->entityManager->getRepository($attribute->entity)->findOneBy([$attribute->identifierField => $value]);
		if (!$entity) {
			throw new NotFoundHttpException(sprintf('"%s" object not found by "%s".', $attribute->entity, self::class));
		}
		return [$entity] ?? [];
	}
}
