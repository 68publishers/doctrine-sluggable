<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Finder;

use Doctrine;
use SixtyEightPublishers;

interface ISimilarSlugFinder extends SixtyEightPublishers\DoctrineSluggable\IAdjustable
{
	/**
	 * @param \Doctrine\ORM\EntityManagerInterface                        $em
	 * @param object                                                      $object
	 * @param string                                                      $fieldName
	 * @param string                                                      $slug
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Option[] $options
	 *
	 * @return array
	 */
	public function getSimilarSlugs(Doctrine\ORM\EntityManagerInterface $em, $object, string $fieldName, string $slug, array $options): array;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper
	 *
	 * @return string
	 */
	public function createDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper): string;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper
	 * @param string                                                             $key
	 *
	 * @return bool
	 */
	public function matchDefinitionKey(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, string $key): bool;
}
