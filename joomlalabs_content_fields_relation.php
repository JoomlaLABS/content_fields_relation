<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Fields_relation
 *
 * @copyright   Copyright (C) 2015 - 2022 Joomla!LABS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Plug-in to automatically create relation fields between articles
 *
 * @since  1.0.0
 */
class PlgContentJoomlalabs_content_fields_relation extends CMSPlugin
{
	/**
	 * The original fields before save article
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $originalFields = [];

	/**
	 * Function that store custom fields of the original article
	 *
	 * @param   string                   $context  The context
	 * @param   \Joomla\CMS\Table\Table  $item     The table
	 * @param   boolean                  $isNew    Is new item
	 * @param   array                    $data     The validated data
	 *
	 * @return void
	 *
	 * @since  1.0.0
	 */
	public function onContentBeforeSave($context, $item, $isNew, $data = [])
	{
		// Check if is an article
		if ($context != 'com_content.article')
		{
			return;
		}

		// Check if data is an array and the item has an id
		if (!is_array($data) || empty($item->id) || empty($data['com_fields']))
		{
			return;
		}

		// Loading the fields
		$fields = FieldsHelper::getFields($context, $item);

		if (!$fields)
		{
			return;
		}

		$originalFields;
		foreach($fields as $field)
		{
			$originalFields[$field->id] = (!is_array($field->rawvalue) && $field->rawvalue != '' ? array($field->rawvalue) : $field->rawvalue);
		}

		//Store original fields in the class var
		$this->originalFields = $originalFields;

		return true;
	}

	/**
	 * Function that create a custom field in the related article
	 *
	 * @param   string                   $context  The context
	 * @param   \Joomla\CMS\Table\Table  $item     The table
	 * @param   boolean                  $isNew    Is new item
	 * @param   array                    $data     The validated data
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function onContentAfterSave($context, $item, $isNew, $data = []): void
	{
		// Check if is an article
		if ($context != 'com_content.article')
		{
			return;
		}
		
		// Check if data is an array and the item has an id
		if (!is_array($data) || empty($item->id) || empty($data['com_fields']))
		{
			return;
		}

		// Loading the fields
		$fields = FieldsHelper::getFields($context, $item);

		if (!$fields)
		{
			return;
		}
		
		$newFields;
		foreach($fields as $field)
		{
			$newFields[$field->id] = (!is_array($field->rawvalue) && $field->rawvalue != '' ? array($field->rawvalue) : $field->rawvalue);
		}

		// Create the relations array with all possible reverse direction
		$relationsArray = array();
		foreach ($this->params->get('fields_relation_repeatable') as $relation)
		{
			if(!array_key_exists($relation->relation_direct, $relationsArray))
			{
				$relationsArray[$relation->relation_direct] = array($relation->relation_reverse);
			} else {
				if(!in_array($relation->relation_reverse, $relationsArray[$relation->relation_direct]))
				{
					$relationsArray[$relation->relation_direct][] = $relation->relation_reverse;
				}
			}
			
			if($this->params->get('both_direction'))
			{
				if(!array_key_exists($relation->relation_reverse, $relationsArray))
				{
					$relationsArray[$relation->relation_reverse] = array($relation->relation_direct);
				} else {
					if(!in_array($relation->relation_direct, $relationsArray[$relation->relation_reverse]))
					{
						$relationsArray[$relation->relation_reverse][] = $relation->relation_direct;
					}
				}
			}
		}

		// Loading the model

		/** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
		$model = Factory::getApplication()->bootComponent('com_fields')->getMVCFactory()
			->createModel('Field', 'Administrator', ['ignore_request' => true]);

		// Create the inverse relationship only forn new relation
		foreach ($this->arrayFieldsDiff($newFields, $this->originalFields) as $key => $values)
		{
			// Check if is a managed relation
			if (!array_key_exists($key, $relationsArray))
			{
				continue;
			}

			foreach ((!is_array($values) ? array($values) : $values) as $value)
			{
				foreach ($relationsArray[$key] as $relations)
				{
					$previusRelations = $model->getFieldValue($relations, $value);
					if (!is_array($previusRelations) && $previusRelations == '')
					{
						$model->setFieldValue($relations, $value, $item->id);
					} elseif (!is_array($previusRelations) && $previusRelations != '')
					{
						$model->setFieldValue($relations, $value, array($previusRelations, $item->id));
					} elseif (!in_array($item->id, $previusRelations))
					{
						$previusRelations[] = $item->id;
						$model->setFieldValue($relations, $value, $previusRelations);
					}
				}
			}
		}

		// Delete the inverse relationship only forn old relation
		foreach ($this->arrayFieldsDiff($this->originalFields, $newFields) as $key => $values)
		{
			// Check if is a managed relation
			if (!array_key_exists($key, $relationsArray))
			{
				continue;
			}

			foreach ((!is_array($values) ? array($values) : $values) as $value)// SEMBRA ENTRI FELL'if SBAGLIATO QUI SOTTO
			{
				foreach ($relationsArray[$key] as $relations)
				{
					$previusRelations = $model->getFieldValue($relations, $value);
					if (!is_array($previusRelations) && $previusRelations == '')
					{
						continue;
					} elseif (!is_array($previusRelations) && $previusRelations != $item->id)
					{
						continue;
					} elseif (!is_array($previusRelations) && $previusRelations == $item->id)
					{
						$model->setFieldValue($relations, $value, null);
					} elseif (in_array($item->id, $previusRelations))
					{
						$model->setFieldValue($relations, $value, array_diff($previusRelations, array($item->id)));
					}
				}
			}
		}
	}

	/**
	 * Function that return the element present in the source data but not in the filter data
	 *
	 * @param   array                    $source   The source data
	 * @param   array                    $filter   The filter data to remove from source
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	private function arrayFieldsDiff($source, $filter): array
	{
		$return = array();
		foreach($source as $key => $data)
		{
			if (!is_array($data))
			{
				if ($data != '' && !in_array($data, $filter[$key]))
				{
					$return[$key] = $data;
				}
			} else {
				$diff = array_diff($data, (!is_array($filter[$key]) ? array($filter[$key]) : $filter[$key]));
				if (!empty($diff))
				{
					$return[$key] = $diff;
				}
			}
		}
		return $return;
	}
}