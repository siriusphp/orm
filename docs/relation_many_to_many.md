# "Many to many" relations

Also known as "__belongs to many__" relation in other ORM. Examples: many products belong to many tags. This involves a simple pivot table.

Other ORMs allow you to use another mapper object instead of the pivot table but my experience is that, if the relation is a little more complex, you will end up working with the relations directly. 

For example, many products belong to many orders  via the  "order items" but, since the "order items" table is heavily complex you will never want to attach/detach products to orders directly, which is something that you would do in the case of a
 "many products belong to many tags" kind of situation.
 
This ORM is build with a focus on DX and having less options to shoot yourself in the foot is a reasonable trade-off. 
 
Besides the options explained in the [relations page](relations.html) below you will find those specific to this "many to many" relations:

