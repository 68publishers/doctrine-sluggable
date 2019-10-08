<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Helper;

use Doctrine;
use SixtyEightPublishers;

final class UniqueSlugHelper
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\ISluggableDefinitionStorage  */
	private $storage;

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\ISluggableDefinitionStorage $storage
	 * @param \Doctrine\ORM\EntityManagerInterface                                                  $em
	 */
	public function __construct(SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\ISluggableDefinitionStorage $storage, Doctrine\ORM\EntityManagerInterface $em)
	{
		$this->storage = $storage;
		$this->em = $em;
	}

	/**
	 * @param object $object
	 * @param string $fieldName
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function isSlugUnique($object, string $fieldName, string $slug): bool
	{
		$definition = $this->storage->getSluggableDefinition($this->em, get_class($object), $fieldName);
		$adapter = SixtyEightPublishers\DoctrineSluggable\EntityAdapter\EntityAdapterFactory::create($this->em, $object);

		return $definition->getUniquer()->isUnique($slug, $adapter, $definition->getFinder());
	}
}
