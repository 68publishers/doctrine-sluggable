<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Uniquer;

use SixtyEightPublishers;

interface IUniquer
{
	/**
	 * @param string                                                                $slug
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter  $adapter
	 * @param \SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder
	 *
	 * @return string
	 */
	public function makeUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): string;

	/**
	 * @param string                                                                $slug
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter  $adapter
	 * @param \SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder
	 *
	 * @return bool
	 */
	public function isUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): bool;
}
