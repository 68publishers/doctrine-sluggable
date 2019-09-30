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
	public function getSimilarSlugs(Doctrine\ORM\EntityManagerInterface $em, $object, string $fieldName, string $slug, array $options): array
	{
		$query = $this->createQuery($em, $object, $fieldName, $slug, $options);

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
	public function createDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper): string
	{
		return $wrapper->getEntityName() . '::' . $wrapper->getFieldName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function matchDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, string $key): bool
	{
		return $this->createDefinitionKey($wrapper) === $key;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function assertOptions(array $options, Doctrine\ORM\Mapping\ClassMetadata $metadata): void
	{
	}

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $object
	 * @param string                               $fieldName
	 * @param string                               $slug
	 * @param array                                $options
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function createQuery(Doctrine\ORM\EntityManagerInterface $em, $object, string $fieldName, string $slug, array $options): Doctrine\ORM\QueryBuilder
	{
		$metadata = $em->getClassMetadata(get_class($object));
		$query = $em->createQueryBuilder();

		return $query->select('rec.'.$fieldName)
			->from($metadata->rootEntityName, 'rec')
			->where($query->expr()->like(
				'rec.'.$fieldName,
				':slug'
			))
			->setParameter('slug', $slug.'%');
	}
}
