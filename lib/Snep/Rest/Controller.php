<?php

/**
 * Controlador de WebServices RESTful HTTP/JSON SNEP.
 */
class Snep_Rest_Controller extends Zend_Controller_Action {

    /**
     * Inicia o controlador e desabilita qualquer layout ou view.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
        $this->_helper->layout()->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        header("Connection: close");
    }

    /**
     * Faz o controle de requisições GET, POST, PUT e DELETE e faz o dispatch
     * para o metodo correto.
     */
    public function indexAction() {
        $method = strtolower($_SERVER["REQUEST_METHOD"]);
        switch ($method) {
            case "get":
                $method = count($_GET) > 0 ? "get" : "index";
                $data = (object) $_GET;
                break;
            case "post":
            case "put":
                $data = json_decode(file_get_contents("php://input"));
                if($data === null) {
                    header("Content-Type: text/plain");
                    header("HTTP/1.1 400 Bad Request");
                    echo "Bad Request: Invalid " . strtoupper($method) . " body";
                    exit(1);
                }
                break;
            case "delete":
                $data = json_decode( file_get_contents("php://input") );
                if($data === null) {
                    header("Content-Type: text/plain");
                    header("HTTP/1.1 400 Bad Request");
                    echo "Bad Request: Invalid " . strtoupper($method) . " body";
                    exit(1);
                }
                break;
            default:
                header("Content-Type: text/plain");
                header("HTTP/1.1 405 Method Not Allowed");
                echo "Method $method Not Allowed";
                exit(1);
        }

        try {
            header("Content-Type: application/json");
            if($method == "post" || $method == "put" || $method == "delete" || $method == "get") {
                $response = $this->{$method}($data);
            }
            else {
                $response = $this->{$method}();
            }
            
            echo json_encode($response);

        } catch (Snep_Rest_Exception_HTTP $ex) {
            header("Content-Type: text/plain");
            header("HTTP/1.1 {$ex->getCode()} {$ex->getErrorMessage()}");
            echo $ex->getMessage();

        } catch (Exception $ex) {
            header("Content-Type: text/plain");
            header("HTTP/1.1 503 Server Error");
            echo $ex->getTraceAsString();

        }
    }

    /**
     * HTTP GET Request sem parâmetros
     *
     * @return array $response
     */
    public function index() {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

    /**
     * HTTP GET Request com parâmetros
     *
     * @param Object $data
     * @return array $response
     */
    public function get($data) {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

    /**
     * HTTP POST Request
     *
     * @param Object $data
     * @return array $response
     */
    public function post($data) {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

    /**
     * HTTP PUT Request
     *
     * @param Object $data
     * @return array $response
     */
    public function put($data) {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

    /**
     * HTTP DELETE Request
     * 
     * @return array $response
     */
    public function delete($data) {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

}
