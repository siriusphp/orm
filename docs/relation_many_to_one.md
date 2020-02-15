# "Many to one" relations

Also known as "__belongs to__" relation in other ORM. Examples: multiple images belong to one product, multiple products belong to a category, multiple pages belong to one parent.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html).

Most of the times (like in the examples above) you don't want to CASCADE delete so this defaults to FALSE. I can't think of a scenario where you would want that behaviour but the behaviour is implemented and tested.