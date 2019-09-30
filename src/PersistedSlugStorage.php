<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

final class PersistedSlugStorage
{
	/** @var array  */
	private static $slugs = [];

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $definition
	 * @param string                                                             $slug
	 *
	 * @return void
	 */
	public static function add(SluggableDefinitionWrapper $definition, string $slug): void
	{
		if (!isset(self::$slugs[$key = self::getKey($definition)])) {
			self::$slugs[$key] = [];
		}

		if (!in_array($slug, self::$slugs[$key], TRUE)) {
			self::$slugs[$key][] = $slug;
		}
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $definition
	 *
	 * @return string[]
	 */
	public static function get(SluggableDefinitionWrapper $definition): array
	{
		$result = [];

		foreach (self::$slugs as $key => $slugs) {
			if ($definition->getDefinition()->getFinder()->matchDefinitionKey($definition, $key)) {
				$result[] = $slugs;
			}
		}

		return array_merge([], ...$result);
	}

	/**
	 * @return void
	 */
	public static function flush(): void
	{
		self::$slugs = [];
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionWrapper $definition
	 *
	 * @return string
	 */
	private static function getKey(SluggableDefinitionWrapper $definition): string
	{
		return $definition
			->getDefinition()
			->getFinder()
			->createDefinitionKey($definition);
	}
}
