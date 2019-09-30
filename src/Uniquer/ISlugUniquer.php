<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Uniquer;

use SixtyEightPublishers;

interface ISlugUniquer extends SixtyEightPublishers\DoctrineSluggable\IAdjustable
{
	/**
	 * @param string                                                      $slug
	 * @param array                                                       $similarSlugs
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Option[] $options
	 *
	 * @return string
	 */
	public function createUnique(string $slug, array $similarSlugs, array $options): string;

	/**
	 * @param string                                                      $slug
	 * @param array                                                       $similarSlugs
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Option[] $options
	 *
	 * @return bool
	 */
	public function isUnique(string $slug, array $similarSlugs, array $options): bool;
}
