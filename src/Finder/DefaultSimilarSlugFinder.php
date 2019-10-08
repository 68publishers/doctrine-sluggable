<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use Doctrine;
use SixtyEightPublishers;

class DefaultSimilarSlugFinder extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISimilarSlugFinder
{
	/**
	 * {@inheritdoc}
	 */
	public function getSimilarSlugs(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): array
	{
		$query = $this->createQuery($adapter, $fieldName, $slug);

		$result = $query->getQuery()
			->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY)
			->execute();

		return array_unique(array_map(static function ($singleResult) use ($fieldName): string {
			return (string) $singleResult[$fieldName];
		}, $result));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTrackedFields(): array
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function pushPersistedSlug(array &$persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $slug): void
	{
		$persisted[] = $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filterPersistedSlugs(array $persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): array
	{
		return $persisted;
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 * @param string                                                               $fieldName
	 * @param string                                                               $slug
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function createQuery(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): Doctrine\ORM\QueryBuilder
	{
		$metadata = $adapter->getClassMetadata();
		$query = $adapter->getEntityManager()->createQueryBuilder();

		$query->select('rec.'.$fieldName)
			->from($adapter->getRootEntityName(), 'rec')
			->where($query->expr()->like(
				'rec.'.$fieldName,
				':slug'
			))
			->setParameter('slug', $slug.'%');

		foreach ((array) $adapter->getIdentifier(FALSE) as $idField => $value) {
			$name = str_replace('.', '_', $idField);

			$query->andWhere($query->expr()->neq('rec.' . $idField, ':' . $name));
			$query->setParameter($name, $value, $metadata->getTypeOfField($idField));
		}

		return $query;
	}
}
