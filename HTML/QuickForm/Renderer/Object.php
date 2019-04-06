<?php

/**
 * A concrete renderer for HTML_QuickForm, makes an object from form contents.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Ron McClain <ron@humaniq.com>
 * @copyright 2001-2011 The PHP Group
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @see http://pear.php.net/package/HTML_QuickForm
 */

/**
 * An abstract base class for QuickForm renderers.
 */
require_once 'HTML/QuickForm/Renderer.php';

/**
 * A concrete renderer for HTML_QuickForm, makes an object from form contents.
 *
 * Based on HTML_Quickform_Renderer_Array code
 *
 * @author Ron McClain <ron@humaniq.com>
 */
class HTML_QuickForm_Renderer_Object extends HTML_QuickForm_Renderer
{
    /**
     * The object being generated.
     *
     * @var QuickformForm
     */
    public $_obj;

    /**
     * Number of sections in the form (i.e. number of headers in it).
     *
     * @var int
     */
    public $_sectionCount;

    /**
     * Current section number.
     *
     * @var int
     */
    public $_currentSection;

    /**
     * Object representing current group.
     *
     * @var object
     */
    public $_currentGroup;

    /**
     * Class of Element Objects.
     *
     * @var object
     */
    public $_elementType = 'QuickFormElement';

    /**
     * Additional style information for different elements.
     *
     * @var array
     */
    public $_elementStyles = array();

    /**
     * true: collect all hidden elements into string; false: process them as usual form elements.
     *
     * @var bool
     */
    public $_collectHidden = false;

    /**
     * Constructor.
     *
     * @param bool $collecthidden true: collect all hidden elements
     */
    public function __construct($collecthidden = false)
    {
        parent::__construct();
        $this->_collectHidden = $collecthidden;
        $this->_obj = new QuickformForm();
    }

    /**
     * Return the rendered Object.
     */
    public function toObject()
    {
        return $this->_obj;
    }

    /**
     * Set the class of the form elements.  Defaults to QuickformElement.
     *
     * @param string $type Name of element class
     */
    public function setElementType($type)
    {
        $this->_elementType = $type;
    }

    public function startForm($form)
    {
        $this->_obj->frozen = $form->isFrozen();
        $this->_obj->javascript = $form->getValidationScript();
        $this->_obj->attributes = $form->getAttributes(true);
        $this->_obj->requirednote = $form->getRequiredNote();
        $this->_obj->errors = new StdClass();

        if ($this->_collectHidden) {
            $this->_obj->hidden = '';
        }
        $this->_elementIdx = 1;
        $this->_currentSection = null;
        $this->_sectionCount = 0;
    }

    public function renderHeader($header)
    {
        $hobj = new StdClass();
        $hobj->header = $header->toHtml();
        $this->_obj->sections[$this->_sectionCount] = $hobj;
        $this->_currentSection = $this->_sectionCount++;
    }

    public function renderElement($element, $required, $error)
    {
        $elObj = $this->_elementToObject($element, $required, $error);
        if (!empty($error)) {
            $name = $elObj->name;
            $this->_obj->errors->$name = $error;
        }
        $this->_storeObject($elObj);
    }

    public function renderHidden($element)
    {
        if ($this->_collectHidden) {
            $this->_obj->hidden .= $element->toHtml()."\n";
        } else {
            $this->renderElement($element, false, null);
        }
    }

    public function startGroup($group, $required, $error)
    {
        $this->_currentGroup = $this->_elementToObject($group, $required, $error);
        if (!empty($error)) {
            $name = $this->_currentGroup->name;
            $this->_obj->errors->$name = $error;
        }
    }

    public function finishGroup($group)
    {
        $this->_storeObject($this->_currentGroup);
        $this->_currentGroup = null;
    }

    /**
     * Creates an object representing an element.
     *
     * @param HTML_QuickForm_element $element form element being rendered
     * @param bool $required Whether an element is required
     * @param string $error Error associated with the element
     *
     * @return object
     */
    public function _elementToObject($element, $required, $error)
    {
        if ($this->_elementType) {
            $ret = new $this->_elementType();
        }
        $ret->name = $element->getName();
        $ret->value = $element->getValue();
        $ret->type = $element->getType();
        $ret->frozen = $element->isFrozen();
        $labels = $element->getLabel();
        if (is_array($labels)) {
            $ret->label = array_shift($labels);
            foreach ($labels as $key => $label) {
                $key = is_int($key) ? $key + 2 : $key;
                $ret->{'label_'.$key} = $label;
            }
        } else {
            $ret->label = $labels;
        }
        $ret->required = $required;
        $ret->error = $error;

        if (isset($this->_elementStyles[$ret->name])) {
            $ret->style = $this->_elementStyles[$ret->name];
            $ret->styleTemplate = 'styles/'.$ret->style.'.html';
        }
        if ('group' == $ret->type) {
            $ret->separator = $element->_separator;
            $ret->elements = array();
        } else {
            $ret->html = $element->toHtml();
        }

        return $ret;
    }

    /**
     * Stores an object representation of an element in the form array.
     *
     * @param QuickformElement $elObj Object representation of an element
     */
    public function _storeObject($elObj)
    {
        if (is_object($this->_currentGroup) && 'group' != $elObj->type) {
            $this->_currentGroup->elements[] = $elObj;
        } elseif (isset($this->_currentSection)) {
            $this->_obj->sections[$this->_currentSection]->elements[] = $elObj;
        } else {
            $this->_obj->elements[] = $elObj;
        }
    }

    public function setElementStyle($elementName, $styleName = null)
    {
        if (is_array($elementName)) {
            $this->_elementStyles = array_merge($this->_elementStyles, $elementName);
        } else {
            $this->_elementStyles[$elementName] = $styleName;
        }
    }
}

/**
 * Convenience class for the form object passed to outputObject().
 *
 * Eg.
 * <pre>
 * {form.outputJavaScript():h}
 * {form.outputHeader():h}
 *   <table>
 *     <tr>
 *       <td>{form.name.label:h}</td><td>{form.name.html:h}</td>
 *     </tr>
 *   </table>
 * </form>
 * </pre>
 *
 * @author Ron McClain <ron@humaniq.com>
 */
class QuickformForm
{
    /**
     * Whether the form has been frozen.
     *
     * @var bool
     */
    public $frozen;

    /**
     * Javascript for client-side validation.
     *
     * @var string
     */
    public $javascript;

    /**
     * Attributes for form tag.
     *
     * @var string
     */
    public $attributes;

    /**
     * Note about required elements.
     *
     * @var string
     */
    public $requirednote;

    /**
     * Collected html of all hidden variables.
     *
     * @var string
     */
    public $hidden;

    /**
     * Set if there were validation errors.
     * StdClass object with element names for keys and their
     * error messages as values.
     *
     * @var object
     */
    public $errors;

    /**
     * Array of QuickformElementObject elements.  If there are headers in the form
     * this will be empty and the elements will be in the
     * separate sections.
     *
     * @var array
     */
    public $elements;

    /**
     * Array of sections contained in the document.
     *
     * @var array
     */
    public $sections;

    /**
     * Output &lt;form&gt; header
     * {form.outputHeader():h}
     *
     * @return string &lt;form attributes&gt;
     */
    public function outputHeader()
    {
        return '<form '.$this->attributes.">\n";
    }

    /**
     * Output form javascript
     * {form.outputJavaScript():h}.
     *
     * @return string Javascript
     */
    public function outputJavaScript()
    {
        return $this->javascript;
    }
}

/**
 * Convenience class describing a form element.
 *
 * The properties defined here will be available from
 * your flexy templates by referencing
 * {form.zip.label:h}, {form.zip.html:h}, etc.
 *
 * @author Ron McClain <ron@humaniq.com>
 */
class QuickformElement
{
    /**
     * Element name.
     *
     * @var string
     */
    public $name;

    /**
     * Element value.
     *
     * @var mixed
     */
    public $value;

    /**
     * Type of element.
     *
     * @var string
     */
    public $type;

    /**
     * Whether the element is frozen.
     *
     * @var bool
     */
    public $frozen;

    /**
     * Label for the element.
     *
     * @var string
     */
    public $label;

    /**
     * Whether element is required.
     *
     * @var bool
     */
    public $required;

    /**
     * Error associated with the element.
     *
     * @var string
     */
    public $error;

    /**
     * Some information about element style.
     *
     * @var string
     */
    public $style;

    /**
     * HTML for the element.
     *
     * @var string
     */
    public $html;

    /**
     * If element is a group, the group separator.
     *
     * @var mixed
     */
    public $separator;

    /**
     * If element is a group, an array of subelements.
     *
     * @var array
     */
    public $elements;

    public function isType($type)
    {
        return $this->type == $type;
    }

    public function notFrozen()
    {
        return !$this->frozen;
    }

    public function isButton()
    {
        return 'submit' == $this->type || 'reset' == $this->type;
    }

    /**
     * XXX: why does it use Flexy when all other stuff here does not depend on it?
     */
    public function outputStyle()
    {
        ob_start();
        HTML_Template_Flexy::staticQuickTemplate('styles/'.$this->style.'.html', $this);
        $ret = ob_get_contents();
        ob_end_clean();

        return $ret;
    }
}
