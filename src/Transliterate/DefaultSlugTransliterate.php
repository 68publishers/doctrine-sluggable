<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterate;

use Behat;
use SixtyEightPublishers;

/**
 * $options = array(
 *      'separator' => '-',
 *      'fieldSeparator' => '-',
 * )
 */
class DefaultSlugTransliterate extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISlugTransliterate
{
	public const    OPTION_SEPARATOR = 'separator',
					OPTION_FIELD_SEPARATOR = 'fieldSeparator',
					DEFAULT_SEPARATOR = '-',
					DEFAULT_FIELD_SEPARATOR = '-';

	/**
	 * {@inheritdoc}
	 */
	public function createSlug(array $fields, array $options): string
	{
		$separator = self::getOption($options, self::OPTION_SEPARATOR, self::DEFAULT_SEPARATOR);
		$fieldSeparator = self::getOption($options, self::OPTION_FIELD_SEPARATOR, self::DEFAULT_FIELD_SEPARATOR);

		$fields = array_map(static function (string $field) use ($separator): string {
			return Behat\Transliterator\Transliterator::urlize(
				Behat\Transliterator\Transliterator::transliterate($field, $separator),
				$separator
			);
		}, $fields);

		return implode($fieldSeparator, $fields);
	}
}
