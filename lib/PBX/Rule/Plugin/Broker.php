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

/**
 * Gerenciador de plugins das regras de negócio.
 *
 * @category  Snep
 * @package   Snep_Rule
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rule_Plugin_Broker extends PBX_Rule_Plugin {

    /**
     * Array com os plugins a serem executados.
     *
     * @var array
     */
    protected $plugins = array();

    /**
     * Define a regra que tem controle sobre esse Broker
     *
     * @param PBX_Rule $rule
     */
    public function setRule(PBX_Rule $rule) {
        parent::setRule($rule);
        foreach ($this->plugins as $plugin) {
            $plugin->setRule($rule);
        }
    }

    /**
     * Define a interface de comunicação com o asterisk em todos os plugins.
     *
     * @param Asterisk_AGI $asterisk
     */
    public function setAsteriskInterface(Asterisk_AGI $asterisk) {
        parent::setAsteriskInterface($asterisk);
        foreach ($this->plugins as $plugin) {
            $plugin->setAsteriskInterface($asterisk);
        }
    }

    /**
     * Register a plugin.
     *
     * @param  PBX_Rule_Plugin $plugin
     * @param  int $stackIndex
     * @return PBX_Rule_Plugin_Broker
     */
    public function registerPlugin(PBX_Rule_Plugin $plugin, $stackIndex = null) {
        if (false !== array_search($plugin, $this->plugins, true)) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Plugin already registered');
        }

        $stackIndex = (int) $stackIndex;

        if ($stackIndex) {
            if (isset($this->plugins[$stackIndex])) {
                require_once 'Zend/Controller/Exception.php';
                throw new Zend_Controller_Exception('Plugin with stackIndex "' . $stackIndex . '" already registered');
            }
            $this->plugins[$stackIndex] = $plugin;
        } else {
            $stackIndex = count($this->plugins);
            while (isset($this->plugins[$stackIndex])) {
                ++$stackIndex;
            }
            $this->plugins[$stackIndex] = $plugin;
        }

        $rule = $this->getRule();
        if ($rule) {
            $this->plugins[$stackIndex]->setRule($rule);
        }

        $asterisk = $this->getAsteriskInterface();
        if ($asterisk) {
            $this->plugins[$stackIndex]->setAsteriskInterface($asterisk);
        }

        ksort($this->plugins);

        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param string|PBX_Rule_Plugin $plugin Plugin object or class name
     * @return PBX_Rule_Plugin_Broker
     */
    public function unregisterPlugin($plugin) {
        if ($plugin instanceof Zend_Controller_Plugin) {
            // Given a plugin object, find it in the array
            $key = array_search($plugin, $this->plugins, true);
            if (false === $key) {
                require_once 'Zend/Controller/Exception.php';
                throw new Zend_Controller_Exception('Plugin never registered.');
            }
            unset($this->plugins[$key]);
        } elseif (is_string($plugin)) {
            // Given a plugin class, find all plugins of that class and unset them
            foreach ($this->plugins as $key => $_plugin) {
                $type = get_class($_plugin);
                if ($plugin == $type) {
                    unset($this->plugins[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Is a plugin of a particular class registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class) {
        foreach ($this->plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class Class name of plugin(s) desired
     * @return false|PBX_Rule_Plugin|array Returns false if none found, plugin
     * if only one found, and array of plugins if multiple plugins of same class
     * found
     */
    public function getPlugin($class) {
        $found = array();
        foreach ($this->plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }

        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins() {
        return $this->plugins;
    }

    /**
     * Invoca o metodo correspondente de todos os plugins registrados.
     */
    public function startup() {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->startup();
            } catch (PBX_Exception_AuthFail $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_StopExecution $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_GoTo $ex) {
                throw $ex;
            } catch (Exception $ex) {
                Zend_Registry::get("log")->err($ex);
            }
        }
    }

    /**
     * Invoca o metodo correspondente de todos os plugins registrados.
     *
     * @param int $index Índice da ação que está sendo executada essa chamada
     */
    public function preExecute($index) {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->preExecute($index);
            } catch (PBX_Exception_AuthFail $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_StopExecution $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_GoTo $ex) {
                throw $ex;
            } catch (Exception $ex) {
                Zend_Registry::get("log")->err($ex);
            }
        }
    }

    /**
     * Invoca o metodo correspondente de todos os plugins registrados.
     *
     * @param int $index Índice da ação que está sendo executada essa chamada
     */
    public function postExecute($index) {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->postExecute($index);
            } catch (PBX_Exception_AuthFail $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_StopExecution $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_GoTo $ex) {
                throw $ex;
            } catch (Exception $ex) {
                Zend_Registry::get("log")->err($ex);
            }
        }
    }

    /**
     * Invoca o metodo correspondente de todos os plugins registrados.
     */
    public function shutdown() {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->shutdown();
            } catch (PBX_Exception_AuthFail $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_StopExecution $ex) {
                throw $ex;
            } catch (PBX_Rule_Action_Exception_GoTo $ex) {
                throw $ex;
            } catch (Exception $ex) {
                Zend_Registry::get("log")->err($ex);
            }
        }
    }

}
