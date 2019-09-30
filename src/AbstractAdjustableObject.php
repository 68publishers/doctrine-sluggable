<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

abstract class AbstractAdjustableObject implements IAdjustable
{
	/**
	 * {@inheritdoc}
	 */
	public static function getOption(array $options, string $name, $default = NULL)
	{
		foreach ($options as $option) {
			if ($option instanceof Annotation\Option && $option->name === $name) {
				return $option->value;
			}
		}

		return $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function assertOptions(array $options, Doctrine\ORM\Mapping\ClassMetadata $metadata): void
	{
	}
}
