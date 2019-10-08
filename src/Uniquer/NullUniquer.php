<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Uniquer;

use SixtyEightPublishers;

final class NullUniquer extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements IUniquer
{
	/**
	 * {@inheritdoc}
	 */
	public function makeUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): string
	{
		return $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): bool
	{
		return TRUE;
	}
}
