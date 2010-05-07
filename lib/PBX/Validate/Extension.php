<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * Original Alnum Validator by Zend Framework crew. Modified for use in Snep.
 *
 * @category   PBX
 * @package    PBX_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Alnum.php 14560 2009-03-31 14:41:22Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   PBX
 * @package    PBX_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class PBX_Validate_Extension extends Zend_Validate_Abstract {
    /**
     * Validation failure message key for when the value is not a valid
     * Extension id
     */
    const NOT_EXTEN = 'notExten';

    /**
     * Validation failure message key for when the value is an empty string
     */
    const STRING_EMPTY = 'stringEmpty';

    /**
     * Whether to allow white space characters; off by default
     *
     * @var boolean
     * @depreciated
     */
    public $allowEmpty;
    
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
            self::NOT_EXTEN    => "'%value%' não é um ramal cadastrado no Snep",
            self::STRING_EMPTY => "Valor não pode ser nullo"
    );

    /**
     * Sets default option values for this instance
     *
     * @param  boolean $allowEmpty
     * @return void
     */
    public function __construct($allowEmpty = true) {
        $this->allowEmpty = (boolean) $allowEmpty;
    }

    /**
     * Returns the allowWhiteSpace option
     *
     * @return boolean
     */
    public function getAllowEmpty() {
        return $this->allowEmpty;
    }

    /**
     * Sets the allowWhiteSpace option
     *
     * @param boolean $allowEmpty
     * @return Zend_Filter_Alnum Provides a fluent interface
     */
    public function setAllowEmpty($allowEmpty) {
        $this->allowEmpty = (boolean) $allowEmpty;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value contains only alphabetic and digit characters
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value) {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if ('' === $valueString && !$this->getAllowEmpty()) {
            $this->_error(self::STRING_EMPTY);
            return false;
        }

        try {
            PBX_Usuarios::get($value);
        }
        catch(PBX_Exception_NotFound $ex) {
            $this->_error(self::NOT_EXTEN);
            return false;
        }

        return true;
    }

}
