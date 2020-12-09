---
title: One to one relations | Sirius ORM
---

# "One to one" relations

Also known as "__has one__" relation in other ORMs. 

It is similar to one-to-many relation with the exception that the result of the relation is either one other entity or null.

In the case of a user entity having one profile entity the relation between the user and the profile is of type one-to-one but, very important, the relation between the profile and the user is of type many-to-one. 

Here's a minimal example

```php
use Sirius\Orm\Blueprint\Relation\OneToOne;

$userDefintion->addRelation(
    'profile', // name of the relation
    OneToOne::make('user_profile') // name of the foreign mapper
);
``` 

### Methods available on the OneToOne blueprint class

The same methods available for [one-to-many relations](relation_one_to_many.md) are also available here.
