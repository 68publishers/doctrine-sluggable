<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use SixtyEightPublishers;

interface ISimilarSlugFinder
{
	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 * @param string                                                               $fieldName
	 * @param string                                                               $slug
	 *
	 * @return array
	 */
	public function getSimilarSlugs(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): array;

	/**
	 * @return array
	 */
	public function getTrackedFields(): array;

	/**
	 * @internal
	 *
	 * @param array                                                                $persisted
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 * @param string                                                               $slug
	 *
	 * @return void
	 */
	public function pushPersistedSlug(array &$persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $slug): void;

	/**
	 * @internal
	 *
	 * @param array                                                                $persisted
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 *
	 * @return array
	 */
	public function filterPersistedSlugs(array $persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): array;
}
