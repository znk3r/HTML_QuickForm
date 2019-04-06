<?php

/**
 * HTML class for a file upload field.
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
 * @author Alexey Borzov <avb@php.net>
 * @copyright 2001-2011 The PHP Group
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @see http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for <input /> form elements.
 */
require_once 'HTML/QuickForm/input.php';

// register file-related rules
if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerRule('uploadedfile', 'callback', '_ruleIsUploadedFile', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('maxfilesize', 'callback', '_ruleCheckMaxFileSize', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('mimetype', 'callback', '_ruleCheckMimeType', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('filename', 'callback', '_ruleCheckFileName', 'HTML_QuickForm_file');
}

/**
 * HTML class for a file upload field.
 *
 * @author Adam Daniel <adaniel1@eesus.jnj.com>
 * @author Bertrand Mansion <bmansion@mamasam.com>
 * @author Alexey Borzov <avb@php.net>
 */
class HTML_QuickForm_file extends HTML_QuickForm_input
{
    /**
     * Uploaded file data, from $_FILES.
     *
     * @var array
     */
    public $_value;

    /**
     * Class constructor.
     *
     * @param string $elementName Input field name attribute
     * @param string $elementLabel Input field label
     * @param mixed $attributes (optional)Either a typical HTML attribute string
     *                          or an associative array
     */
    public function __construct($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->setType('file');
    }

    /**
     * Sets size of file element.
     *
     * @param int $size Size of file element
     */
    public function setSize($size)
    {
        $this->updateAttributes(array('size' => $size));
    }

    /**
     * Returns size of file element.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getAttribute('size');
    }

    /**
     * Freeze the element so that only its value is returned.
     *
     * @return bool
     */
    public function freeze()
    {
        return false;
    }

    /**
     * Sets value for file element.
     *
     * Actually this does nothing. The function is defined here to override
     * HTML_Quickform_input's behaviour of setting the 'value' attribute. As
     * no sane user-agent uses <input type="file">'s value for anything
     * (because of security implications) we implement file's value as a
     * read-only property with a special meaning.
     *
     * @param mixed $value Value for file element
     */
    public function setValue($value)
    {
        return null;
    }

    /**
     * Returns information about the uploaded file.
     *
     * @return array
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element.
     *
     * @param string $event Name of event
     * @param mixed $arg event arguments
     * @param object $caller calling object
     *
     * @return bool
     */
    public function onQuickFormEvent($event, $arg, $caller)
    {
        switch ($event) {
            case 'updateValue':
                if ('get' == $caller->getAttribute('method')) {
                    return PEAR::raiseError('Cannot add a file upload field to a GET method form');
                }
                $this->_value = $this->_findValue();
                $caller->updateAttributes(array('enctype' => 'multipart/form-data'));
                $caller->setMaxFileSize();

                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);

                return $this->onQuickFormEvent('updateValue', null, $caller);

                break;
            case 'createElement':
                $className = get_class($this);
                $this->$className($arg[0], $arg[1], $arg[2]);

                break;
        }

        return true;
    }

    /**
     * Moves an uploaded file into the destination.
     *
     * @param string $dest Destination directory path
     * @param string $fileName New file name
     *
     * @return bool Whether the file was moved successfully
     */
    public function moveUploadedFile($dest, $fileName = '')
    {
        if ('' != $dest && '/' != substr($dest, -1)) {
            $dest .= '/';
        }
        $fileName = ('' != $fileName) ? $fileName : basename($this->_value['name']);

        return move_uploaded_file($this->_value['tmp_name'], $dest.$fileName);
    }

    /**
     * Checks if the element contains an uploaded file.
     *
     * @return bool true if file has been uploaded, false otherwise
     */
    public function isUploadedFile()
    {
        return $this->_ruleIsUploadedFile($this->_value);
    }

    /**
     * Checks if the given element contains an uploaded file.
     *
     * @param array $elementValue Uploaded file info (from $_FILES)
     *
     * @return bool true if file has been uploaded, false otherwise
     */
    public static function _ruleIsUploadedFile($elementValue)
    {
        if ((isset($elementValue['error']) && 0 == $elementValue['error']) ||
            (!empty($elementValue['tmp_name']) && 'none' != $elementValue['tmp_name'])) {
            return is_uploaded_file($elementValue['tmp_name']);
        }

        return false;
    }

    /**
     * Checks that the file does not exceed the max file size.
     *
     * @param array $elementValue Uploaded file info (from $_FILES)
     * @param int $maxSize Max file size
     *
     * @return bool true if filesize is lower than maxsize, false otherwise
     */
    public function _ruleCheckMaxFileSize($elementValue, $maxSize)
    {
        if (
            !empty($elementValue['error']) &&
            (UPLOAD_ERR_FORM_SIZE == $elementValue['error'] || UPLOAD_ERR_INI_SIZE == $elementValue['error'])
        ) {
            return false;
        }
        if (!static::_ruleIsUploadedFile($elementValue)) {
            return true;
        }

        return $maxSize >= @filesize($elementValue['tmp_name']);
    }

    /**
     * Checks if the given element contains an uploaded file of the right mime type.
     *
     * @param array $elementValue Uploaded file info (from $_FILES)
     * @param mixed $mimeType Mime Type (can be an array of allowed types)
     *
     * @return bool true if mimetype is correct, false otherwise
     */
    public function _ruleCheckMimeType($elementValue, $mimeType)
    {
        if (!static::_ruleIsUploadedFile($elementValue)) {
            return true;
        }
        if (is_array($mimeType)) {
            return in_array($elementValue['type'], $mimeType);
        }

        return $elementValue['type'] == $mimeType;
    }

    /**
     * Checks if the given element contains an uploaded file of the filename regex.
     *
     * @param array $elementValue Uploaded file info (from $_FILES)
     * @param string $regex Regular expression
     *
     * @return bool true if name matches regex, false otherwise
     */
    public function _ruleCheckFileName($elementValue, $regex)
    {
        if (!static::_ruleIsUploadedFile($elementValue)) {
            return true;
        }

        return (bool) preg_match($regex, $elementValue['name']);
    }

    /**
     * Tries to find the element value from the values array.
     *
     * Needs to be redefined here as $_FILES is populated differently from
     * other arrays when element name is of the form foo[bar]
     *
     * @param bool $sc1 unused, for signature compatibility
     *
     * @return mixed
     */
    public function _findValue($sc1 = null)
    {
        if (empty($_FILES)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($_FILES[$elementName])) {
            return $_FILES[$elementName];
        }
        if (false !== ($pos = strpos($elementName, '['))) {
            $base = str_replace(
                array('\\', '\''),
                array('\\\\', '\\\''),
                substr($elementName, 0, $pos)
                    );
            $idx = "['".str_replace(
                array('\\', '\'', ']', '['),
                array('\\\\', '\\\'', '', "']['"),
                substr($elementName, $pos + 1, -1)
                     )."']";
            $props = array('name', 'type', 'size', 'tmp_name', 'error');
            $code = "if (!isset(\$_FILES['{$base}']['name']{$idx})) {\n".
                     "    return null;\n".
                     "} else {\n".
                     "    \$value = array();\n";
            foreach ($props as $prop) {
                $code .= "    \$value['{$prop}'] = \$_FILES['{$base}']['{$prop}']{$idx};\n";
            }

            return eval($code."    return \$value;\n}\n");
        }

        return null;
    }

}
