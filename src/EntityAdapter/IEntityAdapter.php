<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\EntityAdapter;

use Doctrine;

interface IEntityAdapter
{
	/**
	 * @return object
	 */
	public function getEntity();

	/**
	 * @return \Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getClassMetadata(): Doctrine\ORM\Mapping\ClassMetadata;

	/**
	 * @return \Doctrine\ORM\EntityManagerInterface
	 */
	public function getEntityManager(): Doctrine\ORM\EntityManagerInterface;

	/**
	 * @return string
	 */
	public function getEntityName(): string;

	/**
	 * @return string
	 */
	public function getRootEntityName(): string;

	/**
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getValue(string $fieldName);

	/**
	 * @param string $fieldName
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function setValue(string $fieldName, $value): void;

	/**
	 * @param bool $single
	 *
	 * @return array|mixed
	 */
	public function getIdentifier(bool $single = TRUE);
}
