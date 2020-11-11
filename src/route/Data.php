<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../src/email.php';
require '../src/sendPush.php';
date_default_timezone_set('America/Mexico_City');
$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
    ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->get('/sendPushTest/{reser}/{token}/{type}/{client}', function (Request $request, Response $response, array $args) {
  $reser = $args['reser'];
  $token = $args['token'];
  $type = $args['type'];
  $client = $args['client'];
  try {
    $db = new db();
    $db = $db->connect();

    //AGREGANDO NOTIFICACION
  /*  $status = 1;
    $sql = "INSERT INTO NOTIFY(USER_FK,RESERVATION_FK,TYPE,STATUS) VALUES (:user,:res,:type,:status)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("user", $client);
    $stmt->bindParam("res", $reser);
    $stmt->bindParam("type", $type);
    $stmt->bindParam("status", $status);
    $stmt->execute();*/
    $db=null;
    if ($type == 2) {
      sendPushTip2($token, $reser);
    } else {
      sendPushTip($token, $reser);
    }
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getVersion/{app}', function (Request $request, Response $response, array $args) {
  $app = $args['app'];

  if ($app == 1) {
    $sql = "SELECT MYEZCARWASH_VERSION AS VERSION FROM VERSION ORDER BY VERSION_ID DESC LIMIT 1";
  } else {
    $sql = "SELECT RIDEWASH_VERSION AS VERSION FROM VERSION ORDER BY VERSION_ID DESC LIMIT 1";
  }

  try {
    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    echo json_encode($result);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/sendPush/{title}/{body}/', function (Request $request, Response $response, array $args) {
  $title = $args['title'];
  $body = $args['body'];

  $sql = "SELECT MOBILE_TOKEN FROM USER WHERE MOBILE_TOKEN IS NOT NULL";

  try {
    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    $registers_id = array();
    foreach ($resultado as &$valor) {
      array_push($registers_id, $valor->MOBILE_TOKEN);
    }
    sendPushMsg($registers_id, $title, $body);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
/*
***********************CITY****************************************
*/
//OBTIENE TODAS LAS CIUDADES
$app->get('/getCitys', function (Request $request, Response $response, array $args) {

  $sql = "SELECT * FROM CITY";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/sendContactMail/{name}/{email}/{phone}/{msg}', function (Request $request, Response $response, array $args) {
  $name = $args['name'];
  $email = $args['email'];
  $phone = $args['phone'];
  $msg = $args['msg'];
  sendContactMail($name, $email, $phone, $msg);
});
$app->get('/sendContactMail2/{name}/{email}/{phone}/{company}/{address}/{time}', function (Request $request, Response $response, array $args) {
  $name = $args['name'];
  $email = $args['email'];
  $phone = $args['phone'];
  $company = $args['company'];
  $address = $args['address'];
  $time = $args['time'];
  sendContactMail2($name, $email, $phone, $company, $address, $time);
});

$app->get('/manualReschedule/{reservation}', function (Request $request, Response $response, array $args) {
  $res = $args['reservation'];
  $email = sendClientReschedule($res);
  $email2 = sendAdminData($res);

  echo $email;
  echo $email2;
});

$app->get('/getAdditionals/{idService}/{locationId}', function (Request $request, Response $response, array $args) {
  $id = $args['idService'];
  $location_id = $args['locationId'];
  $sql = "SELECT AD.ADDITIONAL_ID AS ID,AD.ADDITIONAL_NAME AS NAME,AD.PRICE FROM ADDITIONAL_SERVICE ADS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ADS.ADDITIONAL_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=ADS.LS_ID WHERE ADS.STATUS=1 AND LS.SERVICE_ID=" . $id . " AND LS.LOCATION_ID=" . $location_id . " ORDER BY AD.ADDITIONAL_ID";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    echo json_encode($e);
  }
});
/*
* ******************LOCATION***************************************************
*/

$app->get('/getTimesFromTo/{from}/{to}', function (Request $request, Response $response, array $args) {
  $from = $args['from'];
  $to = $args['to'];
  $sql = "SELECT * FROM SCHEDULE WHERE SCHEDULE_ID>=" . $from . " AND SCHEDULE_ID<=" . $to;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getMaxMinTimes/{location}', function (Request $request, Response $response, array $args) {
  $loc = $args['location'];
  $sql = "SELECT * FROM SCHEDULE WHERE SCHEDULE_ID=(SELECT MAX(SCHEDULE_ID) FROM SCHEDULE_LOCATION WHERE LOCATION_ID=" . $loc . ") OR SCHEDULE_ID=(SELECT MIN(SCHEDULE_ID) FROM SCHEDULE_LOCATION WHERE LOCATION_ID=" . $loc . ")";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getScheduleLocation/{location}', function (Request $request, Response $response, array $args) {
  $loc = $args['location'];
  $sql = "SELECT * FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SL.SCHEDULE_ID  WHERE  SL.LOCATION_ID=" . $loc;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getTimes', function (Request $request, Response $response, array $args) {

  $sql = "SELECT * FROM SCHEDULE WHERE SCHEDULE_ID>=6";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getLocations/{service_type}', function (Request $request, Response $response, array $args) {
  $st = $args['service_type'];
  $sql = "SELECT * FROM LOCATION WHERE STATUS=1 AND SERVICE_TYPE=" . $st;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    //$loc=$stmt->fetchAll(PDO::FETCH_OBJ);

    $resultado = $stmt->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      if ($cont == count($resultado)) {
        $json .= '{"LOCATION_ID":' . $valor[0] . ',"LOCATION_NAME":"' . $valor[1] . '","RETAILER_ID":' . $valor[2] . ',"ZIP":' . $valor[3] . ',"TAX":' . $valor[4] . ',"SERVICE_TYPE":' . $valor[5] . ',"LAT":"' . $valor[6] . '","LNG":"' . $valor[7] . '","ZIP":[';
        $sql = "SELECT * FROM LOCATION_ZIP WHERE LOCATION_ID=" . $valor[0];
        $stmt = $db->query($sql);
        $zips = $stmt->fetchAll();
        $contZip = 1;
        foreach ($zips as &$valorZip) {
          if ($contZip == count($zips)) {
            $json .= $valorZip[1];
          } else {
            $json .= $valorZip[1] . ",";
          }
          $contZip++;
        }
        $json .= "]}";
      } else {
        $json .= '{"LOCATION_ID":' . $valor[0] . ',"LOCATION_NAME":"' . $valor[1] . '","RETAILER_ID":' . $valor[2] . ',"ZIP":' . $valor[3] . ',"TAX":' . $valor[4] . ',"SERVICE_TYPE":' . $valor[5] . ',"LAT":"' . $valor[6] . '","LNG":"' . $valor[7] . '","ZIP":[';
        $sql = "SELECT * FROM LOCATION_ZIP WHERE LOCATION_ID=" . $valor[0];
        $stmt = $db->query($sql);
        $zips = $stmt->fetchAll();
        $contZip = 1;
        foreach ($zips as &$valorZip) {
          if ($contZip == count($zips)) {
            $json .= $valorZip[1];
          } else {
            $json .= $valorZip[1] . ",";
          }
          $contZip++;
        }
        $json .= "]},";
      }

      $cont++;
    }
    $json .= "]";



    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo $json;
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

function aasort(&$array, $key)
{
  $sorter = array();
  $ret = array();
  reset($array);

  foreach ($array as $ii => $va) {
    $sorter[] = $va[$key];
  }

  arsort($sorter);

  foreach ($sorter as $ii => $va) {
    $ret[] = $array[$ii];
  }

  return $array = $ret;
}

$app->get('/getLocationPolygon/{service_type}', function (Request $request, Response $response, array $args) {
  $st = $args['service_type'];
  $sql = "SELECT * FROM LOCATION WHERE STATUS=1 AND SERVICE_TYPE=" . $st;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    //$loc=$stmt->fetchAll(PDO::FETCH_OBJ);

    $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as &$valor) {
      //$json.='{"LOCATION_ID":'.$valor[0].',"LOCATION_NAME":"'.$valor[1].'","RETAILER_ID":'.$valor[2].',"ZIP":'.$valor[3].',"TAX":'.$valor[4].',"SERVICE_TYPE":'.$valor[5].',"LAT":"'.$valor[6].'","LNG":"'.$valor[7].'","COORDINATES":[';
      $sql = "SELECT * FROM LOCATION_AREA WHERE LOCATION_ID=" . $valor->LOCATION_ID;
      $stmt = $db->query($sql);
      $coors = $stmt->fetchAll(PDO::FETCH_OBJ);
      $resultado[$cont]->COORDINATES = $coors;
      $sql = "SELECT AVG(RAITING) AS RAITING,COUNT(*) AS TOTAL FROM LOCATION_RATING WHERE LOCATION_ID=" . $valor->LOCATION_ID;
      $stmt = $db->query($sql);
      $avg = $stmt->fetchAll(PDO::FETCH_OBJ);
      if ($avg[0]->TOTAL == 0) {
        $resultado[$cont]->RAITING = 0;
      } else {
        $resultado[$cont]->RAITING = number_format($avg[0]->RAITING, 2);
      }


      $cont++;
    }


    $db = null;

    header("Access-Control-Allow-Origin: *");
    //echo $resultado;
    $json = json_encode($resultado);
    $array = json_decode($json, true);
    $sort = aasort($array, "RAITING");

    //echo $order;

    //$list = array_sort($someArray, 'RAITING', SORT_DESC);
    echo json_encode($sort);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getNotify/{user}/{type}', function (Request $request, Response $response, array $args) {
  $id = $args['user'];
  $type = $args['type'];
  $sql = "SELECT * FROM NOTIFY WHERE STATUS=1 AND USER_FK=" . $id . " AND TYPE=" . $type . " LIMIT 1";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (count($result) != 0) {
      $sql = "UPDATE NOTIFY SET STATUS=0 WHERE NOTIFY_ID=" . $result[0]->NOTIFY_ID;
      $stmt = $db->prepare($sql);
      $stmt->execute();
    }
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($result);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getLastLocation/{user}', function (Request $request, Response $response, array $args) {
  $id = $args['user'];
  $sql = "SELECT ADDRESS,LAT,LNG,DATE FROM RESERVATION WHERE CLIENT_ID=" . $id . " AND SERVICE_TYPE=2 ORDER BY DATE DESC LIMIT 1 ";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($result);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


$app->get('/getLocationZips/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT * FROM LOCATION_ZIP WHERE LOCATION_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($result);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->post('/deleteZip', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  try {
    $db = new db();
    $db = $db->connect();

    $sql = "DELETE FROM LOCATION_ZIP WHERE LOCATION_ID=" . $data['LOCATION_ID'] . " AND ID=" . $data['ID'];
    $gsent = $db->prepare($sql);
    $gsent->execute();

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo '{"error":{"text":0}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
$app->post('/addZip', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  try {
    $db = new db();
    $db = $db->connect();

    $sql = "INSERT INTO LOCATION_ZIP(LOCATION_ID,ZIP) VALUES (:LOCATION_ID,:ZIP)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("LOCATION_ID", $data['LOCATION_ID']);
    $stmt->bindParam("ZIP", $data['ZIP']);

    $stmt->execute();
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo '{"error":{"text":0}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//EJEMPLO PARA LA APP
$app->get('/appMovil', function (Request $request, Response $response, array $args) {
  //$id = $args['location'];
  $data = $request;
  $data = $request->getQueryParams();
  error_log("hola---->" . json_encode($uri));
  $sql = "SELECT * FROM LOCATION_ZIP WHERE LOCATION_ID=8";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getLocation/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT * FROM LOCATION WHERE LOCATION_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getLocationByRetail/{retailer}/{serviceType}', function (Request $request, Response $response, array $args) {
  $id = $args['retailer'];
  $sT = $args['serviceType'];
  $sql = "SELECT * FROM LOCATION WHERE STATUS=1 AND RETAILER_ID=" . $id . " AND SERVICE_TYPE=" . $sT;
  error_log($sql);
  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getLocationByRetail/{retailer}', function (Request $request, Response $response, array $args) {
  $id = $args['retailer'];
  $sT = $args['serviceType'];
  $sql = "SELECT * FROM LOCATION WHERE STATUS=1 AND RETAILER_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
/*
*************************SERVICES
*/

$app->get('/getServicesLocation/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAME,SE.SERVICE_CATEGORY,SE.DESCRIPTION,LS.PRICE,LO.TAX,LS.STATUS FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LS.STATUS=1 AND SE.STATUS=1 AND LO.LOCATION_ID=" . $id . " ORDER BY ORDER_ID";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/get1Service/{id}', function (Request $request, Response $response, array $args) {
  $id = $args['id'];
  $sql = "SELECT * FROM SERVICE WHERE SERVICE_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllAdditionals', function (Request $request, Response $response, array $args) {

  $sql = "SELECT * FROM ADDITIONAL WHERE STATUS=1";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllAdditionals2', function (Request $request, Response $response, array $args) {

  $sql = "SELECT * FROM ADDITIONAL";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


$app->get('/getRelationService/{id}', function (Request $request, Response $response, array $args) {
  $id = $args['id'];
  $sql = "SELECT AD.ADDITIONAL_ID,AD.ADDITIONAL_NAME,AD.PRICE,ASR.ID,ASR.SERVICE_ID FROM ADDITIONAL_SERVICE_RELATION ASR INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ASR.ADDITIONAL_ID WHERE SERVICE_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/addServiceAddRelation', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();
    $sql = "SELECT STATUS FROM ADDITIONAL_SERVICE  where SERVICE_ID=" . $dat['SERVICE_ID'] . " and ADDITIONAL_ID=" . $dat['ADDITIONAL_ID'] . " LIMIT 1";

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $status = $gsent->fetchAll(PDO::FETCH_OBJ);
    error_log($status["0"]->STATUS);
    if (($status["0"]->STATUS) == '0') {
      $sql = "UPDATE ADDITIONAL_SERVICE SET STATUS=1 WHERE SERVICE_ID=" . $dat['SERVICE_ID'] . " AND ADDITIONAL_ID=" . $dat['ADDITIONAL_ID'];
      $stmt = $db->prepare($sql);
      $stmt->execute();
      $sql = "INSERT INTO ADDITIONAL_SERVICE_RELATION(ADDITIONAL_ID,SERVICE_ID) VALUES(:addId,:serviceId)";
      $stmt = $db->prepare($sql);
      $stmt->bindParam("addId", $dat['ADDITIONAL_ID']);
      $stmt->bindParam("serviceId", $dat['SERVICE_ID']);
      $stmt->execute();
      error_log($sql);
    } else {
      $sql = "INSERT INTO ADDITIONAL_SERVICE_RELATION(ADDITIONAL_ID,SERVICE_ID) VALUES(:addId,:serviceId)";
      $stmt = $db->prepare($sql);
      $stmt->bindParam("addId", $dat['ADDITIONAL_ID']);
      $stmt->bindParam("serviceId", $dat['SERVICE_ID']);
      $stmt->execute();


      $sql = 'SELECT LS_ID FROM LOCATION_SERVICE WHERE SERVICE_ID=' . $dat['SERVICE_ID'];
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $locationService = $gsent->fetchAll(PDO::FETCH_OBJ);
      foreach ($locationService as &$valor) {
        error_log($valor->LS_ID . "jhdsjahdjkhaskjdhaksjdh");
        $sql = "INSERT INTO ADDITIONAL_SERVICE(ADDITIONAL_ID,SERVICE_ID,LS_ID,STATUS) VALUES(:addId,:serviceId,:lsid,:status)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("addId", $dat['ADDITIONAL_ID']);
        $stmt->bindParam("serviceId", $dat['SERVICE_ID']);
        $statusAS = 1;
        $stmt->bindParam("status", $statusAS);
        $stmt->bindParam("lsid", $valor->LS_ID);
        $stmt->execute();
      }
    }

    $db = null;
    echo '{"error":{"text":0}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/updateStatusAdditionalServices', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "DELETE FROM ADDITIONAL_SERVICE_RELATION WHERE ID=" . $dat['ID'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $sql = "UPDATE ADDITIONAL_SERVICE SET STATUS=0 WHERE SERVICE_ID=" . $dat['SERVICE_ID'] . " AND ADDITIONAL_ID=" . $dat['ADDITIONAL_ID'];
    $stmt = $db->prepare($sql);
    error_log($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":{"text":0}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->get('/getServices/{opc}', function (Request $request, Response $response, array $args) {
  $opc = $args['opc'];
  if ($opc == 1 || $opc == 2) {
    $sql = "SELECT SERVICE_ID,SERVICE_NAME,DESCRIPTION,SERVICE_TYPE,PRICE FROM SERVICE WHERE SERVICE_TYPE=" . $opc;
  } else {
    $sql = "SELECT SERVICE_ID,SERVICE_NAME,DESCRIPTION,SERVICE_TYPE,PRICE FROM SERVICE";
  }


  try {

    $db = new db();
    $db = $db->connect();
    $stmt = $db->query($sql);
    $resultado = $stmt->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      if ($cont == count($resultado)) {
        $json .= '{"SERVICE_ID":' . $valor[0] . ',"SERVICE_NAME":"' . $valor[1] . '","SERVICE_TYPE":' . $valor[3] . ',"DESCRIPTION":' . $valor[2] . ',"PRICE":' . $valor[4] . ',"ADDITIONALS":';
        $sql2 = "SELECT AD.ADDITIONAL_ID,SERVICE_ID,ADDITIONAL_NAME FROM ADDITIONAL_SERVICE_RELATION ADR  INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ADR.ADDITIONAL_ID WHERE SERVICE_ID=" . $valor[0];
        $stmt2 = $db->query($sql2);
        $resultado2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
        $json .= json_encode($resultado2) . "}";
      } else {
        $json .= '{"SERVICE_ID":' . $valor[0] . ',"SERVICE_NAME":"' . $valor[1] . '","SERVICE_TYPE":' . $valor[3] . ',"DESCRIPTION":' . $valor[2] . ',"PRICE":' . $valor[4] . ',"ADDITIONALS":';
        $sql2 = "SELECT AD.ADDITIONAL_ID,SERVICE_ID,ADDITIONAL_NAME FROM ADDITIONAL_SERVICE_RELATION ADR  INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ADR.ADDITIONAL_ID WHERE SERVICE_ID=" . $valor[0];
        $stmt2 = $db->query($sql2);
        $resultado2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
        $json .= json_encode($resultado2) . "},";
      }

      $cont++;
    }
    $json .= "]";
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo $json;
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getAbleServices/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAME,LS.STATUS,LS.PRICE,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE  LO.LOCATION_ID=" . $id;
  //$sql="SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAMELS.PRICE,LO.TAX,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LO.LOCATION_ID=".$id;
  error_log($sql);
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

    header("Access-Control-Allow-Origin: *");

    echo json_encode($resultado);
    //return $response->withJson($resultado);

  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getAddServices/{serviceType}', function (Request $request, Response $response, array $args) {
  $serviceType = $args['serviceType'];
  $sql = "SELECT * FROM SERVICE WHERE SERVICE_TYPE=" . $serviceType;
  //$sql="SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAMELS.PRICE,LO.TAX,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LO.LOCATION_ID=".$id;
  error_log($sql);
  try {

    $db = new db();
    $db = new db();
    $db = $db->connect();
    $stmt = $db->query($sql);
    $resultado = $stmt->fetchAll();
    $json = "[";
    $cont = 0;
    foreach ($resultado as &$valor) {
      $sql = "SELECT AD.ADDITIONAL_ID,ASR.SERVICE_ID,ADDITIONAL_NAME,PRICE FROM ADDITIONAL_SERVICE_RELATION ASR INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=ASR.ADDITIONAL_ID WHERE SERVICE_ID='" . $valor[0] . "'";
      $stmt = $db->query($sql);
      $resultado2 = $stmt->fetchAll(PDO::FETCH_OBJ);
      $resultado[$cont]["ADDITIONALS"] = $resultado2;
      $cont++;
    }

    $db = null;

    header("Access-Control-Allow-Origin: *");

    //echo json_encode($resultado);
    return $response->withJson($resultado);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getAllServices/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAME,LS.PRICE,LO.TAX,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LO.LOCATION_ID=" . $id;
  //$sql="SELECT LS.LS_ID,LS.LOCATION_ID,SE.SERVICE_ID,SE.SERVICE_NAMELS.PRICE,LO.TAX,SE.DESCRIPTION FROM LOCATION_SERVICE LS INNER JOIN SERVICE SE ON LS.SERVICE_ID=SE.SERVICE_ID INNER JOIN LOCATION LO ON LO.LOCATION_ID=LS.LOCATION_ID WHERE LO.LOCATION_ID=".$id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->post('/edit1Service', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE SERVICE SET PRICE='" . $dat['PRICE'] . "', SERVICE_NAME='" . $dat['SERVICE_NAME'] . "'  WHERE SERVICE_ID='" . $dat['SERVICE_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/editServiceStatus', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE LOCATION_SERVICE SET STATUS='" . $dat['STATUS'] . "'  WHERE LS_ID='" . $dat['LS_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/editAdditionalStatus', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE ADDITIONAL_SERVICE SET STATUS='" . $dat['STATUS'] . "'  WHERE ID='" . $dat['ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
/*
*************************CAR
*/


$app->get('/getMakes', function (Request $request, Response $response, array $args) {

  $sql = "SELECT MAKE_ID AS id,MAKE_NAME AS name FROM CAR_MAKE ";
  $data = array('error' => 1, 'desc' => 'nigga');
  try {

    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      if ($cont == count($resultado)) {
        $json .= '{"id":' . $valor[0] . ',"name":"' . $valor[1] . '"}';
      } else {
        $json .= '{"id":' . $valor[0] . ',"name":"' . $valor[1] . '"},';
      }

      $cont++;
    }
    $json .= "]";
    $db = null;
    header("Access-Control-Allow-Origin: *");
    print_r($json);
    //print_r($resultado);

  } catch (PDOException $e) { }
});

$app->get('/getModels/{make}', function (Request $request, Response $response, array $args) {
  $id = $args['make'];
  $sql = "SELECT * FROM CAR_MODEL WHERE MAKE_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


$app->get('/getTime/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT SL.LOCATION_ID,SC.HOUR FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SL.SCHEDULE_ID WHERE SL.LOCATION_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $citys = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($citys);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getYear', function (Request $request, Response $response, array $args) {
  header("Access-Control-Allow-Origin: *");

  echo date("Y");
});

$app->get('/getCurrentDate', function (Request $request, Response $response, array $args) {
  header("Access-Control-Allow-Origin: *");

  echo date("Y/m/d");
});

$app->get('/getSchedule/{date}/{location}', function (Request $request, Response $response, array $args) {

  //  echo $request->getParsedBody();
  $location = $args['location'];
  $date = $args['date'];
  $times = [];
  //echo $location;
  //echo $date;
  try {

    $db = new db();

    $db = $db->connect();
    $sql = "SELECT * FROM RESERVATION WHERE LOCATION_ID=" . $location . " AND DATE='" . $date . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (count($resultado) <= 0) {
      //print_r("1");
      $sql = "SELECT SL.ID,LOCATION_ID, SC.HOUR_12,SC.HOUR,SC.ORD FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SL.SCHEDULE_ID  WHERE SL.MAX>0 AND LOCATION_ID=" . $location . " ORDER BY SC.ORD";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);
      header("Access-Control-Allow-Origin: *");
      echo json_encode($resultado);
    } else {

      $sql = "SELECT SL.ID AS ID,SL.LOCATION_ID,SCH.HOUR_12,SCH.HOUR,SCH.ORD FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SCH ON SCH.SCHEDULE_ID=SL.SCHEDULE_ID WHERE LOCATION_ID=" . $location . " AND SL.MAX>0 AND ID NOT IN (SELECT SCHEDULE_LOCATION FROM RESERVATION WHERE DATE='" . $date . "' AND LOCATION_ID=" . $location . ") ORDER BY SCH.ORD ";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado1 = $gsent->fetchAll(PDO::FETCH_OBJ);

      $sql = "SELECT SC.ID,SC.LOCATION_ID,SCH.HOUR_12,SCH.HOUR,SCH.ORD FROM RESERVATION RE  INNER JOIN SCHEDULE_LOCATION SC ON RE.SCHEDULE_LOCATION=SC.ID INNER JOIN SCHEDULE SCH ON SCH.SCHEDULE_ID=SC.SCHEDULE_ID where  RE.DATE='" . $date . "' AND RE.LOCATION_ID=" . $location . " GROUP BY RE.SCHEDULE_LOCATION HAVING COUNT(*)<(SELECT MAX FROM SCHEDULE_LOCATION WHERE ID=RE.SCHEDULE_LOCATION)  ORDER BY SCH.ORD";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      //if(count($resultado2)>0)
      $resultado = array_merge($resultado1, $resultado2);
      //echo $sql;
      header("Access-Control-Allow-Origin: *");
      $db = null;
      echo json_encode($resultado);
    }
  } catch (PDOException $e) {

    echo $e;
  }
});

$app->get('/getScheduleUser/{user}', function (Request $request, Response $response, array $args) {

  //  echo $request->getParsedBody();
  $user = $args['user'];

  //echo $location;
  //echo $date;
  try {

    $db = new db();

    $db = $db->connect();
    // $sql="SELECT RES.RESERVATION_ID,LOC.LOCATION_ID,LOC.LOCATION_NAME,RES.DATE,SC.HOUR FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN SCHEDULE_LOCATION SCH ON SCH.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SCH.SCHEDULE_ID  WHERE DATE>=NOW() AND RES.STATUS=1 AND RES.CLIENT_ID='".$user."'";
    $sql = "SELECT RES.RESERVATION_ID,LOC.LOCATION_ID,LOC.LOCATION_NAME,RES.DATE,SC.HOUR FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN SCHEDULE_LOCATION SCH ON SCH.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SCH.SCHEDULE_ID  WHERE  RES.STATUS=1 AND RES.CLIENT_ID='" . $user . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    header("Access-Control-Allow-Origin: *");
    echo json_encode($resultado);
  } catch (PDOException $e) {

    echo $e;
  }
});

$app->get('/getScheduleUserTest/{user}', function (Request $request, Response $response, array $args) {

  //  echo $request->getParsedBody();
  $user = $args['user'];
  $sql = "SELECT ";
  $sql .= "RES.RESERVATION_ID,";
  $sql .= "RES.SERVICE_TYPE,";
  $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
  $sql .= "SCHE.HOUR,";
  $sql .= "US.USER_NAME AS RETAILER,";
  $sql .= "LO.LOCATION_NAME AS LOCATION,";
  $sql .= "LO.LOCATION_ID,";
  $sql .= "SER.SERVICE_NAME,";
  $sql .= "SER.DESCRIPTION,";
  $sql .= "US2.USER_NAME AS EMPLOYEE,";
  $sql .= "PE.NAME AS EMPLOYEE_NAME,";
  $sql .= "PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
  $sql .= "PE3.NAME AS CUSTOMER_NAME,";
  $sql .= "PE3.LAST_NAME AS CUSTOMER_LAST_NAME,";
  $sql .= "PE3.PHONE,";
  $sql .= "PE3.EMAIL,";
  $sql .= "CM.MAKE_NAME,";
  $sql .= "CMD.MODEL_NAME,";
  $sql .= "VD.YEAR,";
  $sql .= "VD.COLOR,";
  $sql .= "VD.PLATE,";
  $sql .= "LS.PRICE,";
  $sql .= "RES.TAX,";
  $sql .= "RES.TOTAL,";
  $sql .= "RES.TIP,";
  $sql .= "RES.STATUS,";
  $sql .= "RES.ADDRESS,";
  $sql .= "RES.LAT,";
  $sql .= "RES.LNG";
  $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
  $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
  $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
  $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
  $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
  $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
  $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
  $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
  $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
  $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
  $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
  $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.CLIENT_ID='" . $user . "' AND RES.SERVICE_TYPE=1 ORDER BY RES.DATE DESC";

  //echo $location;
  //echo $date;
  try {

    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as $valor) {
      //echo json_encode($valor);
      //echo "\n\n";
      //echo "\n";
      //echo $valor->RESERVATION_ID;
      $sql = "SELECT AD.ADDITIONAL_ID,ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor->RESERVATION_ID;

      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      $additionals = "";
      $sum = 0.0;
      foreach ($resultado2 as &$valor2) {
        //$additionals.=$valor2[0].", ";
        $sum += (float) $valor2->PRICE;
      }
      $additionals = ["ADDITIONALS" => $resultado2, "TOTAL_ADDITIONAL" => $sum];
      $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
      $cont++;
      //$resultado[$cont]+=['TOTAL_ADDITIONALS'=>$sum];
      /*  if($cont==count($resultado)){
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"}';
              }else{
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"},';
              }*/
    }

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($resultado);
  } catch (PDOException $e) {

    echo $e;
  }
});

$app->get('/getScheduleUserRide/{user}', function (Request $request, Response $response, array $args) {

  //  echo $request->getParsedBody();
  $user = $args['user'];
  $sql = "SELECT ";
  $sql .= "RES.RESERVATION_ID,";
  $sql .= "RES.SERVICE_TYPE,";
  $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
  $sql .= "SCHE.HOUR,";
  $sql .= "US.USER_NAME AS RETAILER,";
  $sql .= "LO.LOCATION_NAME AS LOCATION,";
  $sql .= "LO.LOCATION_ID,";
  $sql .= "SER.SERVICE_NAME,";
  $sql .= "SER.DESCRIPTION,";
  $sql .= "US2.USER_NAME AS EMPLOYEE,";
  $sql .= "PE.NAME AS EMPLOYEE_NAME,";
  $sql .= "PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
  $sql .= "PE3.NAME AS CUSTOMER_NAME,";
  $sql .= "PE3.LAST_NAME AS CUSTOMER_LAST_NAME,";
  $sql .= "PE3.PHONE,";
  $sql .= "PE3.EMAIL,";
  $sql .= "CM.MAKE_NAME,";
  $sql .= "CMD.MODEL_NAME,";
  $sql .= "VD.YEAR,";
  $sql .= "VD.COLOR,";
  $sql .= "VD.PLATE,";
  $sql .= "LS.PRICE,";
  $sql .= "RES.TAX,";
  $sql .= "RES.TOTAL,";
  $sql .= "RES.TIP,";
  $sql .= "RES.STATUS,";
  $sql .= "RES.ADDRESS,";
  $sql .= "RES.LAT,";
  $sql .= "RES.LNG";
  $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
  $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
  $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
  $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
  $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
  $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
  $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
  $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
  $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
  $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
  $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
  $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.CLIENT_ID='" . $user . "' AND RES.SERVICE_TYPE=2 ORDER BY RES.DATE DESC";

  //echo $location;
  //echo $date;
  try {

    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as $valor) {
      //echo json_encode($valor);
      //echo "\n\n";
      //echo "\n";
      //echo $valor->RESERVATION_ID;
      $sql = "SELECT AD.ADDITIONAL_ID,ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor->RESERVATION_ID;

      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      $additionals = "";
      $sum = 0.0;
      foreach ($resultado2 as &$valor2) {
        //$additionals.=$valor2[0].", ";
        $sum += (float) $valor2->PRICE;
      }
      $additionals = ["ADDITIONALS" => $resultado2, "TOTAL_ADDITIONAL" => $sum];
      $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
      $cont++;
      //$resultado[$cont]+=['TOTAL_ADDITIONALS'=>$sum];
      /*  if($cont==count($resultado)){
                  $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                  $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"}';
                }else{
                  $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                  $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"},';
                }*/
    }

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($resultado);
  } catch (PDOException $e) {

    echo $e;
  }
});

$app->post('/sendMail', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $mail = "We have finished wash your car";
  //Titulo
  $titulo = "EZCWash service";
  //cabecera
  $headers = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
  //dirección del remitente
  $headers .= "From: EZCWas uncorreo@gmail.com\r\n";
  $bool = mail($data['email'], $titulo, $mail, $headers);
});
$app->post('/addPerson', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data['lastName']);
  //print_r($data['firstName']);

  header("Access-Control-Allow-Origin: *");
  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {


    $db = new db();

    $db = $db->connect();
    $sql = "SELECT MAX(PERSON_ID) AS NUM FROM PERSON";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $personID = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($personID["0"]->NUM) == '')
      $personID = 1;
    else
      $personID = (($personID["0"]->NUM) + 1);
    //echo $personID;
    //  return;
    $sql = "INSERT INTO PERSON(PERSON_ID,NAME,LAST_NAME,EMAIL,PHONE) VALUES (:id,:firstName,:lastName,:email,:phone)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $personID);
    $stmt->bindParam("firstName", $data['firstName']);
    $stmt->bindParam("lastName", $data['lastName']);
    $stmt->bindParam("email", $data['email']);
    $stmt->bindParam("phone", $data['phone']);

    $stmt->execute();

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo '{"PERSON_ID":' . $personID . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//UPDATE RESERVATION
$app->post('/updateReservation', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE RESERVATION SET DATE='" . $dat['date'] . "', SCHEDULE_LOCATION='" . $dat['time'] . "' WHERE RESERVATION_ID='" . $dat['reservation'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $email = sendClientReschedule($dat['reservation']);
    $email2 = sendAdminData($dat['reservation']);

    $db = null;
    echo '{"error":"0","reservation":' . $sql . ',"email":' . $email . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//UPDATE RESERVATION
$app->get('/sendRetailerMail/{reservation}', function (Request $request, Response $response, array $args) {
  $reservation = $args['reservation'];
  try {

    $email = sendAdminData2($reservation);

    echo '{"error":"0","reservation":' . $reservation . ',"email":' . $email . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//CHANGE PERSON DATA
$app->post('/changePersonData', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE PERSON SET NAME='" . $dat['firstName'] . "', LAST_NAME='" . $dat['lastName'] . "', EMAIL='" . $dat['email'] . "', PHONE='" . $dat['phone'] . "' WHERE PERSON_ID=" . $dat['personId'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//TAKE
$app->post('/take', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();
    $sql = "SELECT STATUS FROM RESERVATION WHERE RESERVATION_ID='" . $dat['reservation'] . "'";

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->STATUS) != 1) {
      echo '{"error":{"text":"This reservation has already been taken"}}';
      return;
    }
    //echo $veh;

    $sql = "UPDATE RESERVATION SET STATUS=2,EMPLOYEE=" . $dat['employee'] . " WHERE RESERVATION_ID=" . $dat['reservation'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//done
$app->post('/done', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();

    $sql = "UPDATE RESERVATION SET STATUS=3 WHERE RESERVATION_ID=" . $dat['reservation'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $sql = "SELECT TRANSACCION,CLIENT_ID,SERVICE_TYPE FROM RESERVATION WHERE RESERVATION_ID='" . $dat['reservation'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);
    $trans = $exist["0"]->TRANSACCION;
    $client = $exist["0"]->CLIENT_ID;
    $type = $exist["0"]->SERVICE_TYPE;

    $sql = "SELECT MOBILE_TOKEN FROM USER WHERE USER_ID=" . $client;
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $result = $gsent->fetchAll(PDO::FETCH_OBJ);


    //AGREGANDO NOTIFICACION
    $status = 1;
    $sql = "INSERT INTO NOTIFY(USER_FK,RESERVATION_FK,TYPE,STATUS) VALUES (:user,:res,:type,:status)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("user", $client);
    $stmt->bindParam("res", $dat['reservation']);
    $stmt->bindParam("type", $type);
    $stmt->bindParam("status", $status);
    $stmt->execute();

    $token = $result["0"]->MOBILE_TOKEN;
    if ($token != '') {
      if ($type == 2 || $type == '2') {
        sendPushTip2($token, $dat['reservation']);
      } else {
        sendPushTip($token, $dat['reservation']);
      }
    }

    $db = null;
    $email3 = sendCompleted($dat['reservation'], $trans);
    echo '{"error":"0","email":"' . $email3 . '","token":"' . $token . '","client":"' . $client . '"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//CHANGE PASSWORD
$app->post('/changePass', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($dat[0]);

  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {
    $db = new db();
    $db = $db->connect();
    $sql = "SELECT COUNT(*) AS NUM FROM USER WHERE PASS='" . $dat['old_pass'] . "' AND USER_ID='" . $dat['user'] . "'";

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->NUM) == 0) {
      echo '{"error":{"text":"Wrong password"}}';
      return;
    }
    //echo $veh;

    $sql = "UPDATE USER SET PASS='" . $dat['new_pass'] . "' WHERE USER_ID=" . $dat['user'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":"0"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ANADIR UN DETALLE
$app->post('/addDetail', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  try {


    $db = new db();

    $db = $db->connect();
    $sql = "SELECT MAX(VHE_DETAIL) AS NUM FROM VEHICLE_DETAIL";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $vehID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($vehID["0"]->NUM) == '')
      $vehID = 1;
    else
      $vehID = (($vehID["0"]->NUM) + 1);
    //echo $veh;

    $sql = "INSERT INTO VEHICLE_DETAIL(VHE_DETAIL,PLATE,COLOR,MAKE,MODEL,YEAR) VALUES (:id,:plate,:color,:make,:model,:year)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $vehID);
    $stmt->bindParam("plate", $data['plate']);
    $stmt->bindParam("color", $data['color']);
    $stmt->bindParam("make", $data['make']);
    $stmt->bindParam("model", $data['model']);
    $stmt->bindParam("year", $data['year']);
    $stmt->execute();

    $db = null;
    echo '{"VEHICLE_DETAIL":' . $vehID . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ANDADIR UNA RESERVACION
$app->post('/addReservationNew', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  try {
    $db = new db();

    $db = $db->connect();
    $sql = "SELECT MAX(RESERVATION_ID) AS NUM FROM RESERVATION";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($resID["0"]->NUM) == '')
      $resID = 1;
    else
      $resID = (($resID["0"]->NUM) + 1);
    //echo $veh;
    $status = 1;
    $sql = "INSERT INTO RESERVATION(RESERVATION_ID,SCHEDULE_LOCATION,DATE,STATUS,VEHICLE_DETAIL,LOCATION_ID,CLIENT_ID,LS_ID,TOTAL,TAX,TRANSACCION,SERVICE_TYPE,LAT,LNG,ADDRESS,PROMO_ID,PROMO,PROMO_DISCOUNT,PROMO_DISCOUNT_VALUE) VALUES (:id,:schedule,:date,:status,:vehDetail,:location,:user,:ls_id,:total,:tax,:transaccion,:serviceType,:lat,:lng,:address,:promoId,:promoCode,:discount,:discountValue)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $resID);
    $stmt->bindParam("schedule", $data['schedule']);
    $stmt->bindParam("date", $data['date']);
    $stmt->bindParam("status", $status);
    $stmt->bindParam("vehDetail", $data['vehDetail']);
    $stmt->bindParam("location", $data['location']);
    $stmt->bindParam("user", $data['user']);
    $stmt->bindParam("ls_id", $data['ls_id']);
    $stmt->bindParam("total", $data['total']);
    $stmt->bindParam("tax", $data['tax']);
    $stmt->bindParam("transaccion", $data['transaccion']);
    $stmt->bindParam("serviceType", $data['serviceType']);
    $stmt->bindParam("lat", $data['lat']);
    $stmt->bindParam("lng", $data['lng']);
    $stmt->bindParam("address", $data['address']);
    $stmt->bindParam("promoId", $data['promoId']);
    $stmt->bindParam("promoCode", $data['promoCode']);
    $stmt->bindParam("discount", $data['discount']);
    $stmt->bindParam("discountValue", $data['discountValue']);

    $stmt->execute();
    //USED ​​PROMO REGISTRATION
    if ($data['promoId'] == 0 || $data['promoId'] == '0') { } else {
      $sql = "INSERT INTO PROMO_USER(USER_ID,CODE) VALUES(:user,:promo)";
      $stmt = $db->prepare($sql);
      $stmt->bindParam("user", $data['user']);
      $stmt->bindParam("promo", $data['promoId']);
      $stmt->execute();
    }
    //UPDATE CARD INFO
    $sql = "SELECT PERSON_ID FROM USER WHERE USER_ID=" . $data['user'];
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $per = $gsent->fetchAll(PDO::FETCH_OBJ);
    $perID = $per["0"]->PERSON_ID;
    $sql = "UPDATE PERSON SET NUMC='" . $data['cNumber'] . "',CC_TYPE='" . $data['cType'] . "',CNAME='" . $data['cName'] . "' WHERE PERSON_ID='" . $perID . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $email = sendClientData($resID, $data['additionals']);
    $email2 = sendAdminData($resID);

    $db = null;
    echo '{"RESERVATION_ID":' . $resID . ',"EMAIL":"' . $email . '","EMAIL2":"' . $email2 . '"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ANDADIR UNA RESERVACION
$app->post('/addReservation', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data['lastName']);
  //print_r($data['firstName']);


  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {


    $db = new db();

    $db = $db->connect();
    $sql = "SELECT MAX(RESERVATION_ID) AS NUM FROM RESERVATION";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($resID["0"]->NUM) == '')
      $resID = 1;
    else
      $resID = (($resID["0"]->NUM) + 1);
    //echo $veh;
    $status = 1;
    $sql = "INSERT INTO RESERVATION(RESERVATION_ID,SCHEDULE_LOCATION,DATE,STATUS,VEHICLE_DETAIL,LOCATION_ID,CLIENT_ID,LS_ID,TOTAL,TAX,TRANSACCION,SERVICE_TYPE,LAT,LNG,ADDRESS) VALUES (:id,:schedule,:date,:status,:vehDetail,:location,:user,:ls_id,:total,:tax,:transaccion,:serviceType,:lat,:lng,:address)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $resID);
    $stmt->bindParam("schedule", $data['schedule']);
    $stmt->bindParam("date", $data['date']);
    $stmt->bindParam("status", $status);
    $stmt->bindParam("vehDetail", $data['vehDetail']);
    $stmt->bindParam("location", $data['location']);
    $stmt->bindParam("user", $data['user']);
    $stmt->bindParam("ls_id", $data['ls_id']);
    $stmt->bindParam("total", $data['total']);
    $stmt->bindParam("tax", $data['tax']);
    $stmt->bindParam("transaccion", $data['transaccion']);
    $stmt->bindParam("serviceType", $data['serviceType']);
    $stmt->bindParam("lat", $data['lat']);
    $stmt->bindParam("lng", $data['lng']);
    $stmt->bindParam("address", $data['address']);
    $stmt->execute();

    $sql = "SELECT PERSON_ID FROM USER WHERE USER_ID=" . $data['user'];
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $per = $gsent->fetchAll(PDO::FETCH_OBJ);
    $perID = $per["0"]->PERSON_ID;
    $sql = "UPDATE PERSON SET NUMC='" . $data['cNumber'] . "',CC_TYPE='" . $data['cType'] . "',CNAME='" . $data['cName'] . "' WHERE PERSON_ID='" . $perID . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $email = sendClientData($resID, $data['additionals']);
    $email2 = sendAdminData($resID);

    $db = null;
    echo '{"RESERVATION_ID":' . $resID . ',"EMAIL":"' . $email . '","EMAIL2":"' . $email2 . '"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/updateNewAdditional', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();
    //echo $veh;

    $sql = "UPDATE ADDITIONAL SET ADDITIONAL_NAME='" . $data['ADDITIONAL_NAME'] . "',PRICE='" . $data['PRICE'] . "' WHERE ADDITIONAL_ID=" . $data['ADDITIONAL_ID'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/updateStatusAdditional', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();
    //echo $veh;

    $sql = "UPDATE ADDITIONAL SET STATUS='" . $data['STATUS'] . "' WHERE ADDITIONAL_ID=" . $data['ADDITIONAL_ID'];
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/addNewAdditional', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {


    $db = new db();

    $db = $db->connect();
    //echo $veh;

    $sql = "INSERT INTO ADDITIONAL(ADDITIONAL_NAME,PRICE,STATUS) VALUES (:ADDITIONAL_NAME,:PRICE,1)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("ADDITIONAL_NAME", $data['ADDITIONAL_NAME']);
    $stmt->bindParam("PRICE", $data['PRICE']);

    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->post('/addAdditionalServices', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data['lastName']);
  //print_r($data['firstName']);


  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {


    $db = new db();

    $db = $db->connect();
    //echo $veh;

    $sql = "INSERT INTO RESERVATION_ADDITIONAL_SERVICE(RESERVATION_ID,ADDITIONAL_ID) VALUES (:reservation,:additional)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("reservation", $data['reservation']);
    $stmt->bindParam("additional", $data['additional']);

    $stmt->execute();

    $db = null;
    echo '{"status":"ok","reservation":' . $data['reservation'] . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


//admin

$app->get('/getReservations/{location}/{retailer}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $ret = $args['retailer'];
  $sql = "SELECT RE.RESERVATION_ID,RE.STATUS,LO.LOCATION_ID,LO.LOCATION_NAME,LO.RETAILER_ID,LO.ZIP,LO.TAX,RE.DATE,SCH.HOUR,PE.NAME,PE.LAST_NAME,PE.EMAIL,";
  $sql .= "PE.PHONE,CM.MAKE_NAME,CMO.MODEL_NAME,VH.YEAR,VH.COLOR,VH.PLATE,RE.EMPLOYEE,RE.LAT,RE.LNG,RE.ADDRESS,RE.SERVICE_TYPE FROM RESERVATION RE LEFT JOIN LOCATION LO ON LO.LOCATION_ID=RE.LOCATION_ID LEFT JOIN ";
  $sql .= " SCHEDULE_LOCATION  SL ON SL.ID=RE.SCHEDULE_LOCATION LEFT JOIN SCHEDULE SCH ON ";
  $sql .= " SCH.SCHEDULE_ID=SL.SCHEDULE_ID LEFT JOIN USER US ON US.USER_ID=RE.CLIENT_ID LEFT JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID  LEFT ";
  $sql .= "JOIN VEHICLE_DETAIL VH ON VH.VHE_DETAIL=RE.VEHICLE_DETAIL LEFT JOIN CAR_MAKE CM ON ";
  $sql .= "CM.MAKE_ID=VH.MAKE LEFT JOIN CAR_MODEL CMO ON CMO.MODEL_ID=VH.MODEL WHERE  MONTH(RE.DATE)>=MONTH(NOW()) AND YEAR(RE.DATE)=YEAR(NOW()) and LO.LOCATION_ID=" . $id . " AND LO.RETAILER_ID=" . $ret;
  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $ret = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    echo json_encode($ret);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllReservationsByLocation/{opc}/{from}/{to}/{location}', function (Request $request, Response $response, array $args) {
  $from = $args['from'];
  $to = $args['to'];
  $opc = $args['op'];
  $location = $args['location'];

  $sql = "SELECT ";
  $sql .= "RES.RESERVATION_ID,";
  $sql .= "RES.SERVICE_TYPE,";
  $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
  $sql .= "SCHE.HOUR,";
  $sql .= "US.USER_NAME AS RETAILER,";
  $sql .= "LO.LOCATION_NAME AS LOCATION,";
  $sql .= "SER.SERVICE_NAME,";
  //  $sql.="US2.USER_NAME AS EMPLOYEE,";
  //  $sql.="PE.NAME AS EMPLOYEE_NAME,";
  //  $sql.="PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
  $sql .= "PE3.NAME AS CUSTOMER_NAME,";
  $sql .= "PE3.LAST_NAME AS CUSTOMER_LAST_NAME,";
  $sql .= "PE3.PHONE,";
  $sql .= "PE3.EMAIL,";
  $sql .= "CM.MAKE_NAME,";
  $sql .= "CMD.MODEL_NAME,";
  $sql .= "VD.YEAR,";
  $sql .= "VD.COLOR,";
  $sql .= "VD.PLATE,";
  $sql .= "LS.PRICE,";
  $sql .= "RES.TAX,";
  $sql .= "RES.TOTAL,";
  $sql .= "RES.TIP,";
  $sql .= "RES.STATUS,";
  $sql .= "RES.ADDRESS";
  $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
  $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
  //$sql.=" INNER JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
  $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  //$sql.=" INNER JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
  $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
  $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
  $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
  $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
  $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
  $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
  $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
  $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE LO.LOCATION_ID=" . $location;

  if ($opc == 2) {
    $sql .= " AND RES.DATE>='" . $from . "'";
    $sql .= " AND RES.DATE<='" . $to . "'";
  }



  error_log($sql);
  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as $valor) {
      //echo json_encode($valor);
      //echo "\n\n";
      //echo "\n";
      //echo $valor->RESERVATION_ID;
      $sql = "SELECT AD.ADDITIONAL_ID,ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor->RESERVATION_ID;

      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      $additionals = "";
      $sum = 0.0;
      foreach ($resultado2 as &$valor2) {
        //$additionals.=$valor2[0].", ";
        $sum += (float) $valor2->PRICE;
      }
      $additionals = ["ADDITIONALS" => $resultado2, "TOTAL_ADDITIONAL" => $sum];
      $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
      $cont++;
      //$resultado[$cont]+=['TOTAL_ADDITIONALS'=>$sum];
      /*  if($cont==count($resultado)){
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"}';
              }else{
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"},';
              }*/
    }

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($resultado);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
function utf8ize($d)
{
  if (is_array($d)) {
    foreach ($d as $k => $v) {
      $d[$k] = utf8ize($v);
    }
  } else if (is_string($d)) {
    return utf8_encode($d);
  }
  return $d;
}

$app->get('/getAllReservations/{opc}/{from}/{to}/{service}', function (Request $request, Response $response, array $args) {
  $from = $args['from'];
  $to = $args['to'];
  $opc = $args['opc'];
  $service = $args['service'];

  $sql = "SELECT ";
  $sql .= "RES.RESERVATION_ID,";
  $sql .= "RES.SERVICE_TYPE,";
  $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
  $sql .= "RES.STATUS,";
  $sql .= "SCHE.HOUR,";
  $sql .= "US.USER_NAME AS RETAILER,";
  $sql .= "LO.LOCATION_NAME AS LOCATION,";
  $sql .= "SER.SERVICE_NAME,";
  //$sql.="US2.USER_NAME AS EMPLOYEE,";
  $sql .= "RES.EMPLOYEE,";
  //$sql.="PE.NAME AS EMPLOYEE_NAME,";
  //$sql.="PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
  $sql .= "PE3.NAME AS CUSTOMER_NAME,";
  $sql .= "PE3.LAST_NAME AS CUSTOMER_LAST_NAME,";
  $sql .= "PE3.PHONE,";
  $sql .= "PE3.EMAIL,";
  $sql .= "CM.MAKE_NAME,";
  $sql .= "CMD.MODEL_NAME,";
  $sql .= "VD.YEAR,";
  $sql .= "VD.COLOR,";
  $sql .= "VD.PLATE,";
  $sql .= "LS.PRICE,";
  $sql .= "RES.TAX,";
  $sql .= "RES.TOTAL,";
  $sql .= "RES.TIP,";
  $sql .= "RES.ADDRESS";
  $sql .= " FROM RESERVATION RES LEFT JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
  $sql .= " LEFT JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
  //$sql.=" LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
  $sql .= " LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  //$sql.=" LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
  $sql .= " LEFT JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
  $sql .= " LEFT JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
  $sql .= " LEFT JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
  $sql .= " LEFT JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
  $sql .= " LEFT JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
  $sql .= " LEFT JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
  $sql .= " LEFT JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
  $sql .= " LEFT JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.SERVICE_TYPE=" . $service;

  if ($opc == 2) {
    $sql .= " AND RES.DATE>='" . $from . "'";
    $sql .= " AND RES.DATE<='" . $to . "'";
  }



  error_log($sql);
  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as $valor) {
      //echo json_encode($valor);
      //echo "\n\n";
      //echo "\n";
      //echo $valor->RESERVATION_ID;


      $sql = "SELECT AD.ADDITIONAL_ID,ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS LEFT JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor->RESERVATION_ID;


      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      $additionals = "";
      $sum = 0.0;
      foreach ($resultado2 as &$valor2) {
        //$additionals.=$valor2[0].", ";
        $sum += (float) $valor2->PRICE;
      }
      $additionals = ["ADDITIONALS" => $resultado2, "TOTAL_ADDITIONAL" => $sum];
      $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
      $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
      $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US LEFT JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;
      //error_log($sql);
      error_log($sql);
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);


      //$res="{'EMPLOYEE_USER':'".$resultado3->EMPLOYEE_USER."','EMPLOYEE_NAME':'".$resultado3->EMPLOYEE_NAME."','EMPLOYEE_LAST_NAME':'".$resultado3->EMPLOYEE_LAST_NAME."'}";
      $res = [];
      error_log("resultado3" . count($resultado3));
      if (count($resultado3) > 0) {

        $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LAST_NAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
      } else {
        $res = array("EMPLOYEE_USER" => "none", "EMPLOYEE_NAME" => "none", "EMPLOYEE_LAST_NAME" => "none");
      }
      //$res=json_decode($res,true);
      $resultado[$cont] = array_merge((array) $resultado[$cont], $res);
      $cont++;
      //$resultado[$cont]+=['TOTAL_ADDITIONALS'=>$sum];
      /*  if($cont==count($resultado)){
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"}';
              }else{
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"},';
              }*/
    }

    error_log(print_r($resultado, TRUE));
    error_log(json_encode(utf8ize($resultado)));
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo json_encode(utf8ize($resultado));
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllReservations2/{opc}/{from}/{to}/{service}', function (Request $request, Response $response, array $args) {
  $from = $args['from'];
  $to = $args['to'];
  $opc = $args['opc'];
  $service = $args['service'];

  $sql = "SELECT ";
  $sql .= "RES.RESERVATION_ID,";
  $sql .= "RES.SERVICE_TYPE,";
  $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
  $sql .= "RES.STATUS,";
  $sql .= "SCHE.HOUR,";
  $sql .= "US.USER_NAME AS RETAILER,";
  $sql .= "LO.LOCATION_NAME AS LOCATION,";
  $sql .= "SER.SERVICE_NAME,";
  //$sql.="US2.USER_NAME AS EMPLOYEE,";
  $sql .= "RES.EMPLOYEE,";
  //$sql.="PE.NAME AS EMPLOYEE_NAME,";
  //$sql.="PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
  $sql .= "PE3.NAME AS CUSTOMER_NAME,";
  $sql .= "PE3.LAST_NAME AS CUSTOMER_LAST_NAME,";
  $sql .= "PE3.PHONE,";
  $sql .= "PE3.EMAIL,";
  $sql .= "CM.MAKE_NAME,";
  $sql .= "CMD.MODEL_NAME,";
  $sql .= "VD.YEAR,";
  $sql .= "VD.COLOR,";
  $sql .= "VD.PLATE,";
  $sql .= "LS.PRICE,";
  $sql .= "RES.TAX,";
  $sql .= "RES.TOTAL,";
  $sql .= "RES.TIP,";
  $sql .= "RES.ADDRESS";
  $sql .= " FROM RESERVATION RES LEFT JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
  $sql .= " LEFT JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
  //$sql.=" LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
  $sql .= " LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  //$sql.=" LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
  $sql .= " LEFT JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
  $sql .= " LEFT JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
  $sql .= " LEFT JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
  $sql .= " LEFT JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
  $sql .= " LEFT JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
  $sql .= " LEFT JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
  $sql .= " LEFT JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
  $sql .= " LEFT JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.SERVICE_TYPE=" . $service;

  if ($opc == 2) {
    $sql .= " AND RES.DATE>='" . $from . "'";
    $sql .= " AND RES.DATE<='" . $to . "'";
  }



  error_log($sql);
  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

    $cont = 0;
    foreach ($resultado as $valor) {
      //echo json_encode($valor);
      //echo "\n\n";
      //echo "\n";
      //echo $valor->RESERVATION_ID;


      $sql = "SELECT AD.ADDITIONAL_ID,ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS LEFT JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor->RESERVATION_ID;
      error_log($sql);

      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
      $additionals = "";
      $sum = 0.0;
      foreach ($resultado2 as &$valor2) {
        //$additionals.=$valor2[0].", ";
        $sum += (float) $valor2->PRICE;
      }
      $additionals = ["ADDITIONALS" => $resultado2, "TOTAL_ADDITIONAL" => $sum];
      $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
      $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
      $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US LEFT JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;
      //error_log($sql);
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);


      if (count($resultado3) > 0) {

        $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LAST_NAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
      } else {
        $res = array("EMPLOYEE_USER" => "none", "EMPLOYEE_NAME" => "none", "EMPLOYEE_LAST_NAME" => "none");
      }
      //$res=json_decode($res,true);
      $resultado[$cont] = array_merge((array) $resultado[$cont], $res);
      $cont++;
      //$resultado[$cont]+=['TOTAL_ADDITIONALS'=>$sum];
      /*  if($cont==count($resultado)){
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"}';
              }else{
                $json.='{"ID":'.$valor[0].',"RETAILER":"'.$valor[1].'","ZIP":"'.$valor[2].'","LOCATION":"'.$valor[3].'","EMPLOYEE":"'.$valor[4].'"';
                $json.=',"CLIENT_NAME":"'.$valor[5].'","SERVICE_NAME":"'.$valor[6].'","DATE":"'.$valor[7].'","HOUR":"'.$valor[8].'","IMPORT":"'.$valor[9].'","ADDITIONALS":"'.$additionals.'","TOTAL_ADDITIONALS":"'.$sum.'","TAX":"'.$valor[10].'"},';
              }*/
    }

    error_log(print_r($resultado, TRUE));
    error_log(json_encode(utf8ize($resultado)));
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo json_encode(utf8ize($resultado));
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
//PARA EL CALENDARIO DE LOS EMPLEADOS
$app->get('/getEmpReservations/{loc}', function (Request $request, Response $response, array $args) {
  $location = $args['loc'];
  $sql = "SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,RES.DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) AS VEHICLE,VEH.PLATE,VEH.COLOR,RES.LAT,RES.LNG,RES.ADDRESS,RES.SERVICE_TYPE  ";
  $sql .= " FROM RESERVATION RES LEFT JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL LEFT JOIN CAR_MAKE ";
  $sql .= " CARME ON CARME.MAKE_ID=VEH.MAKE LEFT JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL LEFT JOIN LOCATION LO ON ";
  $sql .= " LO.LOCATION_ID=RES.LOCATION_ID LEFT JOIN USER US ON US.USER_ID=LO.RETAILER_ID LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
  $sql .= " LEFT JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID LEFT JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID LEFT JOIN SERVICE SER";
  $sql .= " ON SER.SERVICE_ID=LS.SERVICE_ID LEFT JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION LEFT JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID";
  $sql .= " WHERE  MONTH(RES.DATE)>=MONTH(NOW()) AND YEAR(RES.DATE)=YEAR(NOW()) AND RES.STATUS=1 AND LO.LOCATION_ID=" . $location . "";


  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      $sql = "SELECT ADDITIONAL_NAME FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor[0];
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll();
      $additionals = "";

      foreach ($resultado2 as &$valor2) {
        $additionals .= $valor2[0] . ", ";
      }

      if ($cont == count($resultado)) {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '"}';
      } else {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '"},';
      }

      $cont++;
    }
    $json .= "]";
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo $json;
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

//OBTIENE LA LISTA DE PENDIENTES PARA LOS LAVA COCHES
//PARA EL CALENDARIO DE LOS EMPLEADOS EMPLEADOS
$app->get('/getEmpWashlist/{emp}', function (Request $request, Response $response, array $args) {
  $emp = $args['emp'];
  $sql = "SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,RES.DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) AS VEHICLE,VEH.PLATE,VEH.COLOR,RES.LAT,RES.LNG,RES.ADDRESS,RES.SERVICE_TYPE  FROM RESERVATION RES INNER JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL INNER JOIN CAR_MAKE CARME ON CARME.MAKE_ID=VEH.MAKE INNER JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.STATUS=2 AND RES.EMPLOYEE=" . $emp;


  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      $sql = "SELECT ADDITIONAL_NAME FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor[0];
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll();
      $additionals = "";

      foreach ($resultado2 as &$valor2) {
        $additionals .= $valor2[0] . ", ";
      }

      if ($cont == count($resultado)) {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '"}';
      } else {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '"},';
      }

      $cont++;
    }
    $json .= "]";
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo $json;
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


//OBTIENE LA LISTA DE PENDIENTES PARA LOS LAVA COCHES
//PARA EL CALENDARIO DE LOS EMPLEADOS EMPLEADOS
$app->get('/getHistoryWashlist/{emp}', function (Request $request, Response $response, array $args) {
  $emp = $args['emp'];
  $sql = "SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,RES.DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) AS VEHICLE,VEH.PLATE,VEH.COLOR,RES.LAT,RES.LNG,RES.ADDRESS,RES.SERVICE_TYPE,RES.TIP  FROM RESERVATION RES INNER JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL INNER JOIN CAR_MAKE CARME ON CARME.MAKE_ID=VEH.MAKE INNER JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.STATUS=3 AND RES.EMPLOYEE=" . $emp . " ORDER BY RES.DATE DESC LIMIT 7";


  try {


    $db = new db();

    $db = $db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();

    /* Obtener todas las filas restantes del conjunto de resultados */

    $resultado = $gsent->fetchAll();
    $json = "[";
    $cont = 1;
    foreach ($resultado as &$valor) {
      $sql = "SELECT ADDITIONAL_NAME FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=" . $valor[0];
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $resultado2 = $gsent->fetchAll();
      $additionals = "";

      foreach ($resultado2 as &$valor2) {
        $additionals .= $valor2[0] . ", ";
      }

      if ($cont == count($resultado)) {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '","TIP":"' . $valor[16] . '"}';
      } else {
        $json .= '{"ID":' . $valor[0] . ',"RETAILER":"' . $valor[1] . '","ZIP":"' . $valor[2] . '","LOCATION":"' . $valor[3] . '","CLIENT_NAME":"' . $valor[4] . '"';
        $json .= ',"CLIENT_EMAIL":"' . $valor[5] . '","SERVICE_NAME":"' . $valor[6] . '","DATE":"' . $valor[7] . '","HOUR":"' . $valor[8] . '","ADDITIONALS":"' . $additionals . '","VEHICLE":"' . $valor[9] . '","PLATE":"' . $valor[10] . '","COLOR":"' . $valor[11] . '","LAT":"' . $valor[12] . '","LNG":"' . $valor[13] . '","ADDRESS":"' . $valor[14] . '","SERVICE_TYPE":"' . $valor[15] . '","TIP":"' . $valor[16] . '"},';
      }

      $cont++;
    }
    $json .= "]";
    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo $json;
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

//OBTENER DIAS DE LA SEMANA
$app->get('/getDaysWeek', function (Request $request, Response $response, array $args) {
  $id = $args['make'];
  $sql = "select * FROM DAYS_WEEK";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
//SELECT P.PERSON_ID FROM RESERVATION R INNER JOIN USER U ON U.USER_ID=R.CLIENT_ID INNER JOIN PERSON P ON P.PERSON_ID=U.PERSON_ID WHERE R.RESERVATION_ID=23 AND R.TRANSACCION=123;
//TIP
$app->get('/tipData2/{res}/{tran}', function (Request $request, Response $response, array $args) {
  $res = $args['res']; //pass
  $tran = $args['tran']; //usu

  $sql = "SELECT P.PERSON_ID FROM RESERVATION R INNER JOIN USER U ON U.USER_ID=R.CLIENT_ID INNER JOIN PERSON P ON P.PERSON_ID=U.PERSON_ID WHERE R.RESERVATION_ID=" . $res . " AND R.TRANSACCION=" . $tran;

  try {

    $db = new db();

    $db = $db->connect();
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (count($exist) <= 0) {
      echo '{"error":{"text":"There was a probleme"}}';
      return;
    }
    $sql = "SELECT CC_TYPE,CNAME,NUMC FROM PERSON WHERE PERSON_ID='" . $exist["0"]->PERSON_ID . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($exist);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


$app->get('/makeTip/{res}/{tip}/{raiting}', function (Request $request, Response $response, array $args) {
  $res = $args['res']; //res
  $tip = $args['tip']; //tip
  $raiting = $args['raiting']; //tip

  $sql = "UPDATE RESERVATION SET TIP='" . $tip . "' WHERE RESERVATION_ID='" . $res . "'";

  try {
    $db = new db();
    $db = $db->connect();
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $sql = "SELECT LOCATION_ID FROM RESERVATION WHERE RESERVATION_ID='" . $res . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $id = $gsent->fetchAll(PDO::FETCH_OBJ);
    $location = $id["0"]->LOCATION_ID;

    $sql="INSERT INTO LOCATION_RATING(LOCATION_ID,RAITING,RESERVATION_FK) VALUES (:location,:raiting,:reservation)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("location", $location);
    $stmt->bindParam("raiting", $raiting);
    $stmt->bindParam("reservation", $res);

    $stmt->execute();

    $db = null;
    header("Access-Control-Allow-Origin: *");
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/tipData/{us}/{pas}', function (Request $request, Response $response, array $args) {
  $user = $args['us']; //pass
  $pass = $args['pas']; //usu

  $sql = "SELECT PERSON_ID FROM USER WHERE USER_NAME='" . $user . "' and PASS='" . $pass . "'";

  try {

    $db = new db();

    $db = $db->connect();
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (count($exist) <= 0) {
      echo '{"error":{"text":"Incorrect User"}}';
      return;
    }
    $sql = "SELECT NUMC FROM PERSON WHERE PERSON_ID='" . $exist["0"]->PERSON_ID . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($exist);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getWorkDays/{location}', function (Request $request, Response $response, array $args) {
  $id = $args['location'];
  $sql = "SELECT * FROM WORKDAYS WHERE LOCATION_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getEmployee/{emp}', function (Request $request, Response $response, array $args) {
  $id = $args['emp'];
  $sql = "SELECT * FROM EMPLOYEE WHERE EMPLOYEE_ID=" . $id;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/login/{userName}/{pass}', function (Request $request, Response $response, array $args) {
  $userName = $args['userName'];
  $pass = $args['pass'];

  $sql = "SELECT * FROM USER WHERE STATUS=1 AND USER_NAME='" . $userName . "' AND PASS='" . $pass . "' AND (USER_TYPE=1 OR USER_TYPE=2);";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    if (count($data) <= 0) {
      $data = array('error' => 'Invalid username or password');
    } else {
      $data['error'] = 0;
    }
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/loginEmployee/{userName}/{pass}', function (Request $request, Response $response, array $args) {
  $userName = $args['userName'];
  $pass = $args['pass'];

  $sql = "SELECT * FROM USER WHERE  STATUS=1 AND USER_NAME='" . $userName . "' AND PASS='" . $pass . "' AND (USER_TYPE=4 OR USER_TYPE=2) ;";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    if (count($data) <= 0) {
      $data = array('error' => 'Invalid username or password');
    } else {
      $data['error'] = 0;
    }
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/loginEmployeeMobile', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  $userName = $dat['userName'];
  $pass = $dat['pass'];
  $mobileToken = $dat['mobileToken'];

  $sql = "SELECT * FROM USER WHERE  STATUS=1 AND USER_NAME='" . $userName . "' AND PASS='" . $pass . "' AND USER_TYPE=4;";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (count($data) <= 0) {
      $data = array('error' => 'Invalid username or password');
    } else {
      $token = bin2hex(openssl_random_pseudo_bytes(8)); //generate a random token
      $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); //the expiration date will be in one hour from the current moment

      $sql = "UPDATE USER SET TOKEN='" . $token . "',TOKEN_EXPIRATE='" . $tokenExpiration . "',MOBILE_TOKEN='" . $mobileToken . "' WHERE USER_NAME='" . $userName . "'";
      $stmt = $db->prepare($sql);
      error_log($sql);
      $stmt->execute();
      $data[0]->TOKEN = $token;
      $data[0]->TOKEN_EXPIRATE = $tokenExpiration;
      $data[0]->error = 0;
    }
    $db = null;
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/loginClientMobile', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  $userName = $dat['userName'];
  $pass = $dat['pass'];
  $mobileToken = $dat['mobileToken'];

  $sql = "SELECT * FROM USER US INNER JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID  WHERE US.STATUS=1 AND (US.USER_NAME='" . $userName . "' OR PER.EMAIL='" . $userName . "')  AND PASS='" . $pass . "' AND USER_TYPE=3;";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (count($data) <= 0) {
      $data = array('error' => 'Invalid username or password');
    } else {

      $token = bin2hex(openssl_random_pseudo_bytes(8)); //generate a random token
      $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); //the expiration date will be in one hour from the current moment

      $sql = "UPDATE USER SET TOKEN='" . $token . "',TOKEN_EXPIRATE='" . $tokenExpiration . "',MOBILE_TOKEN='" . $mobileToken . "' WHERE USER_NAME='" . $userName . "'";
      $stmt = $db->prepare($sql);
      error_log($sql);
      $stmt->execute();
      $data[0]->TOKEN = $token;
      $data[0]->TOKEN_EXPIRATE = $tokenExpiration;
      $data[0]->error = 0;
    }
    $db = null;
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});


$app->get('/loginClient/{userName}/{pass}', function (Request $request, Response $response, array $args) {
  $userName = $args['userName'];
  $pass = $args['pass'];

  $sql = "SELECT * FROM USER WHERE STATUS=1 AND USER_NAME='" . $userName . "' AND PASS='" . $pass . "' AND USER_TYPE=3;";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    if (count($data) <= 0) {
      $data = array('error' => 'Invalid username or password');
    } else {
      $data['error'] = 0;
    }
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getUser/{userId}', function (Request $request, Response $response, array $args) {
  $userId = $args['userId'];

  $sql = "SELECT U.USER_ID,P.PERSON_ID,U.USER_NAME,U.USER_TYPE,P.NAME,P.LAST_NAME,P.EMAIL,P.PHONE,P.NUMC,P.CNAME,P.CC_TYPE FROM USER U LEFT JOIN PERSON P ON P.PERSON_ID=U.PERSON_ID WHERE U.USER_ID=" . $userId;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getUserToken/{token}', function (Request $request, Response $response, array $args) {
  $token = $args['token'];

  $sql = "SELECT RES.RESERVATION_ID,LOC.LOCATION_ID,LOC.LOCATION_NAME,RES.DATE,SC.HOUR,US.USER_ID,US.USER_NAME FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN SCHEDULE_LOCATION SCH ON SCH.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SCH.SCHEDULE_ID LEFT JOIN USER US ON US.USER_ID=RES.CLIENT_ID  WHERE  RES.STATUS=1 AND US.USER_NAME='" . $token . "'";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/deleteUserVehicle', function (Request $request, Response $response, array $args) {

  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "DELETE FROM USER_VEHICLES WHERE VEHICLE_DETAIL='" . $dat["DETAIL_ID"] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->get('/getUserVehicles/{userId}', function (Request $request, Response $response, array $args) {
  $userId = $args['userId'];

  $sql = "SELECT US.VEHICLE_DETAIL,CM.MAKE_NAME,MO.MODEL_NAME,VD.YEAR,VD.PLATE,VD.COLOR FROM USER_VEHICLES US INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=US.VEHICLE_DETAIL INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE INNER JOIN CAR_MODEL MO ON MO.MODEL_ID=VD.MODEL WHERE US.ID_USER=" . $userId;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getAllServices', function (Request $request, Response $response, array $args) {


  $sql = "SELECT * FROM SERVICE";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getEmployes/{retailer}', function (Request $request, Response $response, array $args) {
  $retailer = $args['retailer'];

  $sql = "SELECT US.USER_ID,US.USER_NAME,US.PASS,PE.NAME,PE.LAST_NAME,PE.EMAIL,PE.PHONE FROM EMPLOYEE EMP INNER JOIN USER US ON US.USER_ID=EMP.EMPLOYEE_ID INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_TYPE=4 AND US.STATUS=1 AND EMP.RETAILER_ID=" . $retailer;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllRetailers', function (Request $request, Response $response, array $args) {


  $sql = "SELECT *  FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_TYPE=2 AND STATUS=1";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/getAllUsers', function (Request $request, Response $response, array $args) {


  $sql = "SELECT *  FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.STATUS=1 AND USER_TYPE=3";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/updateRetailerData', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();

    $db = $db->connect();
    $sql = "SELECT EMAIL FROM PERSON WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->EMAIL) != $dat['email']) {

      $sql = "SELECT COUNT(*) AS NUM FROM PERSON WHERE EMAIL='" . $dat['email'] . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

      if (($exist["0"]->NUM) != 0) {
        echo '{"error":{"text":"Email is already exist"}}';
        return;
      }
    }
    $sql = "UPDATE USER SET PASS='" . $dat['pass'] . "' WHERE USER_ID='" . $dat['USER_ID'] . "'";
    $stmt = $db->prepare($sql);
    error_log($sql);
    $stmt->execute();

    $sql = "UPDATE PERSON SET NAME='" . $dat['firstName'] . "', LAST_NAME='" . $dat['lastName'] . "', EMAIL='" . $dat['email'] . "', PHONE='" . $dat['phone'] . "', COMPANY_NAME='" . $dat['companyName'] . "', OFFICE_PHONE1='" . $dat['phone1'] . "', OFFICE_PHONE2='" . $dat['phone2'] . "', FAX='" . $dat['fax'] . "', NO='" . $dat['no'] . "', STREET='" . $dat['street'] . "', CITY='" . $dat['city'] . "', STATE='" . $dat['state'] . "', ZIP='" . $dat['zip'] . "', COUNTRY='" . $dat['country'] . "' WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    error_log($sql);
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/updateUserStatus', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "UPDATE USER SET STATUS='0' WHERE USER_ID='" . $dat['USER_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    error_log($sql);
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/updateLocationStatus', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "UPDATE LOCATION SET STATUS='0' WHERE LOCATION_ID='" . $dat['LOCATION_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    error_log($sql);
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});


$app->post('/updateUserData', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();

    $db = $db->connect();
    $sql = "SELECT EMAIL FROM PERSON WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->EMAIL) != $dat['email']) {

      $sql = "SELECT COUNT(*) AS NUM FROM PERSON WHERE EMAIL='" . $dat['email'] . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

      if (($exist["0"]->NUM) != 0) {
        echo '{"error":{"text":"Email is already exist"}}';
        return;
      }
    }
    $sql = "UPDATE USER SET PASS='" . $dat['pass'] . "' WHERE USER_ID='" . $dat['USER_ID'] . "'";
    $stmt = $db->prepare($sql);
    error_log($sql);
    $stmt->execute();

    $sql = "UPDATE PERSON SET NAME='" . $dat['firstName'] . "', LAST_NAME='" . $dat['lastName'] . "', EMAIL='" . $dat['email'] . "', PHONE='" . $dat['phone'] . "' WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    error_log($sql);
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/updateEmployeeData', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();

    $db = $db->connect();

    $sql = "SELECT EMAIL FROM PERSON WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->EMAIL) != $dat['email']) {
      $sql = "SELECT COUNT(*) AS NUM FROM PERSON WHERE EMAIL='" . $dat['email'] . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

      if (($exist["0"]->NUM) != 0) {
        echo '{"error":{"text":"Email is already exist"}}';
        return;
      }
    }
    $sql = "UPDATE USER SET PASS='" . $dat['pass'] . "' WHERE USER_ID='" . $dat['USER_ID'] . "'";
    $stmt = $db->prepare($sql);
    error_log($sql);
    $stmt->execute();

    $sql = "UPDATE PERSON SET NAME='" . $dat['firstName'] . "', LAST_NAME='" . $dat['lastName'] . "', EMAIL='" . $dat['email'] . "', PHONE='" . $dat['phone'] . "' WHERE PERSON_ID='" . $dat['PERSON_ID'] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->get('/getEmployeeData/{employee}', function (Request $request, Response $response, array $args) {
  $employee = $args['employee'];

  $sql = "SELECT US.USER_ID,US.USER_NAME,US.PASS,PE.PERSON_ID,PE.NAME,PE.LAST_NAME,PE.EMAIL,PE.PHONE,EMP.RETAILER_ID FROM EMPLOYEE EMP INNER JOIN USER US ON US.USER_ID=EMP.EMPLOYEE_ID INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_TYPE=4 AND EMP.EMPLOYEE_ID=" . $employee;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});
$app->get('/getRetailer/{retailer}', function (Request $request, Response $response, array $args) {
  $retailer = $args['retailer'];

  $sql = "SELECT *  FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_ID=" . $retailer;

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/addPromo', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "INSERT INTO PROMO(CODE,DISCOUNT,START,FINISH) VALUES (:CODE,:DISCOUNT,:START,:FINISH)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("CODE", $data['promoCode']);
    $stmt->bindParam("DISCOUNT", $data['discount']);
    $stmt->bindParam("START", $data['start']);
    $stmt->bindParam("FINISH", $data['finish']);
    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/updatePromo', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "UPDATE PROMO SET CODE='" . $data['CODE'] . "',DISCOUNT='" . $data['DISCOUNT'] . "',START='" . $data['START'] . "',FINISH='" . $data['FINISH'] . "' WHERE ID=" . $data['ID'];
    $stmt = $db->prepare($sql);

    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/removePromo', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "DELETE FROM PROMO WHERE ID=" . $data['ID'];
    $stmt = $db->prepare($sql);

    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->get('/getAllPromos', function (Request $request, Response $response, array $args) {


  $sql = "SELECT * FROM PROMO";

  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->get('/validPromo/{code}/{user}', function (Request $request, Response $response, array $args) {
  $code = $args['code'];
  $user = $args['user'];
  $fecha = getdate();
  $d = $fecha['mday'];
  $m = $fecha['mon'];
  $y = $fecha['year'];
  $date = $y . "-" . $m . "-" . $d;
  $sql = "SELECT ID,DISCOUNT,USE_LIMIT FROM PROMO WHERE CODE='" . $code . "' AND START<='" . $date . "' AND FINISH>='" . $date . "'";

  error_log($sql);
  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $exist = $stmt->fetchAll(PDO::FETCH_OBJ);
    $id = ($exist["0"]->ID);
    $discount = $exist["0"]->DISCOUNT;
    $limit = (int) $exist["0"]->USE_LIMIT;
    if ($id == '') {
      echo '{"error":{"text":"The promotion code does not exist or has expired"}}';
      return;
    } else {
      $sql = "SELECT COUNT(*) AS NUM FROM PROMO_USER WHERE USER_ID=" . $user . " and CODE='" . $id . "'";
      $stmt = $db->query($sql);
      $exist = $stmt->fetchAll(PDO::FETCH_OBJ);
      $num = (int) $exist["0"]->NUM;
      if ($limit > $num) {
        echo '{"error":{"text":"0"},"DISCOUNT":' . $discount . ',"CODE_ID":' . $id . '}';
      } else {
        echo '{"error":{"text":"You already used this promo code"}}';
      }
    }
    $db = null;
  } catch (PDOException $e) {

    echo json_encode($e);
  }
});

$app->post('/addLocationArea', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();
    if ($data['index'] == 0) {
      $sql = "DELETE FROM LOCATION_AREA WHERE LOCATION_ID=" . $data['location'];
      $stmt = $db->prepare($sql);
      $stmt->execute();

      $sql = "UPDATE LOCATION SET LAT='" . $data['latCenter'] . "',LNG='" . $data['lngCenter'] . "' WHERE LOCATION_ID=" . $data['location'];
      $stmt = $db->prepare($sql);
      $stmt->execute();
    }

    $sql = "INSERT INTO LOCATION_AREA(LAT,LNG,LOCATION_ID) VALUES (:lat,:lng,:location)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("lat", $data['lat']);
    $stmt->bindParam("lng", $data['lng']);
    $stmt->bindParam("location", $data['location']);
    $stmt->execute();



    $db = null;
    echo '{"status":"ok"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->post('/addLocation', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "SELECT MAX(LOCATION_ID) AS NUM FROM LOCATION";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $id = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($id["0"]->NUM) == '')
      $id = 1;
    else
      $id = (($id["0"]->NUM) + 1);

    $sql = "INSERT INTO LOCATION(LOCATION_ID,LOCATION_NAME,RETAILER_ID,ZIP,TAX,SERVICE_TYPE,LAT,LNG) VALUES (:id,:name,:retailer,:zip,:tax,:serviceType,:lat,:lng)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->bindParam("name", $data['name']);
    $stmt->bindParam("retailer", $data['retailer']);
    $stmt->bindParam("zip", $data['zip']);
    $stmt->bindParam("tax", $data['tax']);
    $stmt->bindParam("serviceType", $data['serviceType']);
    $lat = 0;
    $stmt->bindParam("lat", $lat);
    $stmt->bindParam("lng", $lat);


    $stmt->execute();

    $db = null;
    echo '{"status":"ok","id":"' . $id . '"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
$app->post('/addService', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $sql = "SELECT (MAX(LS_ID)+1) AS ID FROM LOCATION_SERVICE;";
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
    echo '{"error":{"text":"0"},"LS_ID":' . $LS_ID . '}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/addAdditionalService', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

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
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/addNewService', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $sql = "SELECT (MAX(SERVICE_ID)+1) AS ID FROM SERVICE;";
  try {
    $db = new db();

    $db = $db->connect();
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $qry = $gsent->fetchAll(PDO::FETCH_OBJ);
    $SERVICE_ID = $qry["0"]->ID;

    $sql = "INSERT INTO SERVICE(SERVICE_ID,SERVICE_NAME,DESCRIPTION,SERVICE_TYPE,PRICE) VALUES (:id,:name,:description,:serviceType,:price)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $SERVICE_ID);
    $stmt->bindParam("name", $data['name']);
    $stmt->bindParam("serviceType", $data['serviceType']);
    $stmt->bindParam("description", $data['description']);
    $stmt->bindParam("price", $data['price']);

    $stmt->execute();

    $sql = "SELECT * FROM LOCATION WHERE SERVICE_TYPE=" . $data['serviceType'];
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $qry = $gsent->fetchAll(PDO::FETCH_OBJ);

    foreach ($qry as &$valor) {

      $sql = "INSERT INTO LOCATION_SERVICE(LOCATION_ID,SERVICE_ID,PRICE,STATUS) VALUES (:locationId,:service,:price,:status)";
      $stmt = $db->prepare($sql);
      $stmt->bindParam("locationId", $valor->LOCATION_ID);
      $stmt->bindParam("service", $SERVICE_ID);
      $stmt->bindParam("price", $data['price']);
      $status = 1;
      $stmt->bindParam("status", $status);

      $stmt->execute();
    }



    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->post('/addWorkDay', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "INSERT INTO WORKDAYS(LOCATION_ID,DAY_ID) VALUES (:location,:day)";
    error_log($sql);
    $stmt = $db->prepare($sql);
    $stmt->bindParam("location", $data['location']);
    $stmt->bindParam("day", $data['day']);


    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/addScheduleLocation', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  try {
    $db = new db();

    $db = $db->connect();

    $sql = "INSERT INTO SCHEDULE_LOCATION(LOCATION_ID,SCHEDULE_ID,MAX) VALUES (:location,:schedule,:max)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("location", $data['LOCATION_ID']);
    $stmt->bindParam("schedule", $data['SCHEDULE_ID']);
    $stmt->bindParam("max", $data['MAX']);
    $stmt->execute();

    $db = null;
    echo '{"error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->post('/addUserVehicle', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //print_r($data['lastName']);
  //print_r($data['firstName']);


  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
  try {


    $db = new db();

    $db = $db->connect();

    $sql = "SELECT MAX(VHE_DETAIL) AS NUM FROM VEHICLE_DETAIL";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $vehID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($vehID["0"]->NUM) == '')
      $vehID = 1;
    else
      $vehID = (($vehID["0"]->NUM) + 1);
    //echo $veh;

    $sql = "INSERT INTO VEHICLE_DETAIL(VHE_DETAIL,PLATE,COLOR,MAKE,MODEL,YEAR) VALUES (:id,:plate,:color,:make,:model,:year)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $vehID);
    $stmt->bindParam("plate", $dat['plate']);
    $stmt->bindParam("color", $dat['color']);
    $stmt->bindParam("make", $dat['make']);
    $stmt->bindParam("model", $dat['model']);
    $stmt->bindParam("year", $dat['year']);
    $stmt->execute();

    $sql = "INSERT INTO USER_VEHICLES(ID_USER,VEHICLE_DETAIL) VALUES(:user,:vhe)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("user", $dat['user']);
    $stmt->bindParam("vhe", $vehID);

    $stmt->execute();

    $db = null;
    echo '{"status":"ok","vehicle_id":"' . $vehID . '"}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

$app->post('/addEmployee', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();



  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
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
    $stmt->bindParam("phone", $dat['phone']);

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
    echo '{"USER_ID":"' . $userID . '","error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});

$app->post('/addRetailer', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();



  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
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
    $stmt->bindParam("phone", $dat['phone']);

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
    $stmt->bindParam("id", $userId);
    $stmt->bindParam("userName", $dat['userName']);
    $stmt->bindParam("pass", $dat['pass']);
    $stmt->bindParam("type", $dat['user_type']);
    $stmt->bindParam("personId", $idPerson);

    $stmt->execute();

    $db = null;
    echo '{"USER_ID":"' . $userID . '","error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
$app->post('/remember', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();

    $db = $db->connect();
    //echo $veh;


    $sql = "SELECT COUNT(*) AS NUM FROM PERSON WHERE EMAIL='" . $dat['email'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

    if (($exist["0"]->NUM) == 0) {
      echo '{"error":{"text":"The email is not exist"}}';
      return;
    }
    $sql = "SELECT USER_ID,CONCAT(NAME,' ',LAST_NAME) AS NAME FROM USER INNER JOIN PERSON ON USER.PERSON_ID=PERSON.PERSON_ID WHERE USER.USER_TYPE=3 AND EMAIL='" . $dat['email'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $usr = $gsent->fetchAll(PDO::FETCH_OBJ);
    $usrID = $usr["0"]->USER_ID;
    $usrNAME = $usr["0"]->NAME;

    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $string = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < 10; $i++) {
      $string .= $characters[mt_rand(0, $max)];
    }

    $sql = "UPDATE USER SET PASS='" . $string . "' WHERE USER_ID='" . $usrID . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $email = sendForgetPassword($dat['email'], $string, $usrNAME);
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ACTUALIZA LA INFO GENERAL DE LOCATION
$app->post('/updateLocationGD', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "UPDATE LOCATION SET LOCATION_NAME='" . $dat['LOCATION_NAME'] . "',RETAILER_ID='" . $dat["RETAILER_ID"] . "',ZIP='" . $dat["ZIP"] . "',TAX='" . $dat["TAX"] . "' WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ACTUALIZA MOBILE DATA
$app->post('/updateLocationMB', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "UPDATE LOCATION SET LAT='" . $dat['LAT'] . "',LNG='" . $dat["LNG"] . "',MAX_DISTANCE='" . $dat["MAX_DISTANCE"] . "' WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});
//ACTUALIZA LOS DIAS DE TRABAJO 1-ELIMINA LOS DIAS
$app->post('/updateLocationWD1', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "DELETE FROM WORKDAYS WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});

//ACTUALIZA LAS HORAS
$app->post('/updateLocationHD', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "UPDATE SCHEDULE_LOCATION SET MAX='" . $dat["MAX"] . "' WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "' AND SCHEDULE_ID=" . $dat["SCHEDULE_ID"];
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $db = null;

    echo '{"error":0}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});


$app->post('/signupRetailer', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
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

    $sql = "INSERT INTO PERSON(PERSON_ID,NAME,LAST_NAME,EMAIL,PHONE,COMPANY_NAME,OFFICE_PHONE1,OFFICE_PHONE2,FAX,NO,STREET,CITY,STATE,ZIP,COUNTRY) VALUES (:id,:firstName,:lastName,:email,:phone,:companyName,:phone1,:phone2,:fax,:no,:street,:city,:state,:zip,:country)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $idPerson);
    $stmt->bindParam("firstName", $dat['firstName']);
    $stmt->bindParam("lastName", $dat['lastName']);
    $stmt->bindParam("email", $dat['email']);
    $stmt->bindParam("phone", $dat['phone']);
    $stmt->bindParam("companyName", $dat['companyName']);
    $stmt->bindParam("phone1", $dat['phone1']);
    $stmt->bindParam("phone2", $dat['phone2']);
    $stmt->bindParam("fax", $dat['fax']);
    $stmt->bindParam("no", $dat['no']);
    $stmt->bindParam("street", $dat['street']);
    $stmt->bindParam("city", $dat['city']);
    $stmt->bindParam("state", $dat['state']);
    $stmt->bindParam("zip", $dat['zip']);
    $stmt->bindParam("country", $dat['country']);

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
    $stmt->bindParam("id", $userId);
    $stmt->bindParam("userName", $dat['userName']);
    $stmt->bindParam("pass", $dat['pass']);
    $stmt->bindParam("type", $dat['user_type']);
    $stmt->bindParam("personId", $idPerson);

    $stmt->execute();
    $db = null;
    echo '{"USER_ID":"' . $userID . '","error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":' . $e->getMessage() . '}}';
  }
});




$app->post('/signupFacebook', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();
  try {
    $db = new db();
    $db = $db->connect();

    $db = null;
    echo '{"USER_ID":"' . $userID . '","error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});


$app->post('/checkFBExist', function (Request $request, Response $response, array $args) {
  //$email = $args['userName'];
  //$fbId = $args['fbId'];
  $dat = $request->getParsedBody();

  $sql = "SELECT  * FROM USER us INNER JOIN PERSON pe ON us.PERSON_ID=pe.PERSON_ID WHERE FACEBOOK_ID='" . $dat["fbId"] . "'";
  try {

    $db = new db();

    $db = $db->connect();

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (count($data) > 0)
      $data[0]->error = 0;
    else
      $data[0]->error = 1;
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/checkFBEmailExist', function (Request $request, Response $response, array $args) {
  //$email = $args['userName'];
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();
    $sql = "SELECT  US.USER_ID FROM PERSON PE INNER JOIN USER US ON PE.PERSON_ID=US.PERSON_ID WHERE EMAIL='" . $dat['email'] . "'";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $userID = $gsent->fetchAll(PDO::FETCH_OBJ);
    $data = [];
    if ($userID) {
      $_userID = $userID["0"]->USER_ID;

      $sql = "UPDATE PERSON SET PICTURE='" . $dat["picture"] . "' WHERE EMAIL='" . $dat["email"] . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();

      $sql = "UPDATE USER SET FACEBOOK_ID='" . $dat["fbId"] . "' WHERE USER_ID='" . $_userID . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();

      $sql = "UPDATE USER SET MOBILE_TOKEN='" . $dat["mobileToken"] . "' WHERE USER_ID='" . $_userID . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();

      $sql = "SELECT  * FROM USER us INNER JOIN PERSON pe ON us.PERSON_ID=pe.PERSON_ID WHERE us.USER_ID='" . $_userID . "'";
      $gsent = $db->prepare($sql);
      $gsent->execute();
      $data = $gsent->fetchAll(PDO::FETCH_OBJ);
      $data[0]->error = 0;
    } else {
      $data[0]->error = 1;
    }
    $db = null;

    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    header("Access-Control-Allow-Origin: *");
    echo json_encode($e);
  }
});

$app->post('/signupFB', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();

  try {

    $db = new db();
    $db = $db->connect();

    $sql = "SELECT MAX(PERSON_ID) AS NUM FROM PERSON";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $idPerson = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($idPerson["0"]->NUM) == '')
      $idPerson = 1;
    else
      $idPerson = (($idPerson["0"]->NUM) + 1);

    $sql = "INSERT INTO PERSON(PERSON_ID,NAME,LAST_NAME,EMAIL,PHONE,PICTURE) VALUES (:id,:firstName,:lastName,:email,:phone,:picture)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $idPerson);
    $stmt->bindParam("firstName", $dat['firstName']);
    $stmt->bindParam("lastName", $dat['lastName']);
    $stmt->bindParam("email", $dat['email']);
    $stmt->bindParam("phone", $dat['phone']);
    $stmt->bindParam("picture", $dat['picture']);

    $stmt->execute();

    $sql = "SELECT MAX(USER_ID) AS NUM FROM USER";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $userID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($userID["0"]->NUM) == '')
      $userID = 1;
    else
      $userID = (($userID["0"]->NUM) + 1);

    $sql = "INSERT INTO USER(USER_ID,USER_NAME,PASS,USER_TYPE,PERSON_ID,MOBILE_TOKEN,FACEBOOK_ID) VALUES(:id,:userName,:pass,:type,:personId,:mobileToken,:fbId)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $userID);
    $stmt->bindParam("userName", $dat['userName']);
    $stmt->bindParam("pass", $dat['pass']);
    $stmt->bindParam("type", $dat['user_type']);
    $stmt->bindParam("personId", $idPerson);
    $stmt->bindParam("mobileToken", $dat['mobileToken']);
    $stmt->bindParam("fbId", $dat['fbId']);
    $stmt->execute();

    $sql = "SELECT  * FROM USER us INNER JOIN PERSON pe ON us.PERSON_ID=pe.PERSON_ID WHERE us.USER_ID=" . $userID;
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_OBJ);

    $data[0]->error = 0;
    $db = null;
    sendUserFBInfo($dat['email'], $dat['userName'], $dat['pass']);
    header("Access-Control-Allow-Origin: *");
    return $response->withJson($data);
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});


$app->post('/signup', function (Request $request, Response $response, array $args) {
  $dat = $request->getParsedBody();



  //  $sql = "INSERT INTO RESERVATION (, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
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
    $stmt->bindParam("phone", $dat['phone']);

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
    $stmt->bindParam("id", $userId);
    $stmt->bindParam("userName", $dat['userName']);
    $stmt->bindParam("pass", $dat['pass']);
    $stmt->bindParam("type", $dat['user_type']);
    $stmt->bindParam("personId", $idPerson);

    $stmt->execute();

    $sql = "SELECT MAX(VHE_DETAIL) AS NUM FROM VEHICLE_DETAIL";
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $vehID = $gsent->fetchAll(PDO::FETCH_OBJ);
    if (($vehID["0"]->NUM) == '')
      $vehID = 1;
    else
      $vehID = (($vehID["0"]->NUM) + 1);
    //echo $veh;

    $sql = "INSERT INTO VEHICLE_DETAIL(VHE_DETAIL,PLATE,COLOR,MAKE,MODEL,YEAR) VALUES (:id,:plate,:color,:make,:model,:year)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $vehID);
    $stmt->bindParam("plate", $dat['plate']);
    $stmt->bindParam("color", $dat['color']);
    $stmt->bindParam("make", $dat['make']);
    $stmt->bindParam("model", $dat['model']);
    $stmt->bindParam("year", $dat['year']);
    $stmt->execute();

    $sql = "INSERT INTO USER_VEHICLES(ID_USER,VEHICLE_DETAIL) VALUES(:user,:vhe)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("user", $userID);
    $stmt->bindParam("vhe", $vehID);

    $stmt->execute();



    $db = null;
    echo '{"USER_ID":"' . $userID . '","error":{"text":"0"}}';
  } catch (PDOException $e) {
    echo '{"error":{"text":"' . $e->getMessage() . '"}}';
  }
});
