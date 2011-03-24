<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

require_once("Snep/Config.php");
require_once("Zend/Translate.php");
require_once("Zend/Translate/Adapter/Array.php");
require_once("Zend/Validate/Abstract.php");
require_once("Zend/Locale.php");
require_once("Zend/Locale/Format.php");
require_once("Zend/Registry.php");

/**
 * AGI singleton class.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Locale extends Zend_Translate {
    /**
     * Singleton instance.
     *
     * @return Snep_Locale
     */
    protected static $instance;

    /**
     * Returns the singleton instance of this class.
     *
     * @return Snep_Locale
     */
    public static function getInstance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $config = Snep_Config::getConfig();
        // silenciando strict até arrumar zend_locale
        date_default_timezone_set("America/Sao_Paulo");
        parent::__construct('gettext', $config->system->path->base . '/lang/pt_BR.mo', 'pt_BR');

        Zend_Registry::set('i18n', $this);

        $translation_files = $config->system->path->base . "/lang/";
        foreach( scandir($translation_files) as $filename ) {
            // Todos os arquivos .php devem ser classes de descrição de modulos
            if( preg_match("/.*\.mo$/", $filename) ) {
                $translation_id = basename($filename, '.mo');
                if($translation_id != "pt_BR") {
                    $this->addTranslation($translation_files . "/$filename", $translation_id);
                }
            }
        }

        require_once "Zend/Locale.php";

        if(Zend_Locale::isLocale($config->system->locale)) {
            $locale = $config->system->locale;
        } else {
            $locale = "pt_BR";
        }

        Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));
        Zend_Locale::setDefault($locale);
        Zend_Locale_Format::setOptions(array("locale"=> $locale));
        $this->setLocale($locale);
        Zend_Registry::set("Zend_Translate", $this);

        $zend_validate_translator = new Zend_Translate_Adapter_Array(
            $config->system->path->base . "/lang/Zend_Validate/$locale/Zend_Validate.php",
            $locale
        );
        Zend_Validate_Abstract::setDefaultTranslator($zend_validate_translator);
    }

    protected function __clone() {}
}
