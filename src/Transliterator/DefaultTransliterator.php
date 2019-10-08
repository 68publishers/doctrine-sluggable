<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterator;

use Behat;
use SixtyEightPublishers;

/**
 * $options = array(
 *      'separator' => '-',
 *      'fieldSeparator' => '-',
 * )
 */
final class DefaultTransliterator extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ITransliterator
{
	public const    OPTION_SEPARATOR = 'separator',
					OPTION_FIELD_SEPARATOR = 'fieldSeparator';

	public const    DEFAULT_SEPARATOR = '-',
					DEFAULT_FIELD_SEPARATOR = '-';

	/** @var array  */
	protected $defaults = [
		self::OPTION_SEPARATOR => self::DEFAULT_SEPARATOR,
		self::OPTION_FIELD_SEPARATOR => self::DEFAULT_FIELD_SEPARATOR,
	];

	/**
	 * {@inheritdoc}
	 */
	public function transliterate(array $fields): string
	{
		$separator = $this->getOption(self::OPTION_SEPARATOR);
		$fieldSeparator = $this->getOption(self::OPTION_FIELD_SEPARATOR);

		$fields = array_map(static function (string $field) use ($separator): string {
			return Behat\Transliterator\Transliterator::transliterate(trim($field), $separator);
		}, $fields);

		return implode($fieldSeparator, $fields);
	}
}
