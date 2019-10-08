<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

abstract class AbstractAdjustableObject
{
	/** @var array  */
	private $options;

	/** @var array  */
	protected $defaults = [];

	/**
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$this->options = array_merge($this->defaults, $options);

		$this->assertOptions($this->options);
	}

	/**
	 * @param array $options
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException
	 */
	protected function assertOptions(array $options): void
	{
	}

	/**
	 * @param string $name
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	protected function getOption(string $name, $default = NULL)
	{
		return $this->options[$name] ?? $default;
	}
}
