<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Bridge\Nette;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class SluggableExtension extends Nette\DI\CompilerExtension
{
	/** @var bool  */
	private $registerSubscriberIntoEntityManager;

	/**
	 * @param bool $registerSubscriberIntoEntityManager
	 */
	public function __construct(bool $registerSubscriberIntoEntityManager = FALSE)
	{
		$this->registerSubscriberIntoEntityManager = $registerSubscriberIntoEntityManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('storage'))
			->setType(SixtyEightPublishers\DoctrineSluggable\SluggableDefinitionStorage::class);

		$builder->addDefinition($this->prefix('event_subscriber'))
			->setType(SixtyEightPublishers\DoctrineSluggable\SluggableEventSubscriber::class);

		$builder->addDefinition($this->prefix('helper'))
			->setType(SixtyEightPublishers\DoctrineSluggable\Helper\UniqueSlugHelper::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile(): void
	{
		if (FALSE === $this->registerSubscriberIntoEntityManager) {
			return;
		}

		$builder = $this->getContainerBuilder();

		$builder->getDefinition($builder->getByType(Doctrine\ORM\EntityManagerInterface::class))
			->addSetup('?->getEventManager()->addEventSubscriber(?)', [
				'@self',
				$builder->getDefinition($this->prefix('event_subscriber')),
			]);
	}
}
