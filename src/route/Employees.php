<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getAllEmployees', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $retailer = $request->getQueryParam('retailer');
    $txt = $request->getQueryParam('txt');
    $page = ((int) $page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT US.USER_ID,US.USER_NAME,US.PASS,US.USER_TYPE,US.STATUS,PER.NAME,PER.LAST_NAME,PER.EMAIL,PER.PHONE FROM USER US LEFT JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID LEFT JOIN EMPLOYEE EMP ON EMP.EMPLOYEE_ID=US.USER_ID WHERE US.USER_TYPE=4 ";
    if ($retailer != -1) {
        $sql .= " AND EMP.RETAILER_ID ='" . $retailer . "'";
    }
    if ($txt != "") {
        $sql .= " AND CONCAT( PER.NAME,  ' ', PER.LAST_NAME, ' ', US.USER_NAME, ' ', PER.EMAIL,' ', PER.PHONE ) LIKE '%" . $txt . "%'";
    }

    $sql.=" GROUP BY US.USER_ID";
    error_log($sql);
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY US.USER_ID DESC LIMIT " . $page . ",20";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($data as $valor) {

                $sql = "SELECT US.USER_ID,US.USER_NAME FROM EMPLOYEE EMP LEFT JOIN USER US ON US.USER_ID=EMP.RETAILER_ID WHERE EMP.EMPLOYEE_ID= " . $valor->USER_ID;
                error_log($sql);
                $gsent = $db->query($sql);
                $retailer = $gsent->fetchAll(PDO::FETCH_OBJ);
                $valor->RETAILERS = $retailer;
            }


            $db = null;
            $n = count($pages);
            error_log($n);
            $ret = ["data" => $data, "pages" => ceil($n / 20)];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getEmployeeRetailers', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $employee = $request->getQueryParam('employee');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT US.USER_ID,US.USER_NAME FROM EMPLOYEE EMP LEFT JOIN USER US ON US.USER_ID=EMP.RETAILER_ID WHERE EMP.EMPLOYEE_ID=" . $employee;

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);

            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $ret = ["data" => $data, "error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getEmployeesByRetailers', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT US.USER_ID,US.USER_NAME FROM EMPLOYEE EMP LEFT JOIN USER US ON US.USER_ID=EMP.EMPLOYEE_ID WHERE EMP.RETAILER_ID=" . $retailer;

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);

            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $ret = ["data" => $data, "error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/addEmployee', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();

    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {

        try {

            $db = new db();

            $db = $db->connect();
            //echo $veh;
            $sql = "SELECT COUNT(*) AS NUM FROM PERSON WHERE EMAIL='" . $dat['email'] . "'";
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

            if (($exist["0"]->NUM) != 0) {
                echo '{"error":{"text":"Email is already exist"}}';
                return;
            }

            $sql = "SELECT COUNT(*) AS NUM FROM USER WHERE USER_NAME='" . $dat['userName'] . "'";
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

            if (($exist["0"]->NUM) != 0) {
                echo '{"error":{"text":"User name is already exist"}}';
                return;
            }


            $sql = "SELECT MAX(PERSON_ID) AS NUM FROM PERSON";
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $idPerson = $gsent->fetchAll(PDO::FETCH_OBJ);
            if (($idPerson["0"]->NUM) == '')
                $idPerson = 1;
            else
                $idPerson = (($idPerson["0"]->NUM) + 1);

            $sql = "INSERT INTO PERSON(PERSON_ID,NAME,LAST_NAME,EMAIL,PHONE) VALUES (:id,:firstName,:lastName,:email,:phone)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $idPerson);
            $stmt->bindParam("firstName", $dat['firstName']);
            $stmt->bindParam("lastName", $dat['lastName']);
            $stmt->bindParam("email", $dat['email']);
            $stmt->bindParam("phone", $dat['directPhone']);

            $stmt->execute();

            $sql = "SELECT MAX(USER_ID) AS NUM FROM USER";
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $userID = $gsent->fetchAll(PDO::FETCH_OBJ);
            if (($userID["0"]->NUM) == '')
                $userID = 1;
            else
                $userID = (($userID["0"]->NUM) + 1);

            $sql = "INSERT INTO USER(USER_ID,USER_NAME,PASS,USER_TYPE,PERSON_ID) VALUES(:id,:userName,:pass,:type,:personId)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $userID);
            $stmt->bindParam("userName", $dat['userName']);
            $stmt->bindParam("pass", $dat['pass']);
            $USER_TYPE = 4;
            $stmt->bindParam("type", $USER_TYPE);
            $stmt->bindParam("personId", $idPerson);
            $stmt->execute();

            $sql = "INSERT INTO EMPLOYEE(RETAILER_ID,EMPLOYEE_ID) VALUES(:retailer,:employee)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("retailer", $dat['retailer']);
            $stmt->bindParam("employee", $userID);


            $stmt->execute();

            $db = null;
            $ret = ["user_id" => $userID, "error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":"' . $e->getMessage() . '"}}';
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});



$app->post('/assignEmployee', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();

    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {

        try {

            $db = new db();

            $db = $db->connect();
            //echo $veh;
           

            $sql = "INSERT INTO EMPLOYEE(RETAILER_ID,EMPLOYEE_ID) VALUES(:retailer,:employee)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("retailer", $dat['retailer']);
            $stmt->bindParam("employee", $dat['employee']);


            $stmt->execute();

            $db = null;
            $ret = ["error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":"' . $e->getMessage() . '"}}';
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->post('/revokeEmployee', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();

    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {

        try {
            $db=new db();
            $db=$db->connect();
      
            $sql="DELETE FROM EMPLOYEE WHERE EMPLOYEE_ID=".$dat['employee']." AND RETAILER_ID=".$dat['retailer'];
            $gsent = $db->prepare($sql);
            $gsent->execute();
      
            $db = null;
            $ret = ["error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch(PDOException $e) {
            
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
