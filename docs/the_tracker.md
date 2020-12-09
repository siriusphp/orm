---
title: The tracker | Sirius ORM
---

# Architecture - The Tracker object

The Sirius ORM solves the **n+1 problem** using a `Tracker` object. 

Whenever a mapper performs a query, a `Tracker` object stores the rows, 
and the relations we might expect to be asked in the future relative to those rows/entities.

For example, if you query the 'products' mapper for the first 10 products matching some conditions a `Tracker` object is created that stores those rows.

When the entity is actually created from the row, the ORM builds the list of relations that
the entity might ask in the future (lazy or eager). 

In the case of eager-loaded relations, the corresponding query is executed and the matching entities are attached to each product.

In the case of lazy-loaded relations a `LazyValueLoader` is attached to each product as a placeholder for the related entity. 
Later, when the consumer asks for the related entity the LazyValueLoader will perform the query and return the matching entities, the entity will
add the result to it's attribute and dispose the LazyValueLoader

You can read more about lazy values [here](the_lazy_values.md)
