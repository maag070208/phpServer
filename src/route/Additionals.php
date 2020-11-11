<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getAllAdditionals', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $page = ((int) $page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT * FROM ADDITIONAL";


    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY ADDITIONAL_ID ASC LIMIT " . $page . ",20";
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


$app->post('/addAdditional', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $data = $request->getParsedBody();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            $sql = "SELECT * FROM ADDITIONAL WHERE ADDITIONAL_NAME='" . $data['additional'] . "'";
            $stmt = $db->query($sql);
            $existAdd = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($existAdd) {
                $ret = ["error" => 2, "text" => "The additional already exist"];
                return $response->withJson($ret);
            }

            $sql = "INSERT INTO ADDITIONAL(ADDITIONAL_NAME,PRICE,STATUS) VALUES (:ADDITIONAL_NAME,:PRICE,1)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("ADDITIONAL_NAME", $data['additional']);
            $stmt->bindParam("PRICE", $data['price']);

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
