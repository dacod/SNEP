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
require_once("Zend/Registry.php");
require_once("Zend/Locale.php");
require_once("Zend/Locale/Format.php");
require_once("Zend/Translate.php");
require_once("Zend/Translate/Adapter/Array.php");
require_once("Zend/Validate/Abstract.php");

define("TRANSLATIONS_PATH", APPLICATION_PATH . DIRECTORY_SEPARATOR . "lang");

/**
 * Singleton class to control the localization and internationalization features
 * of snep.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Locale {
    /**
     * Singleton instance.
     *
     * @return Snep_Locale
     */
    protected static $instance;

    /**
     * The current system locale
     * 
     * @var string locale
     */
    protected $locale;

    /**
     * The current system language
     *
     * @var string language
     */
    protected $language;

    /**
     * Current system Timezone
     *
     * @var string timezone
     */
    protected $timezone;

    /**
     * The Zend Translate object for string translations.
     *
     * @var Zend_Translate
     */
    protected $zendTranslate;

    /**
     * Zend Locale object for locale management.
     *
     * @var Zend_Locale
     */
    protected $zendLocale;

    /**
     * @var array Available languages
     */
    protected $availableLanguages = array();

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
        $locale = $this->locale = $config->system->locale;
        $language = $this->language = $config->system->language;
        $timezone = $this->timezone = $config->system->timezone;

        if(!Zend_Locale::isLocale($locale)) {
            throw new Exception("Fatal: '$locale' is not a valid locale", 500);
        }

        setlocale(LC_COLLATE, $locale . ".utf8");
        Zend_Locale::setDefault($locale);
        $this->zendLocale = $zendLocale = new Zend_Locale($locale);
        Zend_Registry::set('Zend_Locale', $zendLocale);
        Zend_Locale_Format::setOptions(array("locale"=> $locale));

        if(!Zend_Locale::isLocale($language)) {
            throw new Exception("Fatal: '$language' is not a valid language locale", 500);
        }

        if( !self::isTimezone($timezone) ) {
            throw new Exception("Fatal: '$timezone' is not a valid timezone", 500);
        }
        date_default_timezone_set($timezone);

        $language_alt = substr($language, 0, strpos($language, "_"));
        if(file_exists(TRANSLATIONS_PATH . DIRECTORY_SEPARATOR . "$language.mo")) {
            $translate = new Zend_Translate('gettext', TRANSLATIONS_PATH . DIRECTORY_SEPARATOR . "$language.mo", $language);
        }
        else if(file_exists(TRANSLATIONS_PATH . DIRECTORY_SEPARATOR . "$language_alt.mo")) {
            $translate = new Zend_Translate('gettext', TRANSLATIONS_PATH . DIRECTORY_SEPARATOR . "$language_alt.mo", $language);
        }
        else {
            $translate = new Zend_Translate('gettext', null, $language);
        }

        $this->zendTranslate = $translate;
        Zend_Registry::set("Zend_Translate", $translate);

        $lang_dirs = scandir(TRANSLATIONS_PATH . "/Zend_Validate/");
        if(in_array($language, $lang_dirs)) {
            $validate_locale = $language;
        }
        else if(in_array($language_alt, $lang_dirs)) {
            $validate_locale = $language_alt;
        }
        else {
            $validate_locale = "us";
        }

        $zend_validate_translator = new Zend_Translate_Adapter_Array(
            TRANSLATIONS_PATH . "/Zend_Validate/$validate_locale/Zend_Validate.php",
            $language
        );
        Zend_Validate_Abstract::setDefaultTranslator($zend_validate_translator);
    }

    /**
     * Assert if a timezone identifier is valid or not.
     *
     * @param string $timezone Timezone identifier
     * @return boolean is timezone
     */
    public static function isTimezone($timezone) {
        return key_exists($timezone, Zend_Locale::getTranslationList("territorytotimezone"));
    }

    /**
     * @return string System locale identifier
     */
    public function getLocale() {
        return self::$instance->locale;
    }

    /**
     * @return string System language identifier
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @return string System timezone identifier
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * @return Zend_Translate Default system translator
     */
    public function getZendTranslate() {
        return $this->zendTranslate;
    }

    /**
     * @return Zend_Locale Default system locale
     */
    public function getZendLocale() {
        return $this->zendLocale;
    }

    /**
     * Return all the languages available on the system.
     *
     * @return array available languages
     */
    public function getAvailableLanguages() {
        if (count($this->availableLanguages) === 0) {
            foreach( scandir(TRANSLATIONS_PATH) as $filename ) {
                if( preg_match("/.*\.mo$/", $filename) ) {
                    $this->availableLanguages[] = basename($filename, '.mo');
                }
            }
        }
        return $this->availableLanguages;
    }

    protected function __clone() {}
}
