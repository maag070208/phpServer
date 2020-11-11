<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getAllLocations', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $retailer = $request->getQueryParam('retailer');
    $type = $request->getQueryParam('type');
    $txt = $request->getQueryParam('text');
    $page = ((int) $page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOC.*,USU.USER_ID,USU.USER_NAME FROM LOCATION LOC LEFT JOIN USER USU ON USU.USER_ID=LOC.RETAILER_ID WHERE LOC.STATUS=1";

    if ($retailer != -1) {
        $sql .= " AND LOC.RETAILER_ID ='" . $retailer . "'";
    }
    if ($type != -1) {
        $sql .= " AND LOC.SERVICE_TYPE ='" . $type . "'";
    }
    if ($txt != "") {
        $sql .= " AND CONCAT( LOC.LOCATION_NAME,  ' ', USU.LAST_NAME, ' ', USU.USER_NAME) LIKE '%" . $txt . "%'";
    }
    error_log($sql);
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $pages = $stmt->fetchAll(PDO::FETCH_OBJ);
            $sql .= " ORDER BY LOCATION_ID DESC LIMIT " . $page . ",20";
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


$app->get('/getLocationInfo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOC.*,US.USER_NAME AS RETAILER  FROM LOCATION LOC left join USER US ON US.USER_ID=LOC.RETAILER_ID  WHERE  LOC.LOCATION_ID=" . $location;

    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $ret = ["data" => $data];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getLocationArea', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LAT AS lat,LNG as lng FROM LOCATION_AREA WHERE LOCATION_ID=" . $location;

    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $ret = ["data" => $data];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getLocationItems', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $retailer = $request->getQueryParam('retailer');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOCATION_NAME AS name,LOCATION_ID AS value FROM LOCATION WHERE STATUS=1 AND RETAILER_ID=" . $retailer;

    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $ret = ["data" => $data];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getEmployeeLocationItems', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $employee = $request->getQueryParam('employee');
    $retailer = $request->getQueryParam('retailer');
    $serviceType = $request->getQueryParam('serviceType');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOCATION_NAME AS name,LOCATION_ID AS value FROM LOCATION WHERE STATUS=1 ";
    if($serviceType!=-1){
        $sql.=" AND SERVICE_TYPE=".$serviceType;
    }
    if($retailer!=-1){
        $sql.=" AND RETAILER_ID IN (SELECT RETAILER_ID FROM EMPLOYEE where EMPLOYEE_ID=".$retailer.") ORDER BY LOCATION_ID DESC";
    }
    if($employee!=-1){
        $sql.=" AND RETAILER_ID IN (SELECT RETAILER_ID FROM EMPLOYEE where EMPLOYEE_ID=".$employee.") ORDER BY LOCATION_ID DESC";
    }

    
    error_log($sql);
    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $ret = ["data" => $data];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getWorkDays', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT * FROM WORKDAYS WHERE LOCATION_ID=" . $location;
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
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getScheduleItem', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');
    $date = $request->getQueryParam('date');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();
            $sql = "SELECT * FROM RESERVATION WHERE LOCATION_ID=" . $location . " AND DATE='" . $date . "'";
            error_log($sql);
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);
            if (count($resultado) <= 0) {
                $sql = "SELECT SL.ID as value,SC.HOUR_12 as name FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SL.SCHEDULE_ID  WHERE SL.MAX>0 AND LOCATION_ID=" . $location . " ORDER BY SL.SCHEDULE_ID";
                $gsent = $db->prepare($sql);
                $gsent->execute();
                $data = $gsent->fetchAll(PDO::FETCH_OBJ);
                $ret = ["data" => $data, "error" => 0];
                $db = null;
                header("Access-Control-Allow-Origin: *");
                return $response->withJson($ret);
            } else {

                $sql = "SELECT SL.ID AS value,SCH.HOUR_12 as name FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SCH ON SCH.SCHEDULE_ID=SL.SCHEDULE_ID WHERE LOCATION_ID=" . $location . " AND SL.MAX>0 AND ID NOT IN (SELECT SCHEDULE_LOCATION FROM RESERVATION WHERE DATE='" . $date . "' AND LOCATION_ID=" . $location . ") ORDER BY SL.SCHEDULE_ID";
                $gsent = $db->prepare($sql);
                $gsent->execute();
                $resultado1 = $gsent->fetchAll(PDO::FETCH_OBJ);

                $sql = "SELECT SC.ID as value,SCH.HOUR_12 as name FROM RESERVATION RE  INNER JOIN SCHEDULE_LOCATION SC ON RE.SCHEDULE_LOCATION=SC.ID INNER JOIN SCHEDULE SCH ON SCH.SCHEDULE_ID=SC.SCHEDULE_ID where  RE.DATE='" . $date . "' AND RE.LOCATION_ID=" . $location . " GROUP BY RE.SCHEDULE_LOCATION HAVING COUNT(*)<(SELECT MAX FROM SCHEDULE_LOCATION WHERE ID=RE.SCHEDULE_LOCATION)  ORDER BY SCH.SCHEDULE_ID";
                $gsent = $db->prepare($sql);
                $gsent->execute();
                $resultado2 = $gsent->fetchAll(PDO::FETCH_OBJ);
                $data = array_merge($resultado1, $resultado2);
                $db = null;
                $ret = ["data" => $data, "error" => 0];
                header("Access-Control-Allow-Origin: *");
                return $response->withJson($ret);
            }
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getTimesItem', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $from = $request->getQueryParam('from');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT HOUR_12 AS name,SCHEDULE_ID AS value FROM SCHEDULE WHERE SCHEDULE_ID>=" . $from;
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
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getTimesFromTo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $from = $request->getQueryParam('from');
    $to = $request->getQueryParam('to');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT * FROM SCHEDULE WHERE SCHEDULE_ID>=" . $from . " AND SCHEDULE_ID<=" . $to;
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
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/addLocation', function (Request $request, Response $response, array $args) {

    $jwt = $request->getHeaders();
    $data = $request->getParsedBody();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
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

            $sql = "INSERT INTO LOCATION(LOCATION_ID,LOCATION_NAME,RETAILER_ID,ZIP,TAX,SERVICE_TYPE) VALUES (:id,:name,:retailer,:zip,:tax,:serviceType)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->bindParam("name", $data['name']);
            $stmt->bindParam("retailer", $data['retailer']);
            $stmt->bindParam("zip", $data['zip']);
            $stmt->bindParam("tax", $data['tax']);
            $stmt->bindParam("serviceType", $data['serviceType']);



            $stmt->execute();

            $db = null;
            $data = array("id" => $id);
            $ret = ["data" => $data, "error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/addWorkDay', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
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
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});



$app->post('/addScheduleLocation', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
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
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->post('/updateCoordinates', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();

            $sql = "UPDATE LOCATION SET LAT='" . $data['lat'] . "',LNG='" . $data['lng'] . "' WHERE LOCATION_ID=" . $data['location'];
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

$app->post('/deleteLocationArea', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();

            $sql = "DELETE FROM LOCATION_AREA WHERE LOCATION_ID=" . $data['location'];
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

$app->post('/addLocationArea', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();

            $db = $db->connect();
            if ($data['index'] == 0) {

                /* $sql = "DELETE FROM LOCATION_AREA WHERE LOCATION_ID=" . $data['location'];
                $stmt = $db->prepare($sql);
                $stmt->execute();
*/
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
            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getMaxMinTimes', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $loc = $request->getQueryParam('location');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT * FROM SCHEDULE WHERE SCHEDULE_ID=(SELECT MAX(SCHEDULE_ID) FROM SCHEDULE_LOCATION WHERE LOCATION_ID=" . $loc . ") OR SCHEDULE_ID=(SELECT MIN(SCHEDULE_ID) FROM SCHEDULE_LOCATION WHERE LOCATION_ID=" . $loc . ")";
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            header("Access-Control-Allow-Origin: *");
            $ret = ["data" => $data, "error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getScheduleLocation', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $loc = $request->getQueryParam('location');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT * FROM SCHEDULE_LOCATION SL INNER JOIN SCHEDULE SC ON SC.SCHEDULE_ID=SL.SCHEDULE_ID  WHERE  SL.LOCATION_ID=" . $loc . " ORDER BY SC.SCHEDULE_ID";
    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            header("Access-Control-Allow-Origin: *");
            $ret = ["data" => $data, "error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->post('/updateLocationInfo', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();

    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $sql = "UPDATE LOCATION SET LOCATION_NAME='" . $dat['LOCATION_NAME'] . "',ZIP='" . $dat["ZIP"] . "',TAX='" . $dat["TAX"] . "' WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "'";
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

$app->post('/deleteWorksDay', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {

            $db = new db();
            $db = $db->connect();
            $sql = "DELETE FROM WORKDAYS WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "'";
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


//ACTUALIZA LAS HORAS
$app->post('/updateLocationHD', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {

            $db = new db();
            $db = $db->connect();
            $sql = "UPDATE SCHEDULE_LOCATION SET MAX='" . $dat["MAX"] . "' WHERE LOCATION_ID='" . $dat["LOCATION_ID"] . "' AND SCHEDULE_ID=" . $dat["SCHEDULE_ID"];
            $gsent = $db->prepare($sql);
            $gsent->execute();
            $db = null;

            $ret = ["error" => 0];
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
