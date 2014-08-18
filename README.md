# Kairos custom Doctrine2 Behaviors

This php 5.4+ library is a collection of traits 
that add behaviors to Doctrine2 entites and repositories.

It currently handles:

 * [usageOrderable](#usageOrderable)

## Notice:

Some behaviors (translatable, timestampable, softDeletable, blameable, geocodable) need Doctrine listeners in order to work.
Make sure to activate them by reading the [Listeners](#listeners) section.  

Some traits are based on annotation driver.  
You need to declare `use Doctrine\ORM\Mapping as ORM;` on top of your entity.


<a name="listeners" id="listeners"></a>
## Listeners

If you use symfony2, you can easilly register them by importing a service definition file:

``` yaml

    # app/config/config.yml
    imports:
        - { resource: ../../vendor/kairos/custom-doctrine-behaviors/config/orm-services.yml }

```

You can also register them using doctrine2 api:


``` php

<?php

$em->getEventManager()->addEventSubscriber(new \Kairos\DoctrineBehaviors\ORM\Translatable\TranslatableListener);
// register more if needed

```


## Usage

All you have to do is to define a Doctrine2 entity and use traits:

``` php

<?php

use Doctrine\ORM\Mapping as ORM;
use Kairos\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity(repositoryClass="CategoryRepository")
 */
class Category implements ORMBehaviors\Tree\NodeInterface, \ArrayAccess
{
    use ORMBehaviors\UsageOrderable\UsageOrderable
    ;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;
}

```


<a name="usageOrderable" id="usageOrderable"></a>
### usageOrderable:

usageOrderable behavior is meant to log useractivity in database. This can be usefull to sort entities given that activity.
This naming convention avoids you to handle manually entity associations. It is handled automatically by the UsageOrderableListener.

In order to use UsageOrderable trait, you will have to create this entity.


``` php

<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class CategoryUsageTimestamp
{
    use ORMBehaviors\UsageOrderable\UsageTimestamp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $activity;

    /**
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param  string
     * @return null
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }
}

```

Now you can work on your timstamps using `getUsageTimestamp` methods or alse sort your entities given this activity.

### Roadmap:

* add tests for usageOrderable
* add defaultable extension