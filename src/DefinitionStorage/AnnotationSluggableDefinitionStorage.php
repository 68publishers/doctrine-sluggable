<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\DefinitionStorage;

use Doctrine;
use SixtyEightPublishers;

final class AnnotationSluggableDefinitionStorage implements ISluggableDefinitionStorage
{
	/** @var \Doctrine\Common\Annotations\Reader  */
	private $reader;

	/** @var array  */
	private $definitionStorage = [];

	/**
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(Doctrine\Common\Annotations\Reader $reader)
	{
		$this->reader = $reader;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Doctrine\ORM\Mapping\MappingException
	 * @throws \Psr\Cache\InvalidArgumentException
	 * @throws \ReflectionException
	 */
	public function findSluggableDefinitions(Doctrine\ORM\EntityManagerInterface $em, string $entityClassName): array
	{
		$metadata = $em->getClassMetadata($entityClassName);
		$name = $metadata->getName() . '_sluggable_field';

		if (isset($this->definitionStorage[$name])) {
			return $this->definitionStorage[$name];
		}

		$cache = $em->getConfiguration()->getMetadataCache();
		$cacheItem = $cache ? $cache->getItem($name) : NULL;

		if (NULL !== $cacheItem && $cacheItem->isHit()) {
			return $this->definitionStorage[$name] = unserialize($cacheItem->get(), [
				'allowed_classes' => TRUE,
			]);
		}

		$this->definitionStorage[$name] = $this->createSluggableDefinitions($metadata);

		if (NULL !== $cacheItem) {
			$cacheItem->set(serialize($this->definitionStorage[$name]));
		}

		return $this->definitionStorage[$name];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Doctrine\ORM\Mapping\MappingException
	 * @throws \ReflectionException
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function getSluggableDefinition(Doctrine\ORM\EntityManagerInterface $em, string $entityClassName, string $fieldName): SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition
	{
		$definitions = $this->findSluggableDefinitions($em, $entityClassName);

		if (!isset($definitions[$fieldName])) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
				'Field %s::$%s is not Sluggable.',
				$entityClassName,
				$fieldName
			));
		}

		return $definitions[$fieldName];
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition[]
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 * @throws \ReflectionException
	 */
	private function createSluggableDefinitions(Doctrine\ORM\Mapping\ClassMetadata $metadata): array
	{
		$definitions = [];
		$reflectionClass = $metadata->getReflectionClass();

		if ($metadata->isMappedSuperclass || !$reflectionClass->isInstantiable()) {
			return  $definitions;
		}

		foreach ($metadata->getFieldNames() as $fieldName) {
			if (!$reflectionClass->hasProperty($fieldName)) {
				continue;
			}

			/** @var NULL|\SixtyEightPublishers\DoctrineSluggable\Annotation\Slug $slug */
			$slug = $this->reader->getPropertyAnnotation($reflectionClass->getProperty($fieldName), SixtyEightPublishers\DoctrineSluggable\Annotation\Slug::class);

			if (NULL === $slug) {
				continue;
			}

			$mapping = $metadata->getFieldMapping($fieldName);
			$entityName = ($metadata->isInheritedField($fieldName) ? new \ReflectionClass($mapping['declared']) : $reflectionClass)->getName();

			$definitions[$fieldName] = $slug->createDefinition($entityName, $fieldName);
		}

		return $definitions;
	}
}
