<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Helper;

use Doctrine;
use SixtyEightPublishers;

final class UniqueSlugHelper
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage  */
	private $storage;

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage $storage
	 * @param \Doctrine\ORM\EntityManagerInterface                               $em
	 */
	public function __construct(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage $storage, Doctrine\ORM\EntityManagerInterface $em)
	{
		$this->storage = $storage;
		$this->em = $em;
	}

	/**
	 * @param $object
	 * @param string $fieldName
	 * @param string $slug
	 *
	 * @return bool
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 * @throws \ReflectionException
	 */
	public function isSlugUnique($object, string $fieldName, string $slug): bool
	{
		foreach ($this->storage->getSluggableDefinitions($this->em, $object) as $field => $definition) {
			if ($field === $fieldName) {
				return (new SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper($definition, $this->em, $object))
					->isUnique($slug);
			}
		}

		throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
			'Field %s::$%s doesn\'t exists.',
			get_class($object),
			$fieldName
		));
	}
}
