<?php
/**
 */

/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendPatch_Filter_DispatcherUrlToName implements Zend_Filter_Interface
{
    /**
     * Path delimiter character
     * @var string
     */
    protected $_pathDelimiter = '_';

    /**
     * Word delimiter characters
     * @var array
     */
    protected $_wordDelimiter = array('-', '.');

    /**
     * stores if this conversion is in the context of an action
     *
     * @var boolean
     */
    protected $_isAction = false;

    /**
     * Sets default option values for this instance
     *
     * @param  boolean $allowWhiteSpace
     * @return void
     */
    public function __construct($isAction = false)
    {
        if ($isAction instanceof Zend_Config) {
            $isAction = $isAction->toArray();
        } else if (is_array($isAction)) {
            if (array_key_exists('isAction', $isAction)) {
                $isAction = $isAction['isAction'];
            } else {
                $isAction = false;
            }
        }

        $this->setIsAction((boolean) $isAction);
    }

    /**
     * Retrieve the word delimiter character(s) used in
     * controller or action names
     *
     * @return array
     */
    public function getWordDelimiter()
    {
        return $this->_wordDelimiter;
    }

    /**
     * Set word delimiter
     *
     * Set the word delimiter to use in controllers and actions. May be a
     * single string or an array of strings.
     *
     * @param string|array $spec
     * @return Zend_Controller_Dispatcher_Abstract
     */
    public function setWordDelimiter($spec)
    {
        $spec = $this->_verifyDelimiter($spec);
        $this->_wordDelimiter = $spec;

        return $this;
    }

    /**
     * Retrieve the path delimiter character(s) used in
     * controller names
     *
     * @return array
     */
    public function getPathDelimiter()
    {
        return $this->_pathDelimiter;
    }

    /**
     * Set path delimiter
     *
     * Set the path delimiter to use in controllers. May be a single string or
     * an array of strings.
     *
     * @param string $spec
     * @return Zend_Controller_Dispatcher_Abstract
     */
    public function setPathDelimiter($spec)
    {
        if (!is_string($spec)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Invalid path delimiter');
        }
        $this->_pathDelimiter = $spec;

        return $this;
    }

    /**
     * Returns the allowWhiteSpace option
     *
     * @return boolean
     */
    public function getIsAction()
    {
        return $this->_isAction;
    }

    /**
     * Sets the allowWhiteSpace option
     *
     * @param boolean $allowWhiteSpace
     * @return Zend_Filter_Alnum Provides a fluent interface
     */
    public function setIsAction($value)
    {
        $this->_isAction = (boolean) $value;
        return $this;
    }

    /**
     * Formats a string from a URI into a PHP-friendly name.
     *
     * By default, replaces words separated by the word separator character(s)
     * with camelCaps. If $isAction is false, it also preserves replaces words
     * separated by the path separation character with an underscore, making
     * the following word Title cased. All non-alphanumeric characters are
     * removed.
     *
     * @param string $unformatted
     * @return string
     */
    public function filter($value)
    {
        if ($this->getIsAction()) {
            $segments = (array) $value;
        } else {
            // preserve directories
            $segments = explode($this->getPathDelimiter(), $value);
        }

        foreach ($segments as $key => $segment) {
            $segment        = str_replace($this->getWordDelimiter(), ' ', strtolower($segment));
            $segment        = preg_replace('/[^a-z0-9 ]/', '', $segment);
            $segments[$key] = str_replace(' ', '', ucwords($segment));
        }

        $result = implode('_', $segments);

        if ($this->getIsAction()) {
            $result = lcfirst($result);
        }

        return $result;
    }
}
