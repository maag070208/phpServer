<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getUserGeneralInfo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $id = $request->getQueryParam('user_id');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT US.USER_ID,US.USER_NAME,US.PASS,US.USER_TYPE,USTY.DESCRIPTION AS US_TYPE,US.STATUS,PER.NAME,PER.LAST_NAME,PER.EMAIL,PER.PHONE,PER.PERSON_ID";
    $sql .=" FROM USER US LEFT JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID LEFT JOIN USER_TYPE USTY ON USTY.UT_ID=US.USER_TYPE WHERE US.USER_ID=".$id;
   
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
        
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

           
            $ret=["data"=>$data,"error"=>0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/editUser', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $data = $request->getParsedBody();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            
            $sql = "UPDATE USER SET PASS=:pass where USER_ID=".$data['user_id'];
            $stmt = $db->prepare($sql);
            $stmt->bindParam("pass", $data['pass']);

            $stmt->execute();

            $sql = "UPDATE PERSON SET NAME=:name,LAST_NAME=:last,PHONE=:phone where PERSON_ID=".$data['person_id'];
            $stmt = $db->prepare($sql);
            $stmt->bindParam("name", $data['name']);
            $stmt->bindParam("last", $data['last_name']);
            $stmt->bindParam("phone", $data['phone']);


            $stmt->execute();
            error_log($sql);
            $db = null;
            $ret = ["error" => 0, "text" => "Successful update"];
            
            return $response->withJson($ret);
        } catch (PDOException $e) {
            
            $ret = ["error" => 1, "message" => $e->getMessage()];
            
            return $response->withJson($ret);
        }
        return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
    }
});