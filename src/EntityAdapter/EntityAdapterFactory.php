<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\EntityAdapter;

use Doctrine;
use SixtyEightPublishers;

final class EntityAdapterFactory
{
	/**
	 * @param \Doctrine\Persistence\ObjectManager $om
	 * @param $entity
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter
	 */
	public static function create(Doctrine\Persistence\ObjectManager $om, $entity): IEntityAdapter
	{
		if ($om instanceof Doctrine\ORM\EntityManagerInterface) {
			return new EntityAdapter($om, $entity);
		}

		throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException(sprintf(
			'Object manager %s is not supported.',
			get_class($om)
		));
	}
}
