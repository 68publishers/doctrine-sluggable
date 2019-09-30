<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

final class SluggableDefinition
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\Annotation\Slug  */
	private $slug;

	/** @var string  */
	private $entityName;

	/** @var string  */
	private $fieldName;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Transliterate\ISlugTransliterate|NULL  */
	private $transliterate;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Uniquer\ISlugUniquer|NULL  */
	private $uniquer;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder|NULL  */
	private $finder;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy|NULL  */
	private $strategy;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Annotation\Slug $slug
	 * @param string                                                  $entityName
	 * @param string                                                  $fieldName
	 */
	public function __construct(Annotation\Slug $slug, string $entityName, string $fieldName)
	{
		$this->slug = $slug;
		$this->entityName = $entityName;
		$this->fieldName = $fieldName;
	}

	/**
	 * @return string
	 */
	public function getEntityName(): string
	{
		return $this->entityName;
	}

	/**
	 * @return string
	 */
	public function getFieldName(): string
	{
		return $this->fieldName;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Transliterate\ISlugTransliterate
	 */
	public function getTransliterate(): Transliterate\ISlugTransliterate
	{
		if (NULL === $this->transliterate) {
			$this->transliterate = new $this->slug->transliterate;
		}

		return $this->transliterate;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Uniquer\ISlugUniquer
	 */
	public function getUniquer(): Uniquer\ISlugUniquer
	{
		if (NULL === $this->uniquer) {
			$this->uniquer = new $this->slug->uniquer;
		}

		return $this->uniquer;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder
	 */
	public function getFinder(): Finder\ISimilarSlugFinder
	{
		if (NULL === $this->finder) {
			$this->finder = new $this->slug->finder;
		}

		return $this->finder;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy
	 */
	private function getStrategy(): Strategy\ISluggableStrategy
	{
		if (NULL === $this->strategy) {
			$this->strategy = new $this->slug->strategy;
		}

		return $this->strategy;
	}

	/**
	 * @return array
	 */
	public function getFinderOptions(): array
	{
		return $this->slug->finderOptions;
	}

	/**
	 * @return array
	 */
	public function getTransliterateOptions(): array
	{
		return $this->slug->transliterateOptions;
	}

	/**
	 * @return array
	 */
	public function getUniquerOptions(): array
	{
		return $this->slug->uniquerOptions;
	}

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $object
	 *
	 * @return void
	 */
	public function runInsert(Doctrine\ORM\EntityManagerInterface $em, $object): void
	{
		$this->getStrategy()->doInsert(new SluggableDefinitionWrapper($this, $em, $object), $this->slug->strategyOptions);
	}

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $object
	 *
	 * @return void
	 */
	public function runUpdate(Doctrine\ORM\EntityManagerInterface $em, $object): void
	{
		$this->getStrategy()->doUpdate(new SluggableDefinitionWrapper($this, $em, $object), $this->slug->strategyOptions);
	}
}
