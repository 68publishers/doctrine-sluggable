<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Exception;

final class UniqueSlugException extends \RuntimeException implements SluggableException
{
	/** @var string  */
	private $slug;

	/** @var object  */
	private $entity;

	/** @var string  */
	private $fieldName;

	/**
	 * @param string          $slug
	 * @param object          $entity
	 * @param string          $fieldName
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(string $slug, $entity, string $fieldName, int $code = 0, ?\Throwable $previous = NULL)
	{
		parent::__construct(sprintf(
			'Slug "%s" is not unique for column %s::$%s',
			$slug,
			get_class($entity),
			$fieldName
		), $code, $previous);

		$this->slug = $slug;
		$this->entity = $entity;
		$this->fieldName = $fieldName;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string
	{
		return $this->slug;
	}

	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return string
	 */
	public function getFieldName(): string
	{
		return $this->fieldName;
	}
}
