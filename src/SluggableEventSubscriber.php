<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

/**
 * @todo The problem with slugs based on ID field from related entity that is not saved in database (ID is NULL). The problem arises only when multiple new entities are stored at a time.
 */
final class SluggableEventSubscriber implements Doctrine\Common\EventSubscriber
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\ISluggableDefinitionStorage  */
	private $storage;

	/** @var \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition[]  */
	private $definitions = [];

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\ISluggableDefinitionStorage $storage
	 */
	public function __construct(DefinitionStorage\ISluggableDefinitionStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			Doctrine\ORM\Events::onFlush,
			Doctrine\ORM\Events::postFlush,
		];
	}

	/**
	 * @internal
	 *
	 * @param \Doctrine\ORM\Event\OnFlushEventArgs $args
	 *
	 * @return void
	 */
	public function onFlush(Doctrine\ORM\Event\OnFlushEventArgs $args): void
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() as $object) {
			if (!count($definitions = $this->storage->findSluggableDefinitions($em, get_class($object)))) {
				continue;
			}

			foreach ($definitions as $definition) {
				$this->addDefinition($definition);
				$definition->runInsert(EntityAdapter\EntityAdapterFactory::create($em, $object));
			}
		}

		foreach ($uow->getScheduledEntityUpdates() as $object) {
			if (!count($definitions = $this->storage->findSluggableDefinitions($em, get_class($object)))) {
				continue;
			}

			foreach ($definitions as $definition) {
				$this->addDefinition($definition);
				$definition->runUpdate(EntityAdapter\EntityAdapterFactory::create($em, $object));
			}
		}
	}

	/**
	 * @internal
	 *
	 * @return void
	 */
	public function postFlush(): void
	{
		foreach ($this->definitions as $definition) {
			$definition->getFinder()->invalidateCache();
		}

		$this->definitions = [];
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 *
	 * @return void
	 */
	private function addDefinition(Definition\SluggableDefinition $definition): void
	{
		if (!isset($this->definitions[$oid = spl_object_hash($definition)])) {
			$this->definitions[$oid] = $definition;
		}
	}
}
