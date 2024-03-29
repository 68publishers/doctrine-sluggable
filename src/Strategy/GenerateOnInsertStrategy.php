<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 *      'datetimeFormat' => 'j.n.Y',
 *      'checkOnUpdate' => FALSE,
 *      'rewriteOnInsert' => TRUE,
 * )
 */
final class GenerateOnInsertStrategy extends AbstractFieldsBasedStrategy
{
	public const OPTION_CHECK_ON_UPDATE = 'checkOnUpdate';
	public const OPTION_REWRITE_ON_INSERT = 'rewriteOnInsert';

	/**
	 * {@inheritdoc}
	 */
	public function __construct(array $options)
	{
		$this->defaults[self::OPTION_CHECK_ON_UPDATE] = FALSE;
		$this->defaults[self::OPTION_REWRITE_ON_INSERT] = TRUE;

		parent::__construct($options);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function assertOptions(array $options): void
	{
		parent::assertOptions($options);

		if (!is_bool($options[self::OPTION_CHECK_ON_UPDATE])) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException(sprintf(
				'Option "%s" must be boolean.',
				self::OPTION_CHECK_ON_UPDATE
			));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function doInsert(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		if (FALSE === $this->getOption(self::OPTION_REWRITE_ON_INSERT) && !empty($adapter->getValue($definition->getFieldName()))) {
			$this->checkUnique($definition, $adapter, TRUE);

			return;
		}

		$this->setSlugFromFields($definition, $adapter);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\DoctrineSluggable\Exception\UniqueSlugException
	 */
	public function doUpdate(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		if (FALSE === $this->getOption(self::OPTION_CHECK_ON_UPDATE)) {
			return;
		}

		$fieldName = $definition->getFieldName();
		$uow = $adapter->getEntityManager()->getUnitOfWork();
		$changes = $uow->getEntityChangeSet($adapter->getEntity());

		if (!isset($changes[$fieldName]) || $changes[$fieldName][0] === $changes[$fieldName][1]) {
			return;
		}

		$this->checkUnique($definition, $adapter, FALSE);
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 * @param bool                                                                   $persist
	 *
	 * @return void
	 */
	private function checkUnique(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter, bool $persist): void
	{
		$fieldName = $definition->getFieldName();
		$slug = $adapter->getValue($fieldName);

		if (!$definition->getUniquer()->isUnique($slug, $adapter, $definition->getFinder())) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\UniqueSlugException($slug, $adapter->getEntity(), $fieldName);
		}

		# add slug between persisted
		if ($persist) {
			$definition->getFinder()->addPersistedSlug($adapter, $fieldName, $slug);
		}
	}
}
