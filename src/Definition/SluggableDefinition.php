<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Definition;

use SixtyEightPublishers;

final class SluggableDefinition
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy  */
	private $strategy;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy  */
	private $finder;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator  */
	private $transliterator;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer  */
	private $uniquer;

	/** @var string  */
	private $entityName;

	/** @var string  */
	private $fieldName;

	/** @var array  */
	private $locks = [
		'insert' => FALSE,
		'update' => FALSE,
	];

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy    $strategy
	 * @param \SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder      $finder
	 * @param \SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator $transliterator
	 * @param \SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer               $uniquer
	 * @param string                                                                 $entityName
	 * @param string                                                                 $fieldName
	 */
	public function __construct(
		SixtyEightPublishers\DoctrineSluggable\Strategy\ISluggableStrategy $strategy,
		SixtyEightPublishers\DoctrineSluggable\Finder\ISimilarSlugFinder $finder,
		SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator $transliterator,
		SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer $uniquer,
		string $entityName,
		string $fieldName
	) {
		$this->strategy = $strategy;
		$this->finder = new SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy($finder, $fieldName);
		$this->transliterator = $transliterator;
		$this->uniquer = $uniquer;
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
	 * @return \SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy
	 */
	public function getFinder(): SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy
	{
		return $this->finder;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator
	 */
	public function getTransliterator(): SixtyEightPublishers\DoctrineSluggable\Transliterator\ITransliterator
	{
		return $this->transliterator;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer
	 */
	public function getUniquer(): SixtyEightPublishers\DoctrineSluggable\Uniquer\IUniquer
	{
		return $this->uniquer;
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 *
	 * @return void
	 */
	public function runInsert(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		if (TRUE === $this->locks['insert']) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException('The Insert action is in progress and can\'t be called twice during processing time.');
		}

		$this->locks['insert'] = TRUE;

		$this->strategy->doInsert($this, $adapter);

		$this->locks['update'] = FALSE;
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter
	 *
	 * @return void
	 */
	public function runUpdate(SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		if (TRUE === $this->locks['update']) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\InvalidStateException('The Update action is in progress and can\'t be called twice during processing time.');
		}

		$this->locks['update'] = TRUE;

		$this->strategy->doUpdate($this, $adapter);

		$this->locks['update'] = FALSE;
	}
}
