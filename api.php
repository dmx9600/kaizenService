<?php

require_once("Rest.inc.php");

class API extends REST {

    public $data = "";

    const DB_SERVER = "localhost";
    const DB_USER = "root";
    const DB_PASSWORD = "bingodin";
    const DB = "kaizen";

    private $db = NULL;
    private $mysqli = NULL;

    public function __construct() {
        parent::__construct();    // Init parent contructor
        $this->dbConnect();     // Initiate Database connection
    }

    /*
     *  Connect to Database
     */

    private function dbConnect() {
        $this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
    }

    /*
     * Dynmically call the method based on the query string
     */

    public function processApi() {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404); // If the method not exist with in this class "Page not found".
    }

    //Department API
    private function department() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT *FROM department";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function departmentId() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "SELECT *FROM department where Id=$id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();
                $this->response($this->json($result), 200); // send user details
            }
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function DepartmentInsert() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $department = json_decode(file_get_contents("php://input"), true);
        $column_names = array('DepartmentName', 'StatusId', 'CreateDate', 'CreateUserId', 'ModifyDate', 'ModifyUserId');
        $keys = array_keys($department);
        $columns = '';
        $values = '';
        //$this->response($this->json($department), 200);
        
        
        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $department[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO department(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($department)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Department Created Successfully.", "data" => $department);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    
      
        }

    //User API
    private function User() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT user.Id as UserId, user.EPF, user.Name as EMPName,user.UserName,department.Id as DepartmentId,department.DepartmentName"
                . " FROM user"
                . " INNER JOIN department ON"
                . " user.departmentId=department.Id"
                . " where user.StatusId =1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function UserInsert() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $user = json_decode(file_get_contents("php://input"), true);
        $column_names = array('EPF', 'Name', 'DepartmentId', 'UserName', 'StatusId', 'CreateDate', 'CreateUserId', 'ModifyDate', 'ModifyUserId');
        $keys = array_keys($user);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $user[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO user(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($user)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "User Created Successfully.", "data" => $user);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }

    private function UserDelete() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $user = json_decode(file_get_contents("php://input"), true);
        $id = (int) $user['Id'];

        $query = "UPDATE user set StatusId = 3 WHERE 	Id = $id";
        if (!empty($user)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "User Delete Successfully.", "data" => $user);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }

    private function UserUpdate() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $user = json_decode(file_get_contents("php://input"), true);
        $id = (int) $user['Id'];
        $column_names = array('EPF', 'Name', 'UserName', 'DepartmentId', 'ModifyDate', 'ModifyUserId');
        $keys = array_keys($user['user']);
        $columns = '';
        $values = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If key does not exist, insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $user['user'][$desired_key];
            }
            $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }
        $query = "UPDATE user SET " . trim($columns, ',') . " WHERE Id=$id";
        if (!empty($user)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Employee " . $id . " Updated Successfully.", "data" => $user);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // "No Content" status
    }

    /*
     * 	Encode array into JSON
     */

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

// Initiiate Library	
$api = new API;
$api->processApi();
?>