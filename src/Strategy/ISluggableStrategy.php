<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

interface ISluggableStrategy extends SixtyEightPublishers\DoctrineSluggable\IAdjustable
{
	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper
	 * @param array                                                              $options
	 *
	 * @return void
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, array $options): void;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper
	 * @param array                                                              $options
	 *
	 * @return void
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $wrapper, array $options): void;
}
