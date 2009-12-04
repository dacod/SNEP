<?php
    /**
     * @file
     * Biblioteca para facilitar manipulação da comunicação
     * com o asterisk.
     */
/**
 * Classe para trabalhar com AGI no Asterisk com PHP 5.x
 *
 * @since 09/03/2008
 * @author Carlos Alberto Cesario <carloscesario@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @filesource
 */

/**
 * Classe para trabalhar com AGI no Asterisk com PHP 5.x
 *
 * @since 09/03/2008
 * @author Carlos Alberto Cesario <carloscesario@gmail.com>
 * @access public
 * @version 0.2.2
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */

class AGI
{
    /**
     * Stream de entrada de dados
     *
     * @var stream
     * @access private
     */
    private $STDIN = null;
    private $in = null;

    /**
     * Stream de saida de dados
     *
     * @var stream
     * @access private
     */
    private $STDOUT = null;
    private $out = null;

    /**
     * Stream de saida dos erros
     *
     * @var stream
     * @access private
     */
    private $STDERR = null;

    /**
     * Arquivo para gravacao de logs
     *
     * @var string
     * @access private
     */
    private $STDLOG = null;

    /**
     * Variavel para habilitar ou nao o log dos comandos
     *
     * @var boolean
     * @access private
     */
    private $SAVE_LOG = null;

    /**
     * Variavel para habilitar ou nao o debug dos comandos
     *
     * @var boolean
     * @access private
     */
    private $DEBUG = null;

    /**
     * Variavel para armazenar todas as variaveis passadas pelo asterisk
     *
     * @var array []
     * @access private
     */
    private $AGI_ENV = null;

    /**
     * AGI::setStdin()
     *
     * Define o stream para a variavel $STDIN
     *
     * @param stream $STDIN_VAR Stream stdin
     * @return void
     * @access public
     */
    public function setStdin($STDIN_VAR)
    {
        $this->STDIN = $STDIN_VAR;
        $this->in = $STDIN_VAR;
    }

    /**
     * AGI::getStdin()
     *
     * Recebe o valor da variavel $STDIN
     *
     * @return stream Stream stdin
     * @access public
     */
    public function getStdin()
    {
        return $this->STDIN;
    }

    /**
     * AGI::setStdout()
     *
     * Define o stream para a variavel $STDOUT
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *         $AGI->setStdout(fopen('php://stdout', 'r'));
     *         $AGI->init();
     * ?>
     * </code>
     *
     * @param stream $STDOUT_VAR Stream stdout
     * @access public
     * @return void
     */
    public function setStdout($STDOUT_VAR)
    {
        $this->STDOUT = $STDOUT_VAR;
        $this->out = $STDOUT_VAR;
    }

    /**
     * AGI::getStdout()
     *
     * Recebe o valor da variavel $STDOUT
     *
     * @return stream Stream stdout
     * @access public
     */
    public function getStdout()
    {
        return $this->STDOUT;
    }

    /**
     * AGI::setStderr()
     *
     * Define o stream para a variavel $STDERR
     *
     * @return stream Stream stderr
     * @access public
     */
    public function setStderr($STDERR_VAR)
    {
        $this->STDERR = $STDERR_VAR;
    }

    /**
     * AGI::getStderr()
     *
     * Recebe o valor da variavel $STDERR
     *
     * @return stream Stream stderr
     * @access public
     */
    public function getStderr()
    {
        return $this->STDERR;
    }

    /**
     * AGI::setStdlog()
     *
     * Define o arquivo onde serao
     * gravados os logs dos comandos
     *
     * @param string $STDLOG_VAR Caminho do arquivo de log
     * @return void
     * @access public
     */
    public function setStdlog($STDLOG_VAR)
    {
        $this->STDLOG = $STDLOG_VAR;
    }

    /**
     * AGI::getStdlog()
     *
     * Recebe o valor da variavel $STDLOG
     *
     * @return string Caminho do arquivo de log
     * @access public
     */
    public function getStdlog()
    {
        return $this->STDLOG;
    }

    /**
     * AGI::setSavelog()
     *
     * Habilita ou nao o LOG dos comandos
     *
     * @param boolean $STDLOG_VAR
     * @return void
     * @access public
     */
    public function setSavelog($STDLOG_VAR)
    {
        $this->SAVE_LOG = $STDLOG_VAR;
    }

    /**
     * AGI::getSavelog()
     *
     * Recebe o valor da variavel $SAVE_LOG
     *
     * @return boolean True ou False
     * @access public
     */
    public function getSavelog()
    {
        return $this->SAVE_LOG;
    }

    /**
     * AGI::setDebug()
     *
     * Habilita ou nao o DEBUG dos comandos
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *         $AGI->setDebug(true);
     *         $AGI->init();
     * ?>
     * </code>
     *
     * @param boolean $DEBUG_VAR True ou False
     * @return void
     * @access public
     */
    public function setDebug($DEBUG_VAR)
    {
        $this->DEBUG = $DEBUG_VAR;
    }

    /**
     * AGI::getDebug()
     *
     * Recebe o valor da variavel $DEBUG
     *
     * @return boolean True ou False
     * @access public
     */
    public function getDebug()
    {
        return $this->DEBUG;
    }

    /**
     * AGI::setAgi_Env()
     *
     * Define na variavel AGI_ENV todas
     * as variaveis que o asterisk passa
     *
     * @param string $AGIENV_VAR Nome da variavel do AGI
     * @param string $AGIENV_VALUE Valor do AGI
     * @return void
     * @access public
     */
    public function setAgi_Env($AGIENV_VAR, $AGIENV_VALUE)
    {
        $this->AGI_ENV[$AGIENV_VAR] = $AGIENV_VALUE;
    }

    /**
     * AGI::getAgi_Env()
     *
     * Recebe o valor da variavel AGI_ENV
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *         $AGI->init();
     *         $AGIENV = $AGI->getAgi_Env();
     *         $EXTEN = $AGIENV['extension'];
     *         echo $EXTEN;
     * ?>
     * </code>
     *
     * @return array
     * @access public
     */
    public function getAgi_Env()
    {
        return $this->AGI_ENV;
    }

    /**
     * AGI::__construct()
     *
     * Metodo construtor da classe AGI
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        $this->set_env();
    }

    /**
     * AGI::set_env()
     *
     * Define opcoes do PHP, para execucao do php em modo cli
     *
     * @return void
     * @access private
     */
    private function set_env()
    {
        /**
         * nao deixar esse script rodar por mais do que 60 segundos
         */
        set_time_limit(60);

        /**
         * desabilita a saida do buffer
         */
        ob_implicit_flush(false);

        /**
         * desabilita as mensagens de erro
         */
        error_reporting(0);
    }

    /**
     * AGI::define_handlers()
     *
     * Define valores padroes para os streams de controle,
     * arquivo de log, debug.
     *
     * @return void
     * @access private
     */
    private function define_handlers ()
    {
        if (!$this->getDebug())
        {
            $this->setDebug(false);
        }

        if (!$this->getSavelog())
        {
            $this->setSavelog(false);
        }

        if (!$this->getStdin())
        {
            $this->setStdin(fopen('php://stdin', 'r'));
        }

        if (!$this->getStdout())
        {
            $this->setStdout(fopen('php://stdout', 'r'));
        }

        if (!$this->getStderr())
        {
            $this->setStderr(fopen ('php://stderr', 'r'));
        }

        if (!$this->getStdlog())
        {
            $this->setStdlog(fopen('/var/log/asterisk/my_agi.log', 'a'));
        }
    }

    /**
     * AGI::write_console()
     *
     * Funcao para escrever mensagens na cli
     * usado para debug
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *          $AGI->init();
     *          $AGI->write_console("Discando para SIP/1234");
     * ?>
     * </code>
     *
     * @param string $STR_MESSAGE String a ser impressa na tela
     * @param integer $VBL Valor para o verbose
     * @return void
     * @access public
     */
    public function write_console($STR_MESSAGE, $VBL = 1)
    {
        $STR_MESSAGE = str_replace("\\", "\\\\", $STR_MESSAGE);
        $STR_MESSAGE = str_replace("\"", "\\\"", $STR_MESSAGE);
        $STR_MESSAGE = str_replace("\n", "\\n", $STR_MESSAGE);
        fwrite($this->getStdout(), "VERBOSE \"$STR_MESSAGE\" $VBL\n");
        fflush($this->getStdout());
        fgets($this->getStdin(), 1024);
    }

    /**
     * AGI::exec_command()
     *
     * Funcao para executar comandos AGI
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *         $AGI->init();
     *         $AGI->exec_command("EXEC DIAL","SIP/12345");
     * ?>
     * </code>
     *
     * @param string $STR_CMD Comando a ser executado
     * @param string $STR_PARAM Parametros do comando
     * @return void
     * @access public
     */
    public function exec_command($STR_CMD, $STR_PARAM)
    {
        $COMMAND = null;
        if ($this->getDebug());
        {
            $this->write_console("--> cmd $STR_CMD $STR_PARAM");
        }
        if ($this->getSavelog())
        {
            fwrite($this->getStdlog(), date("M  d") . " " . date("h:i:s") . " -- agi -- COMANDO --> $STR_CMD $STR_PARAM \n");
        }
        $COMMAND = "$STR_CMD $STR_PARAM \n";
        fwrite($this->getStdout(), "$COMMAND");
        fflush($this->getStdout());
    }

    /**
     * AGI::get_result()
     *
     * Funcao para tratar o resultado da funcao exec_command()
     *
     * <code>
     * <?php
     *         $AGI = new AGI();
     *          $AGI->init();
     *          $AGI->exec_command("GET VARIABLE", "UNIQUEID");
     *          $UNIQUEID = $AGI->get_result();
     *          $UNIQUEID = "code - " . $UNIQUEID['code'] .
     *                  " ::  result - " . $UNIQUEID['result'] .
     *                 "  ::  data - " . $UNIQUEID['data'];
     *         echo $UNIQUEID;
     * ?>
     * </code>
     *
     * @return mixed array 'code' 'result' 'data' or int 0 -1
     * @access public
     */
    public function get_result()
    {
        $ARR = null;
        $DATA = null;
        $MATCHES = null;
        $MATCH = null;

        $DATA = fgets($this->getStdin(), 4096);
        if ($this->getSavelog())
        {
            fwrite($this->getStdlog(), $DATA . "\n");
            fwrite($this->getStdlog(), "-------------------------\n");
        }

        /**
         * Procura uma sequencia iniciada por 3 numeros de 0 a 9,
         * seguidos ou nao de algum outro texto
         */
        if (preg_match("/^([0-9]{1,3}) (.*)/", $DATA, $MATCHES))
        {
            if (preg_match('/^result=([0-9a-zA-Z]*)( ?\((.*)\))?$/', $MATCHES[2], $MATCH))
            {
                $ARR['code'] = $MATCHES[1];
                $ARR['result'] = $MATCH[1];
                if (isset($MATCH[3]) && $MATCH[3])
                {
                    $ARR['data'] = $MATCH[3];
                }
                if ($this->getDebug())
                {
                    $this->write_console("==================================================");
                    $this->write_console("RET CODE: " . $ARR['code']);
                    $this->write_console("RET Value: " . $ARR['result']);
                    if ($ARR['data'])
                    {
                        $this->write_console("RET DATA: " . $ARR['data']);
                    }
                    $this->write_console("==================================================");
                }
                return $ARR;
            }
            else return 0;
        }
        else return -1;
    }

    /**
     * AGI::init()
     *
     * Inicializa a captura das variveis passadas pelo asterisk
     *
     * @return void
     * @access public
     */
    public function init()
    {
        $this->define_handlers();

        /**
         * captura todas as variaveis AGI vindas do asterisk
         * e salva na variavel $AGI do tipo array
         */

        $TEMP = null;
        $SPLIT = null;
        $NAME = null;

        while (!feof($this->getStdin()))
        {
            $TEMP = trim(fgets($this->getStdin(), 4096));
            if (($TEMP == "") || ($TEMP == "\n"))
            {
                break;
            }
            $SPLIT = split(":", $TEMP);
            $NAME = str_replace("agi_", "", $SPLIT[0]);
            $this->setAgi_Env($NAME, trim($SPLIT[1]));
        }

        /**
         * Escreve na tela todas as variaveis do AGI
         * para proposito de DEBUG
         */
        if ($this->getDebug())
        {
            $this->write_console("           Asterisk A G I Variables               ");
            $this->write_console("==================================================");

            foreach($this->getAgi_Env() as $KEY => $VALUE)
            {
                $this->write_console("-- agi_$KEY = $VALUE");
            }

            $this->write_console("==================================================");
        }
    }
    
    
    
    // *********************************************************************************************************
    // **                       TEST                                                                          **
    // *********************************************************************************************************
    
   /**
    * Fetch the value of a variable.
    *
    * Does not work with global variables. Does not work with some variables that are generated by modules.
    *
    * @link http://www.voip-info.org/wiki-get+variable
    * @link http://www.voip-info.org/wiki-Asterisk+variables
    * @param string $variable name
    * @param boolean $get_value
    * @return array if $get_value is not set or set to false. 
		*	If $get_value is set to true, the value of the variable is returned. 
		* See evaluate for return information. ['result'] is 0 if variable hasn't been set, 1 if it has. ['data'] holds the value.
    */
    function get_variable($variable, $get_value = false)
    {
	$var = $this->evaluate("GET VARIABLE $variable");
        if(isset($get_value) && $get_value){
          return $var['data'];
        } else {
          return $var;
        }
    }
    
    
   /**
    * Evaluate an AGI command.
    *
    * @access private
    * @param string $command
    * @return array ('code'=>$code, 'result'=>$result, 'data'=>$data)
    */
    function evaluate($command)
    {
      $broken = array('code'=>500, 'result'=>-1, 'data'=>'');

      // write command
      if(!@fwrite($this->out, trim($command) . "\n")) return $broken;
      fflush($this->out);

      // Read result.  Occasionally, a command return a string followed by an extra new line.
      // When this happens, our script will ignore the new line, but it will still be in the
      // buffer.  So, if we get a blank line, it is probably the result of a previous
      // command.  We read until we get a valid result or asterisk hangs up.  One offending
      // command is SEND TEXT.
      $count = 0;
      do
      {
        $str = trim(fgets($this->in, 4096));
      } while($str == '' && $count++ < $this->nlinetoread);

      if($count >= 5)
      {
          $this->write_console("evaluate error on read for $command");
          return $broken;
      }

      // parse result
      $ret['code'] = substr($str, 0, 3);
      $str = trim(substr($str, 3));

      if($str{0} == '-') // we have a multiline response!
      {
        $count = 0;
        $str = substr($str, 1) . "\n";
        $line = fgets($this->in, 4096);
        while(substr($line, 0, 3) != $ret['code'] && $count < 5)
        {
          $str .= $line;
          $line = fgets($this->in, 4096);
          $count = (trim($line) == '') ? $count + 1 : 0;
        }
        if($count >= 5)
        {
//        $this->conlog("evaluate error on multiline read for $command");
          return $broken;
        }
      }

      $ret['result'] = NULL;
      $ret['data'] = '';
      if($ret['code'] != 200) // some sort of error
      {
        $ret['data'] = $str;
        $this->write_console(print_r($ret, true));
      }
      else // normal AGIRES_OK response
      {
        $parse = explode(' ', trim($str));
        $in_token = false;
        foreach($parse as $token)
        {
          if($in_token) // we previously hit a token starting with ')' but not ending in ')'
          {
            $ret['data'] .= ' ' . trim($token, '() ');
            if($token{strlen($token)-1} == ')') $in_token = false;
          }
          elseif($token{0} == '(')
          {
            if($token{strlen($token)-1} != ')') $in_token = true;
            $ret['data'] .= ' ' . trim($token, '() ');
          }
          elseif(strpos($token, '='))
          {
            $token = explode('=', $token);
            $ret[$token[0]] = $token[1];
          }
          elseif($token != '')
            $ret['data'] .= ' ' . $token;
        }
        $ret['data'] = trim($ret['data']);
      }

      // log some errors
      if($ret['result'] < 0)
        $this->write_console("$command returned {$ret['result']}");
      
      return $ret;
    }
    
}

?>

