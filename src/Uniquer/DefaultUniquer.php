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
class DefaultUniquer extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISlugUniquer
{
	public const DEFAULT_SEPARATOR = '-';

	/**
	 * {@inheritdoc}
	 */
	public function createUnique(string $slug, array $similarSlugs, array $options): string
	{
		$separator = $this->getSeparator($options);
		$similarSlugs = $this->filterSimilarSlugs($slug, $similarSlugs, $options);

		if (count($similarSlugs) || in_array($slug, $similarSlugs, TRUE)) {
			$i = 1;

			do {
				$generatedSlug = $slug . $separator . $i++;
			} while (!$this->isUnique($generatedSlug, $similarSlugs, $options));

			$slug = $generatedSlug;
		}

		return $slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUnique(string $slug, array $similarSlugs, array $options): bool
	{
		$similar = $this->filterSimilarSlugs($slug, $similarSlugs, $options);

		return !in_array($this->isCaseSensitive($options) ? $slug : mb_strtolower($slug, 'UTF-8'), $similar, TRUE);
	}

	/**
	 * @param string $slug
	 * @param array  $similarSlugs
	 * @param array  $options
	 *
	 * @return array
	 */
	private function filterSimilarSlugs(string $slug, array $similarSlugs, array $options): array
	{
		$separator = $this->getSeparator($options);
		$caseSensitive = $this->isCaseSensitive($options);

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
	 * @param array $options
	 *
	 * @return string
	 */
	private function getSeparator(array $options): string
	{
		return (string) self::getOption($options, 'separator', self::DEFAULT_SEPARATOR);
	}

	/**
	 * @param array $options
	 *
	 * @return bool
	 */
	private function isCaseSensitive(array $options): bool
	{
		return (bool) self::getOption($options, 'caseSensitive', FALSE);
	}
}
