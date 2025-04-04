<?php

namespace Zepekegno\ObfuscateIdBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder('obfuscate_id');

		$treeBuilder->getRootNode()
			->children()
			->scalarNode('obfuscate_secret_key')
			->defaultNull()
			->end();

		return $treeBuilder;
	}
}
