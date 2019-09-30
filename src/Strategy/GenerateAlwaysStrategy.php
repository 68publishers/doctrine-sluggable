<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 * )
 */
class GenerateAlwaysStrategy extends AbstractFieldsBasedStrategy
{
	/**
	 * {@inheritdoc}
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, array $options): void
	{
		$wrapper->setUniqueSlugFromFields($this->getFields($options));
	}

	/**
	 * {@inheritdoc}
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, array $options): void
	{
		$fields = $this->getFields($options);

		$isChanged  = (bool) array_sum(array_map(static function (string $field) use ($wrapper): int {
			return (int) $wrapper->isFieldChanged($field);
		}, $fields));

		if ($isChanged) {
			$wrapper->setUniqueSlugFromFields($fields);
		}
	}
}
