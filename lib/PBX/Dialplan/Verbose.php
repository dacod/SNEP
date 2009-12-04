<?php
/**
 * Dialplan verbose.
 *
 * Mesma coisa que PBX_Dialplan mas fornece várias informações sobre o processo
 * de parsing. Faz a análise de todas as regras e não somente a primeira regra
 * válida encontrada
 *
 * obs. Pode-se usar essa classe ao invés de PBX_Dialplan, elas são inteiramente
 * compativeis em resultados. Mas isso só é recomendado quando se precisa dos
 * dados adicionais. Apesar de muito pouco essa classe pode representar uma queda
 * no desempenho geral da aplicação.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Dialplan_Verbose extends PBX_Dialplan {

    /**
     * Tempo em que a regra será executada.
     *
     * É usado para definir manualmente o horário de execução do Parsing.
     * Útil para debug.
     *
     * @var string Time
     */
    protected $execution_time;

    /**
     * Array com todas as regras que casam com a requisição.
     *
     * Nota: São armazenadas nessa lista também as regras que casam mas não tem
     * o horário condizente com a execução da regra. Essa informação é mantida
     * para fim de debug.
     *
     * Obs. Não é necessário que seja armazenado a razão para descarte de uma
     * regra já que a lista segue um estrutura simples e clara.
     * Supondo que as seguintes regras foram encontradas.
     *
     *  Regra 1
     * *Regra 2
     *  Regra 3
     *
     * E que a regra 2 seja a regra que será executada, a ordem do array deixa
     * claro que a única forma de a Regra 1 ter sido descartada é por conta de
     * seu horário de válidade não correspondeR com o da execução da regra.
     * Já a Regra 3 foi descartada por uma regra prévia ja ter sido encontrada
     * (descartada por prioridade).
     *
     * @var array Regra
     */
    protected $matches;

    /**
     * Retorna o horário de execução do ultimo parse
     *
     * @return string time
     */
    public function getLastExecutionTime() {
        return $this->execution_time;
    }

    /**
     * Retorna a lista de regras que foram encontradas para a requisição do
     * ultimo parse.
     *
     * @return array matches
     */
    public function getMatches() {
        return $this->matches;
    }
    
    /**
     * Sobreescreve PBX_Dialplan::parse() fazendo uma análise mais detalhada de
     * cada regra de negócio.
     */
    public function parse() {
        
        if(!isset($this->execution_time))
            $this->execution_time = date("H:i");

        $this->foundRule = null;
        $this->matches = array();

        $log = Zend_Registry::get('log');

        $rules = PBX_Rules::getAll();
        if(count($rules) > 0) {
            foreach ($rules as $rule) {
                $rule->setRequest($this->request);
                
                if( $rule->isValidDst($this->request->destino) && $rule->isValidSrc($this->request->origem)) {

                    // Armazenando a regra válida (parcialmente)
                    $this->matches[] = $rule;

                    // Caso seja a primeira regra válida (e com tempo válido), ela é a que queremos executar
                    if(is_null($this->foundRule) && $rule->isValidTime($this->execution_time))
                        $this->foundRule = $rule;
                    
                }
            }
            
            if(!is_object($this->foundRule)) { // Caso nenhuma regra tenha sido encontrada
                throw new PBX_Exception_NotFound("No rule found for this request");
            }
        }
        else {
            throw new PBX_Exception_NotFound("No rules in database");
        }
    }

    /**
     * Define manualmente o tempo que será usado em consideração na avaliação
     * da regra. (Execution time)
     *
     * @param string $time Tempo de execução da regra
     */
    public function setTime($time) {
        $this->execution_time = $time;
    }
}