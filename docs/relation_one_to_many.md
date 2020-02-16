# "One to many" relations

Also known as "__has many__" relation in other ORM. Examples: one product has many images, one category has many products.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html).

Most of the times (like in the examples above) you don't want to CASCADE delete so this defaults to FALSE. One use-case where you want to enable this behaviours is on "one order has many order lines" where you don't need the order lines once the
 order is deleted.