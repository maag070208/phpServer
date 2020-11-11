<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/getMonthSales', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $retailer = $request->getQueryParam('retailer');
    $employee = $request->getQueryParam('employee');
    $location = $request->getQueryParam('employee');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT SUM(TOTAL) FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE MONTH(RES.DATE)=MONTH(NOW()) AND YEAR(RES.DATE)=YEAR(NOW())";

    if ($location > -1) {
        $sql .= " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer > -1) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
    }

    if ($employee > -1) {
        $sql .= " AND RES.EMPLOYEE=" . $employee;
    }
    error_log($sql);

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getYearSales', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $retailer = $request->getQueryParam('retailer');
    $employee = $request->getQueryParam('employee');
    $location = $request->getQueryParam('employee');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT SUM(TOTAL) FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=YEAR(NOW())";

    if ($location > -1) {
        $sql .= " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer > -1) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
    }

    if ($employee > -1) {
        $sql .= " AND RES.EMPLOYEE=" . $employee;
    }
    error_log($sql);

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getAllSales', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $retailer = $request->getQueryParam('retailer');
    $employee = $request->getQueryParam('employee');
    $location = $request->getQueryParam('location');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sqlYear = "SELECT SUM(TOTAL) AS TOTAL FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=YEAR(NOW())";
    $sqlMonth = "SELECT SUM(TOTAL) AS TOTAL FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE MONTH(RES.DATE)=MONTH(NOW()) AND YEAR(RES.DATE)=YEAR(NOW())";
    $sqlWeek = "SELECT SUM(TOTAL) AS TOTAL FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE YEARWEEK(RES.DATE)=YEARWEEK(NOW()) AND MONTH(RES.DATE)=MONTH(NOW()) AND YEAR(RES.DATE)=YEAR(NOW())";
    $by = "";
    if ($location != -1 && $location != null) {
        $by = " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer != -1 && $retailer != null) {
        $by .= " AND LO.RETAILER_ID=" . $retailer;
    }

    if ($employee != -1 && $employee != null) {
        $by .= " AND RES.EMPLOYEE=" . $employee;
    }
    $sqlMonth .= $by;
    $sqlMonth .= $by;
    $sqlYear .= $by;

    error_log($sqlMonth);
    error_log($sqlWeek);
    error_log($sqlYear);

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();
            $stmt = $db->query($sqlYear);
            $byYear = $stmt->fetchAll(PDO::FETCH_OBJ);
            $stmt = $db->query($sqlMonth);
            $byMonth = $stmt->fetchAll(PDO::FETCH_OBJ);
            $stmt = $db->query($sqlWeek);
            $byWeek = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $sales = ["byYear" => $byYear[0]->TOTAL, "byMonth" => $byMonth[0]->TOTAL, "byWeek" => $byWeek[0]->TOTAL];
            $ret = ["data" => $sales, "error" => 0];
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

$app->get('/getTotalReservationsByYear', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOC.LOCATION_NAME,LOC.LOCATION_ID,COUNT(*) AS TOTAL FROM `RESERVATION` RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(DATE)=YEAR(NOW()) group by RES.LOCATION_ID";

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getTotalReservationsByMonth', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOC.LOCATION_NAME,LOC.LOCATION_ID,COUNT(*) AS TOTAL FROM `RESERVATION` RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE MONTH(DATE)=MONTH(NOW()) group by RES.LOCATION_ID";

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getTotalReservationsByWeek', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOC.LOCATION_NAME,LOC.LOCATION_ID,COUNT(*) AS TOTAL FROM `RESERVATION` RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEARWEEK(DATE)=YEARWEEK(NOW()) group by RES.LOCATION_ID";

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getTotalByMonthYear', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $month = $request->getQueryParam('month');
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');
    $employee = $request->getQueryParam('employee');
    $location = $request->getQueryParam('location');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT SUM(TOTAL) FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE MONTH(RES.DATE)=" . $month . " AND YEAR(RES.DATE)=" . $year;

    if ($location != -1 && $location != null) {
        $sql .= " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer != -1 && $retailer != null) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
    }

    if ($employee != -1 && $employee != null) {
        $sql .= " AND RES.EMPLOYEE=" . $employee;
    }

    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getMontlySales', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');
    $type = $request->getQueryParam('type');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT SUM(RES.TOTAL) AS amount, MONTHNAME(RES.DATE)as label FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=" . $year . " ";
    if ($retailer > -1) {
        $sql .= " AND (LOC.RETAILER_ID=" . $retailer . " OR RES.EMPLOYEE=" . $retailer . ")";
    }
    if ($type != -1) {
        $sql .= " AND LOC.SERVICE_TYPE=" . $type;
    }
    $sql .= " GROUP BY MONTH(RES.DATE)";
    error_log($sql);
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getWeeklySales', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');
    $type = $request->getQueryParam('type');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT SUM(RES.TOTAL) AS amount, CONCAT('w',WEEK(RES.DATE)+1) as name FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=" . $year . "";
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }

    if ($type != -1) {
        $sql .= " AND LOC.SERVICE_TYPE=" . $type;
    }
    $sql .= " GROUP BY WEEK(RES.DATE)";
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getWeeklySalesByLocation', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $type = $request->getQueryParam('type');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT LOCATION_NAME AS name,LOCATION_ID FROM LOCATION WHERE STATUS=1";
    if ($type != -1) {
        $sql .= " AND SERVICE_TYPE=" . $type;
    }

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();
            $stmt = $db->query($sql);
            $locations = $stmt->fetchAll(PDO::FETCH_OBJ);
            $index = 0;
            foreach ($locations as &$valor) {
                error_log($valor->LOCATION_ID . "jhdsjahdjkhaskjdhaksjdh");
                $sql = "SELECT SUM(TOTAL) AS y, WEEK(DATE) as x FROM RESERVATION WHERE LOCATION_ID=" . $valor->LOCATION_ID . " AND YEAR(DATE)=" . $year . " GROUP BY WEEK(DATE)";
                $stmt = $db->query($sql);
                $values = $stmt->fetchAll(PDO::FETCH_OBJ);
                $locations[$index]->values = $values;
                $index++;
            }
            $db = null;

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($locations);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getMontlyReservations', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT COUNT(*) AS amount, MONTHNAME(DATE)as label FROM RESERVATION WHERE YEAR(DATE)=" . $year . " GROUP BY MONTH(DATE)";
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getReservationAverage', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $type = $request->getQueryParam('type');
    $opt = $request->getQueryParam('option');
    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT COUNT(RES.RESERVATION_ID) as total FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE LO.STATUS=1";

    if ($type != -1) {
        $sql .= " AND LO.SERVICE_TYPE=" . $type;
    }

    if ($retailer > -1) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
    }


    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();
            $stmt = $db->query($sql);
            $total = $stmt->fetchAll(PDO::FETCH_OBJ);

            $sql = "SELECT (COUNT(RES.RESERVATION_ID)*100/" . $total[0]->total . ") AS average, LO.LOCATION_NAME AS name FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID WHERE LO.STATUS=1 ";
            $sql .= " AND YEAR(DATE)=" . $year;

            //Month
            if ($opt == 2) {
                $sql .= " AND MONTH(RES.DATE)=MONTH(now())";
            }
            if ($type != -1) {
                $sql .= " AND LO.SERVICE_TYPE=" . $type;
            }
            if ($retailer > -1) {
                $sql .= " AND LO.RETAILER_ID=" . $retailer;
            }
            $sql .= " GROUP BY LO.LOCATION_ID";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            $db = null;

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($data);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            echo json_encode($e);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getServiceAverage', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $type = $request->getQueryParam('type');
    $opt = $request->getQueryParam('option');
    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT (COUNT(RES.RESERVATION_ID)*100/(SELECT COUNT(RES.RESERVATION_ID) as total FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN LOCATION_SERVICE LOS ON LOS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON LOS.SERVICE_ID=SER.SERVICE_ID WHERE SER.STATUS=1";
    if ($type != -1) {
        $sql .= " AND LOC.SERVICE_TYPE=" . $type;
    }
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= ")) AS average, SER.SERVICE_NAME AS name FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN LOCATION_SERVICE LOS ON LOS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON LOS.SERVICE_ID=SER.SERVICE_ID WHERE SER.STATUS=1";
    $sql .= " AND YEAR(RES.DATE)=" . $year;
    //Month
    if ($opt == 2) {
        $sql .= " AND MONTH(RES.DATE)=MONTH(now())";
    }
    if ($type != -1) {
        $sql .= " AND LOC.SERVICE_TYPE=" . $type;
    }
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= " GROUP BY SER.SERVICE_NAME";


    error_log($sql);
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getEmployeeAverage', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');
    $opt = $request->getQueryParam('option');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT (COUNT(RES.RESERVATION_ID)*100/(SELECT COUNT(RES.RESERVATION_ID) as total FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN LOCATION_SERVICE LOS ON LOS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON LOS.SERVICE_ID=SER.SERVICE_ID WHERE SER.STATUS=1 AND RES.EMPLOYEE >1";

    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= ")) AS average, US.USER_NAME AS name  FROM RESERVATION RES LEFT JOIN USER US ON US.USER_ID=RES.EMPLOYEE INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN LOCATION_SERVICE LOS ON LOS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON LOS.SERVICE_ID=SER.SERVICE_ID WHERE SER.STATUS=1  AND RES.EMPLOYEE>1";

    $sql .= " AND YEAR(RES.DATE)=" . $year;
    //Month
    if ($opt == 2) {
        $sql .= " AND MONTH(RES.DATE)=MONTH(now())";
    }
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= "  GROUP BY US.USER_NAME";


    error_log($sql);
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getAdditionalAverage', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $opt = $request->getQueryParam('option');

    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT COUNT(*)*100/(SELECT COUNT(*) FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN RESERVATION RES ON RES.RESERVATION_ID=RAS.RESERVATION_ID";
    if ($year != -1) {
        $sql .= " WHERE YEAR(RES.DATE)=" . $year;
    }
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= ") as average,ADDS.ADDITIONAL_NAME as name FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN RESERVATION RES ON RES.RESERVATION_ID=RAS.RESERVATION_ID INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID INNER JOIN ADDITIONAL ADDS ON ADDS.ADDITIONAL_ID=RAS.ADDITIONAL_ID";

    if ($year != -1) {
        $sql .= " WHERE YEAR(RES.DATE)=" . $year;
    }
    //Month
    if ($opt == 2) {
        $sql .= " AND MONTH(RES.DATE)=MONTH(now())";
    }
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= " GROUP BY RAS.ADDITIONAL_ID";



    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getLastMonthEarningsComparation', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    //$sql = "SELECT SUM(TOTAL) AS amount, MONTHNAME(DATE)as label FROM RESERVATION WHERE YEAR(DATE)=" . $year . " GROUP BY MONTH(DATE)";

    $sql = "SELECT SUM(RES.TOTAL) AS amount, MONTHNAME(RES.DATE)as label FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=" . $year;
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= " GROUP BY MONTH(RES.DATE) ORDER BY MONTH(RES.DATE) DESC LIMIT 2";
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getLastMonthReservationsComparation', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $year = $request->getQueryParam('year');
    $retailer = $request->getQueryParam('retailer');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT COUNT(*) AS amount, MONTHNAME(DATE)as label FROM RESERVATION RES INNER JOIN LOCATION LOC ON LOC.LOCATION_ID=RES.LOCATION_ID WHERE YEAR(RES.DATE)=" . $year;
    if ($retailer > -1) {
        $sql .= " AND LOC.RETAILER_ID=" . $retailer;
    }
    $sql .= " GROUP BY MONTH(DATE) ORDER BY MONTH(DATE) DESC LIMIT 2";
    if ($valid) {
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
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
