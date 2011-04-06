<?php

/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Route Rules Simulator.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Lucas Ivan Seidenfus
 */
class SimulatorController extends Zend_Controller_Action {

    public function indexAction() {
        
        $this->view->breadcrumb = $this->view->translate("Regras de Negócio » Ligações de Entrada e/ou Saída » Simulador");

        $trunks = array();
        foreach (PBX_Trunks::getAll() as $value) {
            $trunks[$value->getId()] = $value->getId() . " - " . $value->getName();
        }

        $this->view->trunks = $trunks;

        if ($this->_request->getPost()) {

            $formData = $this->_request->getParams();

            $extension = isset($formData['dst']) && $formData['dst'] != "" ? $formData['dst'] : 's';
            $srcType = isset($formData['srcType']) ? $formData['srcType'] : NULL;
            $trunk = isset($formData['trunk']) ? $formData['trunk'] : NULL;
            $caller = isset($formData['caller']) && $formData['caller'] != "" ? $formData['caller'] : "unknown";
            $time = isset($formData['time']) ? $formData['time'] : NULL;

            $dialplan = new PBX_Dialplan_Verbose();

            if ($srcType == "exten") {
                try {
                    $srcObj = PBX_Usuarios::get($caller);
                } catch (PBX_Exception_NotFound $ex) {
                    $this->view->error = $this->view->translate("Erro Interface PBX: " . $ex->getMessage());
                    $this->view->back = $this->view->translate("Voltar");
                    $this->renderScript('simulator/error.phtml');
                    return;
                }
                $channel = $srcObj->getInterface()->getCanal();
            } else if ($srcType == "trunk") {
                $srcObj = PBX_Trunks::get($trunk);
                $channel = $srcObj->getInterface()->getCanal();
            } else {
                $srcObj = null;
                $channel = "unknown";
            }

            $request = new PBX_Asterisk_AGI_Request(
                            array(
                                "agi_callerid" => $caller,
                                "agi_extension" => $extension,
                                "agi_channel" => $channel
                            )
            );

            $request->setSrcObj($srcObj);

            $dialplan->setRequest($request);

            if ($time) {
                if (preg_match("/^[0-9]:([0-9]{2})$/", $time)) {
                    $time = "0" . $time;
                }
                $dialplan->setTime($time);
            }

            try {
                $dialplan->parse();
            } catch (PBX_Exception_NotFound $ex) {
                $this->view->error = $this->view->translate("Nenhuma regra encontrada!");
                $this->view->back = $this->view->translate("Voltar");
                $this->renderScript('simulator/error.phtml');
            }


            if (count($dialplan->getMatches()) > 0) {
                $found = false;
                foreach ($dialplan->getMatches() as $index => $rule) {
                    if ($rule->getId() == $dialplan->getLastRule()->getId()) {
                        $state = "torun";
                        $found = true;
                    } else if ($found) {
                        $state = "ignored";
                    } else {
                        $state = "outdated";
                    }

                    $actions = array();

                    foreach ($rule->getAcoes() as $action) {
                        $config = $action->getConfigArray();
                        if ($action instanceof PBX_Rule_Action_CCustos) {
                            $actions[] = $this->view->translate("Definir Centro de Custos para ") . $config['ccustos'];
                        } else if ($action instanceof PBX_Rule_Action_DiscarTronco) {
                            $tronco = PBX_Trunks::get($config['tronco']);
                            $actions[] = $this->view->translate("Discar para Tronco ") . $tronco->getName();
                        } else if ($action instanceof PBX_Rule_Action_DiscarRamal) {
                            if (isset($config['ramal']) && $config['ramal'] != "") {
                                $peer = $config['ramal'];
                            } else {
                                $peer = $extension;
                            }

                            try {
                                $ramal = PBX_Usuarios::get($peer);
                                $actions[] = $this->view->translate("Discar para Ramal ") . $ramal->getCallerid();
                            } catch (PBX_Exception_NotFound $ex) {
                                $actions[] = "<strong style='color:red'>" . $this->view->translate("Tentativa com falha para ramal") . $extension . $this->view->translate(' (ramal inexistente)') . "</strong>";
                            }
                        } else if ($action instanceof PBX_Rule_Action_Queue) {
                            $actions[] = $this->view->translate("Direcionar para fila ") . $config['queue'];
                        } else if ($action instanceof PBX_Rule_Action_Cadeado) {
                            $actions[] = $this->view->translate("Requisitar senha");
                        } else if ($action instanceof PBX_Rule_Action_Context) {
                            $actions[] = $this->view->translate("Direcionar para contexto ") . $config['context'];
                        }
                    }

                    $srcs = array();
                    foreach ($rule->getSrcList() as $src) {
                        $srcs[] = trim(implode(":", $src), ':');
                    }
                    $srcs = implode(",", $srcs);
                    $dsts = array();
                    foreach ($rule->getDstList() as $dst) {
                        $dsts[] = trim(implode(":", $dst), ':');
                    }
                    $dsts = implode(",", $dsts);

                    $result[$index] = array(
                        "id" => $rule->getId(),
                        "state" => $state,
                        "caller" => $srcs,
                        "dst" => $dsts,
                        "desc" => $rule->getDesc(),
                        "valid" => join(";", $rule->getValidTimeList()),
                        "actions" => $actions
                    );
                }

                $input = array("caller" => $caller, "dst" => $extension, "time" => $dialplan->getLastExecutionTime());

                $this->view->input = $input;
                $this->view->result = $result;
            }
        }
    }

}
