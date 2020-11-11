<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getAllServices', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $opt = $request->getQueryParam('option');
    $page = ((int) $page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($opt == 1 || $opt == 2) {
        $sql = "SELECT * FROM SERVICE WHERE SERVICE_TYPE=" . $opt;
    } else {
        $sql = "SELECT * FROM SERVICE WHERE STATUS=1";
    }

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY ORDER_ID ASC LIMIT " . $page . ",20";
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


$app->get('/getServicesItem', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $serviceType = $request->getQueryParam('serviceType');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT * FROM SERVICE WHERE STATUS=1 AND SERVICE_TYPE=" . $serviceType;

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            $cont = 0;
            foreach ($data as &$valor) {
                $sql = "SELECT AD.ADDITIONAL_ID,ASR.SERVICE_ID,ADDITIONAL_NAME,PRICE FROM ADDITIONAL_SERVICE_RELATION ASR INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ASR.ADDITIONAL_ID WHERE SERVICE_ID='" . $valor->SERVICE_ID . "'";
                $stmt = $db->query($sql);
                $data2 = $stmt->fetchAll(PDO::FETCH_OBJ);
                $data[$cont]->ADDITIONALS = $data2;
                $cont++;
            }
            $db = null;
            $ret = ["data" => $data, "error" => '0'];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            error_log("error");
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/addService', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT (MAX(LS_ID)+1) AS ID FROM LOCATION_SERVICE;";
    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $qry = $gsent->fetchAll(PDO::FETCH_OBJ);
            $LS_ID = $qry["0"]->ID;

            $sql = "INSERT INTO LOCATION_SERVICE(LS_ID,LOCATION_ID,SERVICE_ID,PRICE,STATUS) VALUES (:lsid,:location,:service,:price,:status)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("lsid", $LS_ID);
            $stmt->bindParam("location", $data['LOCATION_ID']);
            $stmt->bindParam("service", $data['SERVICE_ID']);
            $stmt->bindParam("price", $data['PRICE']);
            $stmt->bindParam("status", $data['STATUS']);


            $stmt->execute();

            $db = null;
            $data = array("id" => $LS_ID);
            $ret = ["data" => $data, "error" => 0];
            return $response->withJson($ret);
            //echo '{"error":{"text":"0"},"LS_ID":' . $LS_ID . '}';
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/addAdditionalService', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $sql = "INSERT INTO ADDITIONAL_SERVICE(ADDITIONAL_ID,SERVICE_ID,LS_ID,STATUS) VALUES (:additional,:service,:lsid,:status)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("additional", $data['ADDITIONAL_ID']);
            $stmt->bindParam("service", $data['SERVICE_ID']);
            $stmt->bindParam("lsid", $data['LS_ID']);
            $stmt->bindParam("status", $data['STATUS']);
            $stmt->execute();

            $db = null;
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getAbleServices', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $id = $request->getQueryParam('location');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAME,SE.SERVICE_CATEGORY,LS.STATUS,LS.PRICE,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE SE.STATUS=1 AND  LO.LOCATION_ID=" . $id;
    //$sql="SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAMELS.PRICE,LO.TAX,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LO.LOCATION_ID=".$id;
    error_log($sql);
    if ($valid) {

        try {

            $db = new db();
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            $json = "[";
            $cont = 0;
            foreach ($resultado as &$valor) {
                $sql = 'SELECT * FROM ADDITIONAL_SERVICE_RELATION WHERE SERVICE_ID=' . $valor->SERVICE_ID;
                $stmt = $db->query($sql);
                $resultado3 = $stmt->fetchAll(PDO::FETCH_OBJ);
                $adicionales = array();
                foreach ($resultado3 as &$valor3) {
                    $sql = "SELECT AD.ADDITIONAL_ID,SERVICE_ID,LS_ID,ADS.STATUS,ADDITIONAL_NAME,PRICE,ADS.ID FROM ADDITIONAL_SERVICE ADS INNER JOIN ADDITIONAL AD ON ADS.ADDITIONAL_ID=AD.ADDITIONAL_ID WHERE LS_ID='" . $valor->LS_ID . "' AND AD.ADDITIONAL_ID=" . $valor3->ADDITIONAL_ID;
                    $stmt = $db->query($sql);
                    $resultado2 = $stmt->fetchAll(PDO::FETCH_OBJ);
                    array_push($adicionales, $resultado2);
                }
                $resultado[$cont]->ADDITIONALS = $adicionales;
                $cont++;
            }

            $db = null;


            $ret = ["data" => $resultado, "error" => '0'];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/editServiceStatus', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {

        try {
            $db = new db();
            $db = $db->connect();

            $sql = "UPDATE LOCATION_SERVICE SET STATUS='" . $dat['STATUS'] . "'  WHERE LS_ID='" . $dat['LS_ID'] . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $db = null;
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});



$app->post('/editAdditionalStatus', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();

            $sql = "UPDATE ADDITIONAL_SERVICE SET STATUS='" . $dat['STATUS'] . "'  WHERE ID='" . $dat['ID'] . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $db = null;
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
