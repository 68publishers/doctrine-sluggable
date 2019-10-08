<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Uniquer;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'separator' => '-',
 *      'caseSensitive' => FALSE,
 * )
 */
final class SequenceUniquer extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements IUniquer
{
	public const    OPTION_SEPARATOR = 'separator',
					OPTION_CASE_SENSITIVE = 'caseSensitive';

	public const    DEFAULT_SEPARATOR = '-',
					DEFAULT_CASE_SENSITIVE = FALSE;

	/** @var array  */
	protected $defaults = [
		self::OPTION_SEPARATOR => self::DEFAULT_SEPARATOR,
		self::OPTION_CASE_SENSITIVE => self::DEFAULT_CASE_SENSITIVE,
	];

	/**
	 * {@inheritdoc}
	 */
	public function makeUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): string
	{
		$separator = $this->getSeparator();
		$similarSlugs = $this->getSimilarSlugs($slug, $adapter, $finder);

		if (count($similarSlugs) || in_array($slug, $similarSlugs, TRUE)) {
			$i = 1;

			do {
				$generatedSlug = $slug . $separator . $i++;
			} while (!$this->checkUnique($generatedSlug, $similarSlugs));

			$slug = $generatedSlug;
		}

		return $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUnique(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): bool
	{
		return $this->checkUnique($slug, $this->getSimilarSlugs($slug, $adapter, $finder));
	}

	/**
	 * @param string $slug
	 * @param array  $similarSlugs
	 *
	 * @return bool
	 */
	private function checkUnique(string $slug, array $similarSlugs): bool
	{
		return !in_array($this->isCaseSensitive() ? $slug : mb_strtolower($slug, 'UTF-8'), $similarSlugs, TRUE);
	}

	/**
	 * @param string                                                                $slug
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter  $adapter
	 * @param \SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder
	 *
	 * @return array
	 */
	private function getSimilarSlugs(string $slug, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, SixtyEightPublishers\DoctrineSluggable\Finder\SimilarSlugFinderProxy $finder): array
	{
		return $this->filterSimilarSlugs(
			$slug,
			$finder->getSimilarSlugs($adapter, $finder->getFieldName(), $slug)
		);
	}

	/**
	 * @param string $slug
	 * @param array  $similarSlugs
	 *
	 * @return array
	 */
	private function filterSimilarSlugs(string $slug, array $similarSlugs): array
	{
		$separator = $this->getSeparator();
		$caseSensitive = $this->isCaseSensitive();

		$quotedSeparator = preg_quote($separator, '/');
		$quotedSlug = preg_quote($slug, '/');

		foreach ($similarSlugs as $key => $similar) {
			if (!preg_match("@{$quotedSlug}($|{$quotedSeparator}[\d]+$)@smi", $similar)) {
				unset($similarSlugs[$key]);

				continue;
			}

			if (!$caseSensitive) {
				$similarSlugs[$key] = mb_strtolower($similar, 'UTF-8');
			}
		}

		return $similarSlugs;
	}

	/**
	 * @return string
	 */
	private function getSeparator(): string
	{
		return (string) $this->getOption(self::OPTION_SEPARATOR);
	}

	/**
	 * @return bool
	 */
	private function isCaseSensitive(): bool
	{
		return (bool) $this->getOption(self::OPTION_CASE_SENSITIVE, self::DEFAULT_CASE_SENSITIVE);
	}
}
