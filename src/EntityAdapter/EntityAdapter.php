<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\EntityAdapter;

use Doctrine;

final class EntityAdapter implements IEntityAdapter
{
	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/** @var object */
	private $entity;

	/** @var \Doctrine\ORM\Mapping\ClassMetadata  */
	private $classMetadata;

	/** @var bool  */
	private $proxyLoaded = FALSE;

	/** @var array|mixed|NULL */
	private $identifier;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 * @param object                               $entity
	 */
	public function __construct(Doctrine\ORM\EntityManagerInterface $em, $entity)
	{
		$this->em = $em;
		$this->entity = $entity;
		$this->classMetadata = $em->getClassMetadata(get_class($entity));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassMetadata(): Doctrine\ORM\Mapping\ClassMetadata
	{
		return $this->classMetadata;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityManager(): Doctrine\ORM\EntityManagerInterface
	{
		return $this->em;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return $this->classMetadata->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRootEntityName(): string
	{
		return $this->classMetadata->rootEntityName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue(string $fieldName)
	{
		$this->loadProxy();

		return $this->getClassMetadata()->getReflectionProperty($fieldName)->getValue($this->entity);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(string $fieldName, $value): void
	{
		$this->loadProxy();
		$this->getClassMetadata()->getReflectionProperty($fieldName)->setValue($this->entity, $value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(bool $single = TRUE)
	{
		if (NULL === $this->identifier) {
			$this->resolveIdentifier();
		}

		if (TRUE === $single && is_array($this->identifier)) {
			return reset($this->identifier);
		}

		return $this->identifier;
	}

	/**
	 * @return void
	 */
	private function loadProxy(): void
	{
		if (TRUE === $this->proxyLoaded) {
			return;
		}

		if ($this->entity instanceof Doctrine\ORM\Proxy\Proxy && FALSE === $this->entity->__isInitialized()) {
			$this->entity->__load();
		}
	}

	/**
	 * @return void
	 */
	private function resolveIdentifier(): void
	{
		# set Identifier from identity map or Load entity's data
		if ($this->entity instanceof Doctrine\ORM\Proxy\Proxy) {
			$uow = $this->em->getUnitOfWork();

			if ($uow->isInIdentityMap($this->entity)) {
				$this->identifier = $uow->getEntityIdentifier($this->entity);

				return;
			}

			$this->loadProxy();
		}

		if (NULL !== $this->identifier) {
			return;
		}

		$identifier = [];

		foreach ($this->getClassMetadata()->identifier as $name) {
			$identifier[$name] = $this->getValue($name);

			if (NULL === $identifier[$name]) {
				return;
			}
		}

		$this->identifier = $identifier;
	}
}
