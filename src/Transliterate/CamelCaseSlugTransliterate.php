<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterate;

/**
 * $options = array(
 *      'separator' => '-',
 *      'fieldSeparator' => '-',
 * )
 */
class CamelCaseSlugTransliterate extends DefaultSlugTransliterate
{
	/**
	 * {@inheritdoc}
	 */
	public function createSlug(array $fields, array $options): string
	{
		$separator = self::getOption($options, self::OPTION_SEPARATOR, self::DEFAULT_SEPARATOR);

		$slug = preg_replace_callback('/^[a-z]|' . $separator . '[a-z]/smi', static function (array $assoc): string {
			return mb_strtoupper($assoc[0], 'UTF-8');
		}, parent::createSlug($fields, $options));

		return str_replace($separator, '', $slug);
	}
}
