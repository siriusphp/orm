---
title: Cookbook - Custom entities | Sirius ORM
---

# Cookbook - Custom entities

Out of the box the Sirius ORM provides a `GenericEntity` class to get you started as soon as possible. 

It allows you to work with entities like you would a plain object:
 
 ```php
$product->title = 'New product';
echo $product->category->name; // where category could be eager or lazy loaded
```

If you prefer another style like using getters and setters or whatever here's what you have to do:

#### 1. Create the entity class

- it has to implement the `EntityInterface` interface. Sorry, another one of those nasty trade-offs.
- implement a way to set/retrieve properties on the entity instance

Here's an example for `Category` entity that uses getters and setters

```php
use Sirius\Orm\Entity\EntityInterface;

class Category implements EntityInterface
{
    protected $id;
    protected $name;
    protected $parent_id;
    protected $parent;
    protected $products;

    protected $_state;

    protected $_changes;

    public function getPk()
    {
        return $this->id;
    }

    public function setPk($val){
        $this->id = $val;
    }

    public function getPersistenceState() {
        return $this->_state;
    }

    public function setPersistenceState($state) {
        $this->_state = $state;
    }   

    public function getArrayCopy() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'parent' => $this->parent ? $this->parent->getArrayCopy() : null,
            // ... you get the idea
        ];
    }

    public function getChanges() {
        return $this->_changes;
    }

    // getters and setters
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $current = $this->name;
        $this->name = $name;
        if ($name !== $current) {
            $this->_changes['name'] = true;
        }   
    }
}
```

#### 2. Create the entity hydrator class

The hydrator for this entity should be able to transform an array (representing the row in the DB) and build a new entity

```php
use Sirius\Orm\Entity\HydratorInterface;

class CategoryHydrator implements HydratorInterface {

    public function newEntity($attributes = []){
        $category = new Category;
        $category->setPk($attributes['id']);
        $category->setName($attributes['name']);
        $category->setParentId($attributes['parent_id']);
    }
}
```

Check out `GenericEntityHydrator` class for inspiration.

#### 3. Create a mapper class

For this you need to alter the behavior of `setEntityAttribute` and `getEntityAttribute` methods

```php
use Sirius\Orm\Mapper;

class CategoryMapper extends Mapper { 
    protected $table = 'category';
    protected $primaryKey = 'id';
    // here goes the rest of the mapper's properties

    public function setEntityAttribute(EntityInterface $entity, $attribute, $value)
    {
        $setter = 'set_' . $attribute;
        return $entity->{$setter}($value);
    }

    public function getEntityAttribute(EntityInterface $entity, $attribute)
    {
        $getter = 'get_' . $attribute;
        return $entity->{$getter}();
    }

}
```

#### 4. Register the mapper in the ORM using a factory

```php
$orm->register('categories', function($orm) {
    return new CategoryMapper($orm, new CategoryFactory());
});
```

If your mappers are complex and you are using a DiC you can do this:

```php
$orm->register('categories', function($orm) use ($di) {
    return new CategoryMapper($orm, new CategoryFactory(), $di->get('someService'));
});
```

**Warning!** The Sirius ORM internals makes some assumptions about the results of various values returned by entity methods:

1. One-to-many and many-to-many relations are attached to entities as Collections (which extend Doctrine's ArrayCollection class)
2. `getChanges()` is used to determine the changes to be persisted. Since there is no Entity Manager to track them, the entity is in responsible for tracking the changes.

