<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'field' => 'unique based field',
 * )
 */
class NegativeFieldBasedSimilarSlugFinder extends DefaultSimilarSlugFinder
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
			'|!',
			FALSE,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function matchDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, string $key): bool
	{
		$defKey = $this->createDefinitionKey($wrapper);

		if (FALSE !== strpos($defKey, '|!')) {
			[ $keyBase, $_ ] = explode('|!', $defKey);

			return 0 === strncmp($defKey, $keyBase, strlen($keyBase)) && $defKey !== $key;
		}

		return FALSE;
	}
}
