<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use SixtyEightPublishers;

final class SimilarSlugFinderProxy implements ISimilarSlugFinder
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder  */
	private $inner;

	/** @var string  */
	private $fieldName;

	/** @var array  */
	private $cache = [];

	/** @var array  */
	private $persisted = [];

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder $inner
	 * @param string                                                            $fieldName
	 */
	public function __construct(ISimilarSlugFinder $inner, string $fieldName)
	{
		$this->inner = $inner;
		$this->fieldName = $fieldName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSimilarSlugs(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): array
	{
		if ($fieldName !== $this->fieldName) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
				'Name of field must be same. Field "%s" is known by %s but field "%s" was passed into method %s.',
				$this->fieldName,
				self::class,
				$fieldName,
				__METHOD__
			));
		}

		$key = $adapter->getRootEntityName() . '::' . $fieldName . '=' . $slug;

		if (!isset($this->cache[$key])) {
			$this->cache[$key] = $this->inner->getSimilarSlugs($adapter, $fieldName, $slug);
		}

		return array_merge($this->cache[$key], $this->getPersistedSlugs($adapter));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTrackedFields(): array
	{
		return $this->inner->getTrackedFields();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException
	 */
	public function pushPersistedSlug(array &$persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $slug): void
	{
		throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
			'Method %s is not allowed.',
			__METHOD__
		));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException
	 */
	public function filterPersistedSlugs(array $persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): array
	{
		throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
			'Method %s is not allowed.',
			__METHOD__
		));
	}

	/**
	 * @return string
	 */
	public function getFieldName(): string
	{
		return $this->fieldName;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder
	 */
	public function unwrap(): ISimilarSlugFinder
	{
		return $this->inner;
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 * @param string                                                               $fieldName
	 * @param string                                                               $slug
	 *
	 * @return void
	 */
	public function addPersistedSlug(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): void
	{
		$key = $adapter->getRootEntityName() . '::' . $fieldName;

		if (!array_key_exists($key, $this->persisted)) {
			$this->persisted[$key] = [];
		}

		$this->inner->pushPersistedSlug(
			$this->persisted[$key],
			$adapter,
			$slug
		);
	}

	/**
	 * @return void
	 */
	public function invalidateCache(): void
	{
		$this->cache = [];
		$this->persisted = [];
	}

	/**
	 * @return array
	 */
	public function __sleep(): array
	{
		return [
			'inner',
			'fieldName',
		];
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 *
	 * @return array
	 */
	private function getPersistedSlugs(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): array
	{
		$key = $adapter->getRootEntityName() . '::' . $this->getFieldName();

		if (!array_key_exists($key, $this->persisted)) {
			return [];
		}

		return $this->inner->filterPersistedSlugs($this->persisted[$key], $adapter);
	}
}
