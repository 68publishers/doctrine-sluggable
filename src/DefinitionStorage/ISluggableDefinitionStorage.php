<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\DefinitionStorage;

use Doctrine;
use SixtyEightPublishers;

interface ISluggableDefinitionStorage
{
	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param string                               $entityClassName
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition[]
	 */
	public function findSluggableDefinitions(Doctrine\ORM\EntityManagerInterface $em, string $entityClassName): array;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param string                               $entityClassName
	 * @param string                               $fieldName
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition
	 */
	public function getSluggableDefinition(Doctrine\ORM\EntityManagerInterface $em, string $entityClassName, string $fieldName): SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition;
}
