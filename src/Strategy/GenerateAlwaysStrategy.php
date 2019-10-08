<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 *      'datetimeFormat' => 'j.n.Y',
 * )
 */
final class GenerateAlwaysStrategy extends AbstractFieldsBasedStrategy
{
	/**
	 * {@inheritdoc}
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$this->setSlugFromFields($definition, $adapter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$uow = $adapter->getEntityManager()->getUnitOfWork();
		$changes = $uow->getEntityChangeSet($adapter->getEntity());

		foreach (array_merge($this->getFields(), $definition->getFinder()->getTrackedFields()) as $field) {
			if (!isset($changes[$field]) || $changes[$field][0] === $changes[$field][1]) {
				continue;
			}

			$this->setSlugFromFields($definition, $adapter);

			return;
		}
	}
}
