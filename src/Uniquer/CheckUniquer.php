<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Uniquer;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'caseSensitive' => FALSE,
 * )
 */
final class CheckUniquer extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements IUniquer
{
	public const OPTION_CASE_SENSITIVE = 'caseSensitive';

	public const DEFAULT_CASE_SENSITIVE = FALSE;

	/** @var array  */
	protected $defaults = [
		self::OPTION_CASE_SENSITIVE => self::DEFAULT_CASE_SENSITIVE,
	];

	/**
	 * {@inheritdoc}
	 */
	public function makeUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): string
	{
		if (!$this->isUnique($slug, $adapter, $finder)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\UniqueSlugException($slug, $adapter->getEntity(), $finder->getFieldName());
		}

		return $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): bool
	{
		$caseSensitive = $this->isCaseSensitive();
		$similarSlugs = $finder->getSimilarSlugs($adapter, $finder->getFieldName(), $slug);

		if (FALSE === $caseSensitive) {
			$similarSlugs = array_map(static function (string $slug) {
				return mb_strtolower($slug, 'UTF-8');
			}, $similarSlugs);
		}

		return !in_array($caseSensitive ? $slug : mb_strtolower($slug, 'UTF-8'), $similarSlugs, TRUE);
	}

	/**
	 * @return bool
	 */
	private function isCaseSensitive(): bool
	{
		return (bool) $this->getOption(self::OPTION_CASE_SENSITIVE, self::DEFAULT_CASE_SENSITIVE);
	}
}
