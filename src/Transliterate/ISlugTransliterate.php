<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Transliterate;

use SixtyEightPublishers;

interface ISlugTransliterate extends SixtyEightPublishers\DoctrineSluggable\IAdjustable
{
	/**
	 * @param string[]                                                    $fields
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Option[] $options
	 *
	 * @return string
	 */
	public function createSlug(array $fields, array $options): string;
}
