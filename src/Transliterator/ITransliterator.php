<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterator;

use SixtyEightPublishers;

interface ITransliterator
{
	/**
	 * @param string[] $fields
	 *
	 * @return string
	 */
	public function transliterate(array $fields): string;
}
