<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Annotation;

use SixtyEightPublishers;

/**
 * @Annotation
 * @Target("PROPERTY")
 * @Attributes({
 *   @Attribute("strategy", type = "string"),
 *   @Attribute("finder", type = "string"),
 *   @Attribute("transliterator", type = "string"),
 *   @Attribute("uniquer", type = "string"),
 *   @Attribute("strategyOptions", type = "array"),
 *   @Attribute("finderOptions", type = "array"),
 *   @Attribute("transliteratorOptions", type = "array"),
 *   @Attribute("uniquerOptions", type = "array"),
 * })
 */
final class Slug
{
	/** @var array  */
	private $values;

	/**
	 * @param array $values
	 */
	public function __construct(array $values)
	{
		$this->values = $values;
	}

	/**
	 * @param string $entityName
	 * @param string $fieldName
	 *
	 * @return \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition
	 */
	public function createDefinition(string $entityName, string $fieldName): SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition
	{
		return new SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition(
			$this->createInstance(
				$this->values,
				'strategy',
				SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy::class,
				SixtyEightPublishers\DoctrineSluggable\Strategy\GenerateAlwaysStrategy::class
			),
			$this->createInstance(
				$this->values,
				'finder',
				SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder::class,
				SixtyEightPublishers\DoctrineSluggable\Finder\DefaultSimilarSlugFinder::class
			),
			$this->createInstance(
				$this->values,
				'transliterator',
				SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator::class,
				SixtyEightPublishers\DoctrineSluggable\Transliterator\DefaultTransliterator::class
			),
			$this->createInstance(
				$this->values,
				'uniquer',
				SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer::class,
				SixtyEightPublishers\DoctrineSluggable\Uniquer\NullUniquer::class
			),
			$entityName,
			$fieldName
		);
	}

	/**
	 * @param array  $values
	 * @param string $name
	 * @param string $parentClass
	 * @param string $defaultClass
	 *
	 * @return mixed
	 */
	private function createInstance(array $values, string $name, string $parentClass, string $defaultClass)
	{
		$class = $defaultClass;

		if (isset($values[$name])) {
			$class = $values[$name];

			$this->assertSubclass($class, $parentClass);
		}

		return new $class($values[$name . 'Options'] ?? []);
	}

	/**
	 * @param string $class
	 * @param string $parentClass
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException
	 */
	private function assertSubclass(string $class, string $parentClass): void
	{
		if (!class_exists($class)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Class %s doesn\'t exists.',
				$class
			));
		}
		if (!is_subclass_of($class, $parentClass, TRUE)) {
			$isInterface = interface_exists($parentClass);

			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Class %s must be %s %s.',
				$class,
				$isInterface ? 'implementor of interface' : 'inheritor of class',
				$parentClass
			));
		}
	}
}
