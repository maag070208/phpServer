<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->get('/getPromo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $promo = $request->getQueryParam('promo');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT * FROM PROMO WHERE ID=".$promo;


    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $ret = ["data" => $data,'error' => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getAllPromo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $page = ((int) $page) * 20;
    $term = $request->getQueryParam('term');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT * FROM PROMO";

    if ($term != "") {
        $sql .= " where CONCAT( CODE,  ' ', DISCOUNT) LIKE '%" . $term . "%'";
    }

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY ID ASC LIMIT " . $page . ",20";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
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

$app->post('/editPromo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $data = $request->getParsedBody();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            
            $sql = "UPDATE PROMO SET CODE=:CODE,DISCOUNT=:DISCOUNT,START=:START,FINISH=:FINISH,USE_LIMIT=:USE_LIMIT where ID=:ID";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("ID", $data['id']);
            $stmt->bindParam("CODE", $data['promo']);
            $stmt->bindParam("DISCOUNT", $data['discount']);
            $stmt->bindParam("START", $data['startDate']);
            $stmt->bindParam("FINISH", $data['finishDate']);
            $stmt->bindParam("USE_LIMIT", $data['limit']);
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

$app->post('/addPromo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $data = $request->getParsedBody();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            $sql = "SELECT * FROM PROMO WHERE CODE='".$data['promo']."'";
            $stmt = $db->query($sql);
            $existCode = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if($existCode){
                $ret = ["error" => 2, "text" => "The promo code already exist"];
                return $response->withJson($ret);
            }

            $sql = "INSERT INTO PROMO(CODE,DISCOUNT,START,FINISH,USE_LIMIT) VALUES (:CODE,:DISCOUNT,:START,:FINISH,:LIMIT)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("CODE", $data['promo']);
            $stmt->bindParam("DISCOUNT", $data['discount']);
            $stmt->bindParam("START", $data['startDate']);
            $stmt->bindParam("FINISH", $data['finishDate']);
            $stmt->bindParam("LIMIT", $data['limit']);
            $stmt->execute();

            $db = null;
            $ret = ["error" => 0, "text" => "Successful insertion"];
            
            return $response->withJson($ret);
        } catch (PDOException $e) {
            
            $ret = ["error" => 1, "text" => $e->getMessage()];
            
            return $response->withJson($ret);
        }
        return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
    }
});
