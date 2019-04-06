<?php

/**
 * Checks that the length of value is within range.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Bertrand Mansion <bmansion@mamasam.com>
 * @copyright 2001-2011 The PHP Group
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @see http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Abstract base class for QuickForm validation rules.
 */
require_once 'HTML/QuickForm/Rule.php';

/**
 * Checks that the length of value is within range.
 *
 * @author Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Rule_Range extends HTML_QuickForm_Rule
{
    /**
     * Validates a value using a range comparison.
     *
     * @param string $value Value to be checked
     * @param mixed $options Int for length, array for range
     *
     * @return bool true if value is valid
     */
    public function validate($value, $options = null)
    {
        $length = strlen($value);
        switch ($this->name) {
            case 'minlength': return $length >= $options;
            case 'maxlength': return $length <= $options;
            default:          return $length >= $options[0] && $length <= $options[1];
        }
    }

    public function getValidationScript($options = null)
    {
        switch ($this->name) {
            case 'minlength':
                $test = '{jsVar}.length < '.$options;

                break;
            case 'maxlength':
                $test = '{jsVar}.length > '.$options;

                break;
            default:
                $test = '({jsVar}.length < '.$options[0].' || {jsVar}.length > '.$options[1].')';
        }

        return array('', "{jsVar} != '' && {$test}");
    }

}
