Content Fields Relation
====================

Joomla!LABS Content Fields Relation Plugin
---------------------

Automatically populate a custom fields in another article.
The logic used is that in the current article the custom field indicates the target article of a relationship and the plugin automatically creates the inverse relationship to the current article in the target article

How does it work?
---------------------

The idea is to use Custom Fields to create relationships between articles.
So the first step is to create a custom ```sql``` filed type that gives the possibility to select one or more articles:
![image](https://user-images.githubusercontent.com/906604/163669923-cb397de1-f8ba-4534-a09c-35146f94303e.png)
And we suggest to activate the "Enhanced select" layout
![image](https://user-images.githubusercontent.com/906604/163670345-775deaa8-4f30-4c9c-a394-d799eb5c46be.png)

The important thing is to point the "content.id" as "value" in the Query field.
The query can contain filters, sorts, and any join conditions.

E.g.
```
SELECT #__content.id AS value, #__content.title AS text
FROM #__content, #__categories
WHERE #__content.catid = #__categories.id
   AND #__categories.alias = 'people'
ORDER BY hits
```
For each relationship, an opposite relationship must also be created (which can also be the same).

E.g.
Relation | Inverse Relation
--- | ---
Is parent of | Is child of
Is sibling of | Is sibling of
Is descendent of | Is ancestor of

![image](https://user-images.githubusercontent.com/906604/163670464-caaa7d2f-25e0-4151-aa51-68ee2c81da7f.png)

Now through the Custom Fields we can specify relationships between specific items.

For example the kinship ties of the Simpson family:
![image](https://user-images.githubusercontent.com/906604/163671277-5cd74712-9c5e-4f62-b66c-d66b092e6220.png)

But we also want the Inverse Relationships to be created automatically:
![image](https://user-images.githubusercontent.com/906604/163671323-c5a4f500-9efa-4cdb-ac89-51fef4453baf.png)

**This plugin allows us to do exactly that!**

Just specify the inverse relations inside it:
![image](https://user-images.githubusercontent.com/906604/163671460-c0756046-2246-472c-af9c-1c1c48bf1277.png)

Enabling "Both Directions" it will also be possible to automatically create the Direct relationship when the Inverse is created.

Or you can manually specify for which relationships you want to create an inverse relationship between Direct and Inverse if only one direction implies the creation of the inverse.

E.g. 
![image](https://user-images.githubusercontent.com/906604/163671665-52235372-dbc4-4b39-9bf8-0223fa2773d0.png)

With the final result:
![image](https://user-images.githubusercontent.com/906604/163671823-999db0bf-1a65-43f9-9b56-5fd7f5ee2172.png)

In this way it is possible to create different relationships between articles.
It will then be possible to manage these custom fields with the appropriate overrides to create real liniks between the articles.

