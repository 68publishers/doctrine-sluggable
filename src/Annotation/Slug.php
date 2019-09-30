<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Annotation;

use Doctrine;
use SixtyEightPublishers;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Slug extends Doctrine\Common\Annotations\Annotation
{
	/** @var string  */
	public $strategy = SixtyEightPublishers\DoctrineSluggable\Strategy\GenerateAlwaysStrategy::class;

	/** @var array<SixtyEightPublishers\DoctrineSluggable\Annotation\Option>  */
	public $strategyOptions = [];

	/** @var string  */
	public $finder = SixtyEightPublishers\DoctrineSluggable\Finder\DefaultSimilarSlugFinder::class;

	/** @var array<SixtyEightPublishers\DoctrineSluggable\Annotation\Option>  */
	public $finderOptions = [];

	/** @var string  */
	public $transliterate = SixtyEightPublishers\DoctrineSluggable\Transliterate\DefaultSlugTransliterate::class;

	/** @var array<SixtyEightPublishers\DoctrineSluggable\Annotation\Option>  */
	public $transliterateOptions = [];

	/** @var string  */
	public $uniquer = SixtyEightPublishers\DoctrineSluggable\Uniquer\DefaultUniquer::class;

	/** @var array<SixtyEightPublishers\DoctrineSluggable\Annotation\Option>  */
	public $uniquerOptions = [];

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
	 *
	 * @return void
	 */
	public function validateFor(Doctrine\ORM\Mapping\ClassMetadata $metadata): void
	{
		$this->assertSubclass($this->strategy, SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy::class);
		$this->assertSubclass($this->finder, SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder::class);
		$this->assertSubclass($this->transliterate, SixtyEightPublishers\DoctrineSluggable\Transliterate\ISlugTransliterate::class);
		$this->assertSubclass($this->uniquer, SixtyEightPublishers\DoctrineSluggable\Uniquer\ISlugUniquer::class);

		($this->strategy . '::assertOptions')($this->strategyOptions, $metadata);
		($this->finder . '::assertOptions')($this->finderOptions, $metadata);
		($this->transliterate . '::assertOptions')($this->transliterateOptions, $metadata);
		($this->uniquer . '::assertOptions')($this->finderOptions, $metadata);
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
