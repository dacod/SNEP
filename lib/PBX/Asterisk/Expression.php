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
 * Classe para trabalhar com expressões regulares no estilo Asterisk.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Expression {
    protected $expression;

    function __construct($expression = null) {
        $this->setExpression($expression);
    }

    public function getExpression() {
        return $this->expression;
    }

    public function setExpression($expression) {
        $this->expression = $expression;
    }

    /**
     * Verifica se um valor casa com a expressão regular.
     *
     * @param string $value Valor para comparação
     * @return boolean match
     */
    public function match($value) {
        if(preg_match($this->getPCRE(),$value)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Converte a expressão regular do formato Asterisk para PCRE
     *
     * Com essa função a expressão pode ser testada no php usando preg_match.
     *
     * @return string Perl Compactible Regular Expression
     */
    public function getPCRE() {
        $astrule = $this->getExpression();

        $astrule = str_replace("*", "\*", $astrule);
        $astrule = str_replace("|", "", $astrule);
        if(preg_match_all("#\[[^]]*\]#",$astrule, $brackets)){
            $brackets = $brackets[0];
            foreach($brackets as $key => $value){
                $new_bracket = "[";
                for($i = 1; $i < strlen($value)-1; $i++){
                    $char = (substr($value,$i,1) !== false) ? substr($value,$i,1) : -1;
                    $charnext = (substr($value,$i+1,1) !== false) ? substr($value,$i+1,1) : -1;
                    if($char != "-" && $charnext != "-" && $i < (strlen($value)-2)){
                        $new_bracket = $new_bracket . $char . "|";
                    } else {
                        $new_bracket = $new_bracket . $char;
                    }
                }
                $lists[$key] = $new_bracket . "]";
            }
            $astrule = str_replace($brackets, $lists, $astrule);
        }
        $sub  = array("_","X","Z","N", ".", "!");
        $exp  = array("","[0-9]","[1-9]","[2-9]", "[[0-9]|.*]", ".*");
        $rule = str_replace($sub, $exp, $astrule);
        return "/^" . $rule . "$/i";
    }
}
