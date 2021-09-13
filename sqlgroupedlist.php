<?php
defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('GroupedList');

class JFormFieldSQLGroupedList extends JFormFieldGroupedList
{

    protected $type = 'SQLGroupedList';

    protected function getSQL($element)
    {
        $items = array();
        if ($element['query']) {
            $db = JFactory::getDbo();

            $db->setQuery($element['query']);

            try {
                $items = $db->loadObjectlist();
            } catch (JDatabaseExceptionExecuting $e) {
                JFactory::getApplication()
                    ->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            }
        }
        return $items;
    }

    function genElement($element = null, $label = 0)
    {
        $groups = array();
        if (!empty($element)) {
            foreach ($element->children() as $option) {
                $groups[$label] = $groups[$label] ?? array();
                if ($option->getName() == 'option') {
                    $disabled = (string) $option['disabled'];
                    $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
                    $tmp = JHtml::_(
                        'select.option',
                        ($option['value']) ? (string) $option['value'] : trim((string) $option),
                        JText::_(trim((string) $option)),
                        'value',
                        'text',
                        $disabled
                    );
                    $tmp->class = (string) $option['class'];
                    $tmp->onclick = (string) $option['onclick'];
                    $groups[$label][] = $tmp;
                }
            }
            $key  =  (string)(($element['field_key']) ? $element['field_key'] : 'id');
            $value  =  (string)(($element['field_value']) ? $element['field_value'] : 'value');
            $group  =  (string)($element['field_group'] ? $element['field_group'] : '');
            $topLevel  =  (bool)($element['type'] ? true : false);

            $sql_items = $this->getSQL(array('query' => (string) ($element['query'])));
            foreach ($sql_items as $item) {
                if ($topLevel && (!empty($group))) {
                    if ($groupLabel = (string) $item->$group) {
                        $label = JText::_($groupLabel);
                    }
                }

                $groups[$label] = $groups[$label] ?? array();
                $groups[$label][] = JHtml::_(
                    'select.option',
                    $item->$key,
                    $item->$value
                );
            }
        }
        return $groups;
    }

    protected function getGroups()
    {
        $groups = array();

        if (!empty($this->element['query'])) {
            $groups = array_merge($groups, $this->genElement($this->element));
        }

        foreach ($this->element->children() as $element) {
            if ($element->getName() == 'group') {
                $groups = array_merge($groups, $this->genElement($element, (string)$element['label']));
            }
        }

        reset($groups);
        return $groups;
    }
}
