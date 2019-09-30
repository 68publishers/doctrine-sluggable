<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use Doctrine;
use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 * )
 */
abstract class AbstractFieldsBasedStrategy extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISluggableStrategy
{
	/**
	 * @param array $options
	 *
	 * @return array
	 */
	protected function getFields(array $options): array
	{
		return self::getOption($options, 'fields', []);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function assertOptions(array $options, Doctrine\ORM\Mapping\ClassMetadata $metadata): void
	{
		$fields = self::getOption($options, 'fields');

		if (NULL === $fields) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Missing "fields" option');
		}

		if (!is_array($fields)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Option "fields" must be array');
		}

		foreach ($fields as $field) {
			if ($metadata->hasField($field)) {
				continue;
			}

			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Missing field "%s" in %s entity',
				$field,
				$metadata->getName()
			));
		}
	}
}
