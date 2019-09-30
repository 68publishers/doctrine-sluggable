<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

final class SluggableDefinitionStorage
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
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $object
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\SluggableDefinition[]
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 * @throws \ReflectionException
	 */
	public function getSluggableDefinitions(Doctrine\ORM\EntityManagerInterface $em, $object): array
	{
		$metadata = $em->getClassMetadata(get_class($object));
		$name = $metadata->getName() . '_sluggable_field';

		if (isset($this->definitionStorage[$name])) {
			return $this->definitionStorage[$name];
		}

		/** @var \Doctrine\Common\Cache\Cache|NULL $cache */
		$cache = $em->getMetadataFactory()->getCacheDriver();

		if (NULL !== $cache && $cache->contains($name)) {
			return $this->definitionStorage[$name] = unserialize($cache->fetch($name), [
				'allowed_classes' => TRUE,
			]);
		}

		$this->definitionStorage[$name] = $this->createSluggableDefinitions($metadata);

		if (NULL !== $cache) {
			$cache->save($name, serialize($this->definitionStorage[$name]));
		}

		return $this->definitionStorage[$name];
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\SluggableDefinition[]
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
			$slug = $this->reader->getPropertyAnnotation($reflectionClass->getProperty($fieldName), Annotation\Slug::class);

			if (NULL === $slug) {
				continue;
			}

			$slug->validateFor($metadata);

			$mapping = $metadata->getFieldMapping($fieldName);
			$entityName = ($metadata->isInheritedField($fieldName) ? new \ReflectionClass($mapping['declared']) : $reflectionClass)->getName();

			$definitions[$fieldName] = new SluggableDefinition($slug, $entityName, $fieldName);
		}

		return $definitions;
	}
}
