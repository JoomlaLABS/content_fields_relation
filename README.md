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
And we suggest to select the "Enhanced select" Form Options Layout
![image](https://user-images.githubusercontent.com/906604/163670345-775deaa8-4f30-4c9c-a394-d799eb5c46be.png)

The important thing is to point the "content.id" as "value" in the Query field.
The query can contain filters, sorts, and any join conditions.

E.g.
```sql
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

Extra trick
---------------------

It will then be possible to manage these custom fields with the appropriate overrides to create real liniks between the articles.

E.g. create this override file ```templates/[template name]/html/layouts/com_fields/field/content-fields.php``` with this code:
```php
<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!array_key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = Text::_($field->label);
$texts = array();
$article = JTable::getInstance("content");
foreach ((!is_array($field->rawvalue) ? array($field->rawvalue) : $field->rawvalue) as $item) {
	$article->load($item);
	$texts[] = "<a href=\"".Route::_('index.php?view=article&id='.$article->id.'&catid='.$article->catid)."\">".htmlentities($article->title)."</a>";
}
$value = implode(', ', $texts);
$showLabel = $field->params->get('showlabel');
$prefix = Text::plural($field->params->get('prefix'), $value);
$suffix = Text::plural($field->params->get('suffix'), $value);
$labelClass = $field->params->get('label_render_class');
$valueClass = $field->params->get('value_render_class');

if ($value == '')
{
	return;
}

?>
<?php if ($showLabel == 1) : ?>
	<span class="field-label <?php echo $labelClass; ?>"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
<?php endif; ?>
<?php if ($prefix) : ?>
	<span class="field-prefix"><?php echo htmlentities($prefix, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?></span>
<?php endif; ?>
<span class="field-value <?php echo $valueClass; ?>"><?php echo $value; ?></span>
<?php if ($suffix) : ?>
	<span class="field-suffix"><?php echo htmlentities($suffix, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?></span>
<?php endif; ?>
```
And specify it in the Display Option Layout
![image](https://user-images.githubusercontent.com/906604/163688713-b32f1579-9713-4c94-98a1-acfa9323bb17.png)

This way each article listed in the custom field will be linked to its page:
![image](https://user-images.githubusercontent.com/906604/163688950-f02123cc-fcf7-47c7-b9bf-486f77d3e99d.png)
