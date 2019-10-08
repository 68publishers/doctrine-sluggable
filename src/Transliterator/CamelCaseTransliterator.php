<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterator;

use Behat;
use SixtyEightPublishers;

/**
 * $options = array()
 */
final class CamelCaseTransliterator extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ITransliterator
{
	/**
	 * {@inheritdoc}
	 */
	public function transliterate(array $fields): string
	{
		$slug = Behat\Transliterator\Transliterator::transliterate(implode('-', $fields), '-');

		$slug = preg_replace_callback('/^[a-z]|-[a-z]/smi', static function (array $assoc): string {
			return mb_strtoupper($assoc[0], 'UTF-8');
		}, $slug);

		return str_replace('-', '', $slug);
	}
}
