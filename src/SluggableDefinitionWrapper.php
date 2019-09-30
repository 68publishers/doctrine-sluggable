<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

final class SluggableDefinitionWrapper
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\SluggableDefinition  */
	private $definition;

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/** @var object */
	private $object;

	/** @var \Doctrine\ORM\Mapping\ClassMetadata  */
	private $metadata;

	/** @var array */
	private $similarSlugs = [];

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinition $definition
	 * @param \Doctrine\ORM\EntityManagerInterface                        $em
	 * @param object                                                      $object
	 */
	public function __construct(SluggableDefinition $definition, Doctrine\ORM\EntityManagerInterface $em, $object)
	{
		$this->definition = $definition;
		$this->em = $em;
		$this->object = $object;
		$this->metadata = $em->getClassMetadata(get_class($object));
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\SluggableDefinition
	 */
	public function getDefinition(): SluggableDefinition
	{
		return $this->definition;
	}

	/**
	 * @return string
	 */
	public function getEntityName(): string
	{
		return $this->definition->getEntityName();
	}

	/**
	 * @return string
	 */
	public function getFieldName(): string
	{
		return $this->definition->getFieldName();
	}

	/**
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getValue(string $fieldName)
	{
		$value = $this->metadata
			->getReflectionProperty($fieldName)
			->getValue($this->object);

		if ($this->metadata->hasAssociation($fieldName) && $this->metadata->isSingleValuedAssociation($fieldName) && NULL !== $value) {
			$ids = $this->em->getClassMetadata(get_class($value))->getIdentifierValues($value);

			return 0 >= count($ids) ? NULL : reset($ids);
		}

		return $value;
	}

	/**
	 * @return string
	 */
	public function getSlugValue(): string
	{
		return (string) $this->getValue($this->getFieldName());
	}

	/**
	 * @param string $propertyName
	 *
	 * @return bool
	 */
	public function isFieldChanged(string $propertyName): bool
	{
		$uow = $this->em->getUnitOfWork();

		if ($uow->isScheduledForInsert($this->object)) {
			return TRUE;
		}

		$changes = $uow->getEntityChangeSet($this->object);

		if (!array_key_exists($propertyName, $changes)) {
			return FALSE;
		}

		[ $oldValue, $newValue ] = $changes[$propertyName];

		return $oldValue !== $newValue;
	}

	/**
	 * @return bool
	 */
	public function isSlugChanged(): bool
	{
		return $this->isFieldChanged($this->getFieldName());
	}

	/**
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function isUnique(string $slug): bool
	{
		return $this->definition
			->getUniquer()
			->isUnique($slug, $this->getSimilarSlugs($slug), $this->definition->getUniquerOptions());
	}

	/**
	 * @param string $slug
	 *
	 * @return void
	 */
	public function setSlug(string $slug): void
	{
		if (!$this->isUnique($slug)) {
			throw new Exception\UniqueSlugException("Slug '{$slug}' is not unique.");
		}

		$this->setSlugFieldValue($slug);
	}

	/**
	 * @param array $fieldNames
	 *
	 * @return void
	 */
	public function setUniqueSlugFromFields(array $fieldNames): void
	{
		$values = array_map(function ($fieldName) {
			$value = $this->metadata
				->getReflectionProperty($fieldName)
				->getValue($this->object);

			return $value instanceof \DateTime ? $value->format(\DateTime::ATOM) : $value;  # @todo: add format to configuration
		}, $fieldNames);

		$slug = $this->definition
			->getTransliterate()
			->createSlug($values, $this->definition->getTransliterateOptions());

		$this->setSlugFieldValue($this->makeSlugUnique($slug));
	}

	/**
	 * @param string $slug
	 *
	 * @return string
	 */
	public function makeSlugUnique(string $slug): string
	{
		$slug = $this->definition
			->getUniquer()
			->createUnique($slug, $this->getSimilarSlugs($slug), $this->definition->getUniquerOptions());

		return $slug;
	}

	/**
	 * @param string $slug
	 *
	 * @return array
	 */
	public function getSimilarSlugs(string $slug): array
	{
		if (!array_key_exists($slug, $this->similarSlugs)) {
			$this->similarSlugs[$slug] = array_merge(
				$this->definition->getFinder()->getSimilarSlugs($this->em, $this->object, $this->getFieldName(), $slug, $this->definition->getFinderOptions()),
				$this->getPersistedSlugs()
			);
		}

		return $this->similarSlugs[$slug];
	}

	/**
	 * @return array
	 */
	private function getPersistedSlugs(): array
	{
		return PersistedSlugStorage::get($this);
	}

	/**
	 * @param string $slug
	 *
	 * @return void
	 */
	private function addPersistedSlug(string $slug): void
	{
		PersistedSlugStorage::add($this, $slug);
	}

	/**
	 * @param string $slug
	 *
	 * @return void
	 */
	private function setSlugFieldValue(string $slug): void
	{
		$uow = $this->em->getUnitOfWork();
		$property = $this->metadata->getReflectionProperty($this->getFieldName());
		$oldValue = $property->getValue($this->object);

		$property->setValue($this->object, $slug);
		$this->addPersistedSlug($slug);

		$uow->recomputeSingleEntityChangeSet($this->metadata, $this->object);
		$uow->propertyChanged($this->object, $this->getFieldName(), $oldValue, $slug);
	}
}
