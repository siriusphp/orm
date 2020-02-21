---
title: Relation aggregates | Sirius ORM
---

# Relation aggregates

This feature is WIP.

Sometimes you want to query a relation and extract some aggregates. You may want to count the number of comments on a blog post, the average rating on a product etc. It would be faster if these aggregates are already available somewhere else (a
 stats table or in special columns) but sometimes your app doesn't need this type of optimizations.
 