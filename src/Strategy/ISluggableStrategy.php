<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

interface ISluggableStrategy
{
	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 *
	 * @return void
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 *
	 * @return void
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void;
}
