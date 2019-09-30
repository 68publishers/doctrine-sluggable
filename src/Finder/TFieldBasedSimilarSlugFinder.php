<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use Doctrine;
use SixtyEightPublishers;

trait TFieldBasedSimilarSlugFinder
{
	/**
	 * 0 => Field option name
	 * 1 => Definition key separator
	 * 2 => TRUE|FALSE - positive or negative
	 *
	 * @return array
	 */
	abstract protected function getTraitConfiguration(): array;

	/**
	 * @param array                               $options
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function assertOptions(array $options, Doctrine\ORM\Mapping\ClassMetadata $metadata): void
	{
		/** @noinspection PhpUndefinedClassInspection */
		parent::assertOptions($options, $metadata);

		$field = self::getOption($options, self::OPTION_FIELD);

		if (NULL === $field) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Missing "field" option');
		}

		if (!$metadata->hasField($field) && !$metadata->hasAssociation($field)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Missing field "%s" in %s entity',
				$field,
				$metadata->getName()
			));
		}
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper
	 *
	 * @return string
	 */
	public function createDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper): string
	{
		$field = (string) self::getOption($wrapper->getDefinition()->getFinderOptions(), self::OPTION_FIELD);

		/** @noinspection PhpUndefinedClassInspection */
		return parent::createDefinitionKey($wrapper) . $this->getTraitConfiguration()[1] . $wrapper->getValue($field);
	}

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $object
	 * @param string                               $fieldName
	 * @param string                               $slug
	 * @param array                                $options
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 */
	protected function createQuery(Doctrine\ORM\EntityManagerInterface $em, $object, string $fieldName, string $slug, array $options): Doctrine\ORM\QueryBuilder
	{
		/** @noinspection PhpUndefinedClassInspection */
		$query = parent::createQuery($em, $object, $fieldName, $slug, $options);

		$this->initializeProxy($object);

		$fieldBase = self::getOption($options, $this->getTraitConfiguration()[0]);

		$metadata = $em->getClassMetadata(get_class($object));
		$base = $metadata->getReflectionProperty($fieldBase)->getValue($object);
		$mapping = array_key_exists($fieldBase, $metadata->getAssociationMappings()) ? $metadata->getAssociationMapping($fieldBase) : NULL;


		if (($base || $base === 0) && NULL === $mapping) {
			$query->andWhere($this->eqComparison($query, 'rec.' . $fieldBase, ':unique_base'))
				->setParameter(':unique_base', $base, $metadata->getTypeOfField($fieldBase));
		} elseif ($base && NULL !== $mapping && $this->isAssociationToOne($mapping['type'])) {
			$associationAlias = 'mapped_'. $fieldBase;
			$associationMetadata = $em->getClassMetadata(get_class($base));

			$this->initializeProxy($base);
			$query->innerJoin('rec.' . $fieldBase, $associationAlias);

			foreach (array_keys($mapping['targetToSourceKeyColumns']) as $i => $mappedKey) {
				$mappedProperty = $associationMetadata->fieldNames[$mappedKey];
				$mappedPropertyValue = $associationMetadata->getReflectionProperty($mappedProperty)->getValue($base);

				if (NULL === $mappedPropertyValue) {
					$query->andWhere($this->nullComparison($query, $associationAlias . '.' . $mappedProperty));
				} else {
					$query->andWhere($this->eqComparison($query, $associationAlias . '.' . $mappedProperty, ':assoc_' . $i))
						->setParameter(':assoc_' . $i, $mappedPropertyValue, $associationMetadata->getTypeOfField($mappedProperty));
				}
			}
		} else {
			$query->andWhere($this->nullComparison($query, 'rec.' . $fieldBase));
		}

		return $query;
	}

	/**
	 * @param object $object
	 *
	 * @return void
	 */
	private function initializeProxy($object): void
	{
		if ($object instanceof Doctrine\ORM\Proxy\Proxy && !$object->__isInitialized()) {
			$object->__load();
		}
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param mixed                      $x
	 * @param mixed                      $y
	 *
	 * @return \Doctrine\ORM\Query\Expr\Comparison
	 */
	private function eqComparison(Doctrine\ORM\QueryBuilder $qb, $x, $y): Doctrine\ORM\Query\Expr\Comparison
	{
		return TRUE === $this->getTraitConfiguration()[2]
			? $qb->expr()->eq($x, $y)
			: $qb->expr()->neq($x, $y);
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param string                     $x
	 *
	 * @return string
	 */
	private function nullComparison(Doctrine\ORM\QueryBuilder $qb, string $x): string
	{
		return TRUE === $this->getTraitConfiguration()[2]
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
}
