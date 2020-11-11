<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getAllClients', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $txt = $request->getQueryParam('txt');
    $reservationless = $request->getQueryParam('reservationless');
    $export=$request->getQueryParam('export');
    $page = ((int)$page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql="SELECT * FROM USER USR LEFT JOIN PERSON PER ON PER.PERSON_ID=USR.PERSON_ID WHERE USR.USER_TYPE=3 AND USR.STATUS=1 ";
    
    if($reservationless==1){
       $sql.=" AND USR.USER_ID NOT IN (SELECT RES.CLIENT_ID FROM RESERVATION RES INNER JOIN USER USR ON USR.USER_ID=RES.CLIENT_ID)";
    }

    if ($txt != "") {
        $sql .= " AND CONCAT( PER.NAME,  ' ', PER.LAST_NAME, ' ', USR.USER_NAME, ' ', PER.EMAIL,' ', PER.PHONE ) LIKE '%" . $txt . "%'";
    }
    error_log($sql);
    if ($valid) {
        try {

            $db = new db();
            $db = $db->connect();
            
            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY USR.USER_ID DESC ";
            if($export!=1){
                $sql .= " LIMIT " . $page . ",20";
            }
           
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            error_log($sql);
            $n=count($pages);
            error_log($n);
            $ret=["data"=>$data,"pages"=>ceil($n/20)];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            error_log($e);
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});




//CANCEL RESERVATION
$app->post('/deleteClient', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $dat = $request->getParsedBody();

        try {

            $db = new db();
            $db = $db->connect();
            $sql = "UPDATE USER SET STATUS=0 WHERE  USER_ID=" . $dat["user_id"];
            $gsent = $db->prepare($sql);
            $gsent->execute();

            $db = null;

            $ret = ["error" => 0];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
