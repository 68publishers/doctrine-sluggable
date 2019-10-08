# Doctrine Sluggable

:abc: Doctrine Sluggable extension! 

Simple usage and integration into [Nette Framework](https://github.com/nette).

## Installation

The best way to install `68publishers/doctrine-sluggable` is using Composer:

```bash
$ composer require 68publishers/doctrine-sluggable
```

then register `SluggableEventSubscriber` into `EventManager` like this:

```php
<?php

/** @var Doctrine\ORM\EntityManager $em */
/** @var Doctrine\Common\Annotations\Reader $reader */

$subscriber = new SixtyEightPublishers\DoctrineSluggable\SluggableEventSubscriber(
	new SixtyEightPublishers\DoctrineSluggable\DefinitionStorage\AnnotationSluggableDefinitionStorage($reader)
);

$em->getEventManager()->addEventSubscriber($subscriber);
```

but you'd better use compiler extension if you are using `Nette` Framework:

```yaml
extensions:
    sluggable: SixtyEightPublishers\DoctrineSluggable\Bridge\Nette\SluggableExtension
```

## Usage

Example entity:

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use SixtyEightPublishers\DoctrineSluggable\Annotation as Sluggable;

/**
 * @ORM\Entity
 */
class Product {
	/**
	 * @ORM\Column(type="string", length=255)
	 *
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\ManyToOne(targetEntity="Category")
	 *
	 * @var Category
	 */
	private $category;

	/**
	 * @Sluggable\Slug(
	 *      strategy="SixtyEightPublishers\DoctrineSluggable\Strategy\GenerateOnInsertStrategy",
	 *      strategyOptions={
	 *            "fields": {"name"}, 
	 *            "checkOnUpdate": true
	 *      },
	 *      finder="SixtyEightPublishers\DoctrineSluggable\Finder\FieldBasedSimilarSlugFinder",
	 *      finderOptions={
	 *            "field": "category", 
	 *            "type": "outer"
	 *      },
	 *      transliterator="SixtyEightPublishers\DoctrineSluggable\Transliterator\CamelCaseTransliterator"
	 * )
	 * @ORM\Column(type="string", length=255)
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * @return string
	 */
	public function getSlug() : string
	{
		# slug is generated on EntityManager's onFlush event
		if (NULL === $this->slug) {
			throw new RuntimeException('Slug is not set.');
		}

		return $this->slug;
	}
}
```

## Options

@todo

## Contributing

Before committing any changes, don't forget to run

```bash
$ vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
$ vendor/bin/tester ./tests
```
