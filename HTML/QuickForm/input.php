<?php

/**
 * Base class for <input /> form elements.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Adam Daniel <adaniel1@eesus.jnj.com>
 * @author Bertrand Mansion <bmansion@mamasam.com>
 * @copyright 2001-2011 The PHP Group
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @see http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for form elements.
 */
require_once 'HTML/QuickForm/element.php';

/**
 * Base class for <input /> form elements.
 *
 * @author Adam Daniel <adaniel1@eesus.jnj.com>
 * @author Bertrand Mansion <bmansion@mamasam.com>
 *
 * @abstract
 */
class HTML_QuickForm_input extends HTML_QuickForm_element
{
    /**
     * Class constructor.
     *
     * @param string $elementName Input field name attribute
     * @param mixed $elementLabel Label(s) for the input field
     * @param mixed $attributes Either a typical HTML attribute string or an associative array
     */
    public function __construct($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Sets the element type.
     *
     * @param string $type Element type
     */
    public function setType($type)
    {
        $this->_type = $type;
        $this->updateAttributes(array('type' => $type));
    }

    /**
     * Sets the input field name.
     *
     * @param string $name Input field name attribute
     */
    public function setName($name)
    {
        $this->updateAttributes(array('name' => $name));
    }

    /**
     * Returns the element name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Sets the value of the form element.
     *
     * @param string $value Default value of the form element
     */
    public function setValue($value)
    {
        $this->updateAttributes(array('value' => $value));
    }

    /**
     * Returns the value of the form element.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * Returns the input field in HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        return $this->_getTabs().'<input'.$this->_getAttrString($this->_attributes).' />';
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element.
     *
     * @param string $event Name of event
     * @param mixed $arg event arguments
     * @param object $caller calling object
     */
    public function onQuickFormEvent($event, $arg, $caller)
    {
        // do not use submit values for button-type elements
        $type = $this->getType();
        if (('updateValue' != $event) ||
            ('submit' != $type && 'reset' != $type && 'image' != $type && 'button' != $type)) {
            parent::onQuickFormEvent($event, $arg, $caller);
        } else {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_defaultValues);
            }
            if (null !== $value) {
                $this->setValue($value);
            }
        }

        return true;
    }

    /**
     * We don't need values from button-type elements (except submit) and files.
     *
     * @param mixed $submitValues
     * @param mixed $assoc
     */
    public function exportValue($submitValues, $assoc = false)
    {
        $type = $this->getType();
        if ('reset' == $type || 'image' == $type || 'button' == $type || 'file' == $type) {
            return null;
        }

        return parent::exportValue($submitValues, $assoc);
    }

}
