<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'field' => 'unique based field',
 * )
 */
class FieldBasedSimilarSlugFinder extends DefaultSimilarSlugFinder
{
	use TFieldBasedSimilarSlugFinder;

	public const OPTION_FIELD = 'field';

	/**
	 * {@inheritdoc}
	 */
	protected function getTraitConfiguration(): array
	{
		return [
			self::OPTION_FIELD,
			'|&',
			TRUE,
		];
	}
}
