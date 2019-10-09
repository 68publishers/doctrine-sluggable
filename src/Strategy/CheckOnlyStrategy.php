<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * Check only, slug field must be set manually.
 *
 * $options = array()
 */
final class CheckOnlyStrategy extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISluggableStrategy
{
	/**
	 * {@inheritdoc}
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$this->checkUnique($definition, $adapter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$fieldName = $definition->getFieldName();
		$changes = $adapter->getEntityManager()->getUnitOfWork()->getEntityChangeSet($adapter->getEntity());

		if (!isset($changes[$fieldName]) || $changes[$fieldName][0] === $changes[$fieldName][1]) {
			return;
		}

		$this->checkUnique($definition, $adapter);
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 *
	 * @return void
	 */
	private function checkUnique(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$fieldName = $definition->getFieldName();
		$slug = $adapter->getValue($fieldName);

		if (!$definition->getUniquer()->isUnique($slug, $adapter, $definition->getFinder())) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\UniqueSlugException($slug, $adapter->getEntity(), $fieldName);
		}

		# add slug between persisted
		$definition->getFinder()->addPersistedSlug($adapter, $fieldName, $slug);
	}
}
