<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable;

use Doctrine;

/**
 * @todo The problem with slugs based on ID field from related entity that is not saved in database (ID is NULL). The problem arises only when multiple new entities are stored at a time.
 */
final class SluggableEventSubscriber implements Doctrine\Common\EventSubscriber
{
	/** @var \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage  */
	private $storage;

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage $storage
	 */
	public function __construct(SluggableDefinitionStorage $storage)
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
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 * @throws \ReflectionException
	 */
	public function onFlush(Doctrine\ORM\Event\OnFlushEventArgs $args): void
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() as $object) {
			if (!count($definitions = $this->storage->getSluggableDefinitions($em, $object))) {
				continue;
			}

			foreach ($definitions as $definition) {
				$definition->runInsert($em, $object);
			}
		}

		foreach ($uow->getScheduledEntityUpdates() as $object) {
			if (!count($definitions = $this->storage->getSluggableDefinitions($em, $object))) {
				continue;
			}

			foreach ($definitions as $definition) {
				$definition->runUpdate($em, $object);
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
		PersistedSlugStorage::flush();
	}
}
