<?php

/**
 * Class for errors thrown by HTML_QuickForm package
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: @package_version@
 */
class HTML_QuickForm_Error extends PEAR_Error
{

// {{{ properties

    /**
     * Prefix for all error messages
     * @var string
     */
    public $error_message_prefix = 'QuickForm Error: ';

// }}}
// {{{ constructor

    /**
     * Creates a quickform error object, extending the PEAR_Error class
     *
     * @param int   $code      the error code
     * @param int   $mode      the reaction to the error, either return, die or trigger/callback
     * @param int   $level     intensity of the error (PHP error code)
     * @param mixed $debuginfo any information that can inform user as to nature of the error
     */
    public function __construct(
        $code = QUICKFORM_ERROR,
        $mode = PEAR_ERROR_RETURN,
        $level = E_USER_NOTICE,
        $debuginfo = null
    ) {
        if (is_int($code)) {
            parent::__construct(HTML_QuickForm::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            parent::__construct("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

// }}}
} // end class HTML_QuickForm_Error
