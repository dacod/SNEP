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
 * Controller for RESTful WebServices with HTTP and JSON.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Rest_Controller extends Zend_Controller_Action {

    /**
     * Disables the view for the Zend Framework environment.
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
     * Treats all the requests for GET, POST, PUT and DELETE routing for the right method.
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
     * Index action
     *
     * Same as a GET without any params.
     *
     * @return array $response
     */
    public function index() {
        throw new Snep_Rest_Exception_MethodNotAllowed();
    }

    /**
     * GET action
     *
     * Normal HTTP GET action with parameters set.
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
