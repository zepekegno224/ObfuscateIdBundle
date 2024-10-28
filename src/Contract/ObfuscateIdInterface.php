<?php

namespace Zepekegno\ObfuscateIdBundle\Contract;

interface ObfuscateIdInterface
{
	public function obfuscate(int $value): string;

	public function deobfuscate(string $value): ?int;

}
