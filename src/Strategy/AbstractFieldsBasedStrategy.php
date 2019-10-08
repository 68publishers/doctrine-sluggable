<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineSluggable\Strategy;

use SixtyEightPublishers;

/**
 * $options = array(
 *      'fields' => ['array of fields'],
 *      'datetimeFormat' => 'j.n.Y',
 * )
 */
abstract class AbstractFieldsBasedStrategy extends SixtyEightPublishers\DoctrineSluggable\AbstractAdjustableObject implements ISluggableStrategy
{
	public const    OPTION_FIELDS = 'fields',
					OPTION_DATETIME_FORMAT = 'datetimeFormat';

	public const    DEFAULT_DATETIME_FORMAT = \DateTimeInterface::ATOM;

	/** @var array  */
	protected $defaults = [
		self::OPTION_DATETIME_FORMAT => self::DEFAULT_DATETIME_FORMAT,
	];

	/**
	 * {@inheritdoc}
	 */
	protected function assertOptions(array $options): void
	{
		if (!array_key_exists(self::OPTION_FIELDS, $options)) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Missing "fields" option.');
		}

		if (!is_array($options[self::OPTION_FIELDS]) || empty($options[self::OPTION_FIELDS])) {
			throw new SixtyEightPublishers\DoctrineSluggable\Exception\AssertionException('Option "fields" must be non empty array.');
		}
	}

	/**
	 * @return array
	 */
	protected function getFields(): array
	{
		return $this->getOption(self::OPTION_FIELDS, []);
	}

	/**
	 * @return string
	 */
	protected function getDatetimeFormat(): string
	{
		return (string) $this->getOption(self::OPTION_DATETIME_FORMAT);
	}

	/**
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 *
	 * @return string
	 */
	protected function setSlugFromFields(SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): string
	{
		$datetimeFormat = $this->getDatetimeFormat();

		$values = array_map(static function ($field) use ($adapter, $datetimeFormat) {
			$value = $adapter->getValue($field);

			return $value instanceof \DateTimeInterface ? $value->format($datetimeFormat) : $value;
		}, $this->getFields());

		$slug = $definition->getTransliterator()->transliterate($values);

		$this->setSlugManually(
			$definition->getUniquer()->makeUnique($slug, $adapter, $definition->getFinder()),
			$definition,
			$adapter
		);

		return $slug;
	}

	/**
	 * @param string                                                                 $slug
	 * @param \SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition
	 * @param \SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter   $adapter
	 *
	 * @return void
	 */
	protected function setSlugManually(string $slug, SixtyEightPublishers\DoctrineSluggable\Definition\SluggableDefinition $definition, SixtyEightPublishers\DoctrineSluggable\EntityAdapter\IEntityAdapter $adapter): void
	{
		$fieldName = $definition->getFieldName();
		$oldValue = $adapter->getValue($fieldName);
		$uow = $adapter->getEntityManager()->getUnitOfWork();

		$adapter->setValue($fieldName, $slug);

		# add slug between persisted
		$definition->getFinder()->addPersistedSlug($adapter, $fieldName, $slug);

		$uow->recomputeSingleEntityChangeSet($adapter->getClassMetadata(), $adapter->getEntity());
		$uow->propertyChanged($adapter->getEntity(), $fieldName, $oldValue, $slug);
	}
}
