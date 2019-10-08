<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use Doctrine;
use SixtyEightPublishers;

/**
 * $options = array(
 *      'field' => 'unique based field',
 *      'type' => 'inner'
 * )
 *
 * Types:
 *
 * 1) `inner` - slug duplicates are allowed with different values of the `field` only
 * 2) `outer` - slugs duplicates are allowed with the same value of the `field` only
 */
final class FieldBasedSimilarSlugFinder extends DefaultSimilarSlugFinder
{
	public const    TYPE_INNER = 'inner',
					TYPE_OUTER = 'outer';

	public const    OPTION_FIELD = 'field',
					OPTION_TYPE = 'type',
					DEFAULT_TYPE = self::TYPE_INNER;

	/** @var array  */
	protected $defaults = [
		self::OPTION_TYPE => self::DEFAULT_TYPE,
	];

	/**
	 * {@inheritDoc}
	 */
	public function assertOptions(array $options): void
	{
		$field = $options[self::OPTION_FIELD] ?? NULL;
		$type = $options[self::OPTION_TYPE];

		if (NULL === $field || empty($field)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Field options is missing or empty.');
		}

		if (!in_array($type, [self::TYPE_INNER, self::TYPE_OUTER], TRUE)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Value "%s" is not valid type.',
				$type
			));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTrackedFields(): array
	{
		return [
			$this->getFieldOption(),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function pushPersistedSlug(array &$persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $slug): void
	{
		$persisted[$this->getFieldValueString($adapter)][] = $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filterPersistedSlugs(array $persisted, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): array
	{
		$value = $this->getFieldValueString($adapter);
		$type = $this->getTypeOption();

		switch ($type) {
			case self::TYPE_INNER:
				return $persisted[$value] ?? [];

			case self::TYPE_OUTER:
				if (isset($persisted[$value])) {
					unset($persisted[$value]);
				}

				return array_merge(...array_values($persisted));
		}

		return [];
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 * @param string                                                               $fieldName
	 * @param string                                                               $slug
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 */
	protected function createQuery(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, string $fieldName, string $slug): Doctrine\ORM\QueryBuilder
	{
		$query = parent::createQuery($adapter, $fieldName, $slug);

		$typeOption = $this->getTypeOption();
		$fieldBase = $this->getFieldOption();

		$metadata = $adapter->getClassMetadata();
		$base = $adapter->getValue($fieldBase);
		$mapping = array_key_exists($fieldBase, $metadata->getAssociationMappings()) ? $metadata->getAssociationMapping($fieldBase) : NULL;


		if (($base || $base === 0) && NULL === $mapping) {
			$query->andWhere($this->eqComparison($typeOption, $query, 'rec.' . $fieldBase, ':unique_base'))
				->setParameter(':unique_base', $base, $metadata->getTypeOfField($fieldBase));
		} elseif ($base && NULL !== $mapping && $this->isAssociationToOne($mapping['type'])) {
			$associationAlias = 'mapped_'. $fieldBase;
			$associationAdapter = SixtyEightPublishers\DoctrineSluggable\EntityAdapter\EntityAdapterFactory::create($adapter->getEntityManager(), $base);
			$associationMetadata = $associationAdapter->getClassMetadata();

			$query->innerJoin('rec.' . $fieldBase, $associationAlias);

			foreach (array_keys($mapping['targetToSourceKeyColumns']) as $i => $mappedKey) {
				$mappedProperty = $associationMetadata->fieldNames[$mappedKey];
				$mappedPropertyValue = $associationAdapter->getValue($mappedProperty);

				if (NULL === $mappedPropertyValue) {
					$query->andWhere($this->nullComparison($typeOption, $query, $associationAlias . '.' . $mappedProperty));
				} else {
					$query->andWhere($this->eqComparison($typeOption, $query, $associationAlias . '.' . $mappedProperty, ':assoc_' . $i))
						->setParameter(':assoc_' . $i, $mappedPropertyValue, $associationMetadata->getTypeOfField($mappedProperty));
				}
			}
		} else {
			$query->andWhere($this->nullComparison($typeOption, $query, 'rec.' . $fieldBase));
		}

		return $query;
	}

	/**
	 * @param string                     $type
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param mixed                      $x
	 * @param mixed                      $y
	 *
	 * @return \Doctrine\ORM\Query\Expr\Comparison
	 */
	private function eqComparison(string $type, Doctrine\ORM\QueryBuilder $qb, $x, $y): Doctrine\ORM\Query\Expr\Comparison
	{
		return self::TYPE_INNER === $type
			? $qb->expr()->eq($x, $y)
			: $qb->expr()->neq($x, $y);
	}

	/**
	 * @param string                     $type
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param string                     $x
	 *
	 * @return string
	 */
	private function nullComparison(string $type, Doctrine\ORM\QueryBuilder $qb, string $x): string
	{
		return self::TYPE_INNER === $type
			? $qb->expr()->isNull($x)
			: $qb->expr()->isNotNull($x);
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	private function isAssociationToOne(int $type): bool
	{
		return in_array($type, [
			Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE,
			Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE,
		], TRUE);
	}

	/**
	 * @return string
	 */
	private function getFieldOption(): string
	{
		return (string) $this->getOption(self::OPTION_FIELD);
	}

	/**
	 * @return string
	 */
	private function getTypeOption(): string
	{
		return (string) $this->getOption(self::TYPE_INNER);
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 *
	 * @return string
	 */
	private function getFieldValueString(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): string
	{
		$fieldName = $this->getFieldOption();
		$metadata = $adapter->getClassMetadata();
		$fieldValue = $adapter->getValue($fieldName);

		if (NULL !== $fieldValue && $metadata->hasAssociation($fieldName) && $metadata->isSingleValuedAssociation($fieldName)) {
			$associatedAdapter = SixtyEightPublishers\DoctrineSluggable\EntityAdapter\EntityAdapterFactory::create($adapter->getEntityManager(), $fieldValue);

			return implode('__', (array) $associatedAdapter->getIdentifier(FALSE));
		}

		return (string) $fieldValue;
	}
}
