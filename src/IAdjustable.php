<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

interface IAdjustable
{
	/**
	 * @param array  $options
	 * @param string $name
	 * @param NULL   $default
	 *
	 * @return mixed
	 */
	public static function getOption(array $options, string $name, $default = NULL);

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Option[] $options
	 * @param \Doctrine\ORM\Mapping\ClassMetadata                         $metadata
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function assertOptions(array $options, Doctrine\ORM\Mapping\ClassMetadata $metadata): void;
}
