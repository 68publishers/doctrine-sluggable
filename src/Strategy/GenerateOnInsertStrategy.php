<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 *      'checkOnUpdate' => FALSE
 * )
 */
class GenerateOnInsertStrategy extends AbstractFieldsBasedStrategy
{
	public const OPTION_CHECK_ON_UPDATE = 'checkOnUpdate';

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
		if (FALSE === self::getOption($options, self::OPTION_CHECK_ON_UPDATE, FALSE)) {
			return;
		}

		if (!$wrapper->isUnique($slug = $wrapper->getSlugValue())) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\UniqueSlugException(sprintf(
				'Slug "%s" is not unique.',
				$slug
			));
		}
	}
}
