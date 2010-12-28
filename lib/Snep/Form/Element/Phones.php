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
 * Classe Text retorna um bloco html de formulário: views/scripts/incremental.phtml
 *
 * @see Snep_Form_Element_Text
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Elton Santana <elton@opens.com.br>
 *
 */

class Snep_Form_Element_Phones extends Zend_Form_Element {

     public function init()
    {
        parent::init();
        $this->addDecorator('ViewScript', array(
            'viewScript' => 'incremental.phtml'
        ));
    }

}
