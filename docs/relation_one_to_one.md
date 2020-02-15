# "One to one" relations

Also known as "__has one__" relation in other ORM. On the surface, this relation seems similar to the "many to one". For example you could think that one page has one parent but you would be wrong to classify it as a "one to one" relation.

The difference is given by the order of operations when saving the table rows. In the "one page has one parent page" scenario you would save first the parent parent page (so you get the ID) and then the child page. This is why it is actually a
 "many pages have one parent page".
 
In a "one to one" relation you first save the "main" entity and then the related entity. One example would be to have a general "content" table and other special tables for each type of content (eg: "content_products", "content_pages"). In this
 scenario you first save row in the "content" table. Sure, you could have many rows in the "content_products" table for each row in the "content" table (which would make it a "many content_products have one content") but the ORM will only return the
  first.
 
In this scenario you would probably want to do CASCADE delete but for this relation the default is still FALSE because usually this happens directly in the database.

There are no special options for this type of relation, besides those explained in the [relations page](relations.html).