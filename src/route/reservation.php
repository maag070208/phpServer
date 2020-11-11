<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'JWTValidation.php';
require '../src/email.php';
require '../src/sendPush.php';

$app->get('/getAllReservations', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();

    error_log("hola");
    $from = $request->getQueryParam('from');
    $to = $request->getQueryParam('to');
    $opc = $request->getQueryParam('opc');
    $service = $request->getQueryParam('service');
    $retailer = $request->getQueryParam('retailer');
    $status = $request->getQueryParam('status');
    $employee = $request->getQueryParam('employeeId');
    $by = $request->getQueryParam('by');
    $txt = $request->getQueryParam('txt');
    $page = $request->getQueryParam('page');
    $export = $request->getQueryParam('export');
    //$export = -1;
    $paid = $request->getQueryParam('paid');
    $location = $request->getQueryParam('location');

    $page = ((int) $page) * 20;

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            //$decoded = GetTokenData($jwt['HTTP_AUTHORIZATION'][0]);
            $sql = "SELECT ";
            $sql .= "RES.RESERVATION_ID,";
            $sql .= "RES.SERVICE_TYPE,";
            $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
            $sql .= "RES.STATUS,";
            $sql .= "SCHE.HOUR,";
            $sql .= "US.USER_NAME AS RETAILER,";
            $sql .= "LO.LOCATION_NAME AS LOCATION,";
            $sql .= "SER.SERVICE_NAME,";
            $sql .= "US2.USER_NAME AS EMPLOYEE,";
            $sql .= "RES.EMPLOYEE,";
            $sql .= "PE.NAME AS EMPLOYEE_NAME,";
            $sql .= "PE.LAST_NAME AS EMPLOYEE_LASTNAME,";
            $sql .= "PE3.NAME AS CUSTOMER_NAME,";
            $sql .= "PE3.LAST_NAME AS CUSTOMER_LASTNAME,";
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
            $sql .= "RES.ADDRESS,";
            $sql .= "PROM.CODE,";
            $sql .= "RES.PROMO_DISCOUNT,";
            $sql .= "LOC_RAT.RAITING";
            $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
            $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
            $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
            $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
            $sql .= " LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
            $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
            $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
            $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
            $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
            $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
            $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
            $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
            $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
            $sql .= " LEFT JOIN LOCATION_RATING LOC_RAT ON LOC_RAT.RESERVATION_FK=RES.RESERVATION_ID ";
            $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.SERVICE_TYPE=" . $service;

            if ($location != -1) {
                $sql .= " AND LO.LOCATION_ID ='" . $location . "'";
            }
            if ($retailer != -1) {
                $sql .= " AND LO.RETAILER_ID ='" . $retailer . "'";
            }
            if ($employee != -1) {
                $sql .= " AND RES.EMPLOYEE ='" . $employee . "'";
            }
            if ($status != -1) {
                $sql .= " AND RES.STATUS ='" . $status . "'";
            }
            if ($paid == 1) {
                $sql .= " AND RES.STATUS <= 3";
            }
            if ($opc == 2) {
                $sql .= " AND RES.DATE>='" . $from . "'";
                $sql .= " AND RES.DATE<='" . $to . "'";
            }
            if ($by == 1) {
                $sql .= " AND CONCAT( PE3.NAME,  ' ', PE3.LAST_NAME, ' ', US3.USER_NAME, ' ', PE3.EMAIL,' ', PE3.PHONE ) LIKE '%" . $txt . "%'";
            }

            if ($by == 2) {
                //$sql .= " AND (PE3.NAME LIKE '%" . $txt . "%'";
                //$sql .= " OR PE3.LAST_NAME LIKE '%" . $txt . "%')";

                $sql .= " AND CONCAT( PE.NAME,  ' ', PE.LAST_NAME, ' ', US2.USER_NAME, ' ', PE.EMAIL,' ', PE.PHONE ) LIKE '%" . $txt . "%'";
            }

            if ($by == 3) {
                $sql .= " AND (US.USER_NAME LIKE '%" . $txt . "%')";
            }
            //BY LOCATION
            if ($by == 4) {
                $sql .= " AND (LO.LOCATION_NAME LIKE '" . $txt . "%')";
            }
            if ($export != 1) {
                $sql .= " ORDER BY RES.RESERVATION_ID DESC LIMIT " . $page . ",20";
            }



            error_log($sql);
            $db = new db();

            $db = $db->connect();

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

            $cont = 0;
            foreach ($resultado as $valor) {

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
                $adds = CheckAdds($resultado2);
                $additionals = ["ADDITIONALS" => $adds, "TOTAL_ADDITIONAL" => $sum];
                $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
                $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
                $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;

                $gsent = $db->prepare($sql);
                $gsent->execute();
                $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);
                /*
            if ($resultado3) {
                $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LASTNAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
            } else {
                $res = array("EMPLOYEE_USER" => 'None', "EMPLOYEE_NAME" => 'None', "EMPLOYEE_LASTNAME" => 'None');
            }
            $resultado[$cont] = array_merge((array) $resultado[$cont], $res);
*/
                $cont++;
            }

            $db = null;
            header("Access-Control-Allow-Origin: *");

            $dataArray = [
                "pages" => getPages($opc, $service, $from, $to, $by, $txt, $status, $retailer, $employee),
                "data" => utf8ize($resultado),
                "adds" => utf8ize(getAdds()),
            ];
            return $response->withJson(utf8ize($dataArray));
        } catch (PDOException $e) {
            error_log(json_encode($e));
        }
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});





//GET ALL DATA
$app->get('/getAllReservations2/{opc}/{from}/{to}/{service}/{by}/{txt}', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();



    $from = $request->getQueryParam('from');
    $to = $request->getQueryParam('to');
    $opc = $request->getQueryParam('opc');
    $service = $request->getQueryParam('service');
    $retailer = $request->getQueryParam('retailer');
    $status = $request->getQueryParam('status');
    $employee = $request->getQueryParam('employeeId');
    $by = $request->getQueryParam('by');
    $txt = $request->getQueryParam('txt');




    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $decoded = GetTokenData($jwt['HTTP_AUTHORIZATION'][0]);
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
        $sql .= "PE3.LAST_NAME AS CUSTOMER_LASTNAME,";
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
        $sql .= "RES.ADDRESS,";
        $sql .= "PROM.CODE,";
        $sql .= "RES.PROMO_DISCOUNT";
        $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
        $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
        $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
        $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
        //$sql.=" INNER JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
        $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
        $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
        $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
        $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
        $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
        $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
        $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
        $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.SERVICE_TYPE=" . $service . " ORDER BY RES.RESERVATION_ID DESC ";



        if ($retailer != -1) {
            $sql .= " AND LO.RETAILER_ID ='" . $retailer . "'";
        }
        if ($employee != -1) {
            $sql .= " AND RES.EMPLOYEE ='" . $employee . "'";
        }

        if ($status != -1) {
            $sql .= " AND RES.STATUS ='" . $status . "'";
        }

        if ($opc == 2) {
            $sql .= " AND RES.DATE>='" . $from . "'";
            $sql .= " AND RES.DATE<='" . $to . "'";
        }

        if ($by == 1) {
            $sql .= " AND CONCAT( PE3.NAME,  ' ', PE3.LAST_NAME, ' ', US3.USER_NAME, ' ', PE3.EMAIL,' ', PE3.PHONE ) LIKE '%" . $txt . "%'";
        }

        if ($by == 2) {
            //$sql .= " AND (PE3.NAME LIKE '%" . $txt . "%'";
            //$sql .= " OR PE3.LAST_NAME LIKE '%" . $txt . "%')";

            $sql .= " AND CONCAT( PE.NAME,  ' ', PE.LAST_NAME, ' ', US2.USER_NAME, ' ', PE.EMAIL,' ', PE.PHONE ) LIKE '%" . $txt . "%'";
        }

        if ($by == 3) {
            $sql .= " AND (US.USER_NAME LIKE '%" . $txt . "%')";
        }
        //BY LOCATION
        if ($by == 4) {
            $sql .= " AND (LO.LOCATION_NAME LIKE '" . $txt . "%')";
        }



        error_log($sql);
        $db = new db();

        $db = $db->connect();

        $gsent = $db->prepare($sql);
        $gsent->execute();

        /* Obtener todas las filas restantes del conjunto de resultados */

        $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

        $cont = 0;
        foreach ($resultado as $valor) {

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
            $adds = CheckAdds($resultado2);
            $additionals = ["ADDITIONALS" => $adds, "TOTAL_ADDITIONAL" => $sum];
            $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
            $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
            $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);

            if ($resultado3) {
                $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LASTNAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
            } else {
                $res = array("EMPLOYEE_USER" => 'None', "EMPLOYEE_NAME" => 'None', "EMPLOYEE_LASTNAME" => 'None');
            }
            $resultado[$cont] = array_merge((array) $resultado[$cont], $res);

            $cont++;
        }
        $db = null;
        header("Access-Control-Allow-Origin: *");

        $dataArray = [
            "pages" => getPages($opc, $service, $from, $to, $by, $txt, $status, $retailer, $employee),
            "data" => $resultado,
            "adds" => getAdds(),
        ];
        return $response->withJson($dataArray);
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

function getPages($opc, $service, $from, $to, $by, $txt, $status, $retailer, $employee)
{

    try {

        $db = new db();
        $db = $db->connect();

        $sql = "SELECT ";
        $sql .= " COUNT(*) AS PAGES";
        $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
        $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
        $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
        $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
        $sql .= " LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
        $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
        $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
        $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
        $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
        $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
        $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
        $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
        $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
        $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.SERVICE_TYPE=" . $service;

        if ($retailer != -1) {
            $sql .= " AND LO.RETAILER_ID ='" . $retailer . "'";
        }
        if ($employee != -1) {
            $sql .= " AND RES.EMPLOYEE ='" . $employee . "'";
        }

        if ($status != -1) {
            $sql .= " AND RES.STATUS ='" . $status . "'";
        }

        if ($opc == 2) {
            $sql .= " AND RES.DATE>='" . $from . "'";
            $sql .= " AND RES.DATE<='" . $to . "'";
        }

        if ($by == 1) {
            $sql .= " AND CONCAT( PE3.NAME,  ' ', PE3.LAST_NAME, ' ', US3.USER_NAME, ' ', PE3.EMAIL,' ', PE3.PHONE ) LIKE '%" . $txt . "%'";
        }

        if ($by == 2) {
            $sql .= " AND CONCAT( PE.NAME,  ' ', PE.LAST_NAME, ' ', US2.USER_NAME, ' ', PE.EMAIL,' ', PE.PHONE ) LIKE '%" . $txt . "%'";
        }

        if ($by == 3) {
            $sql .= " AND (US.USER_NAME LIKE '%" . $txt . "%')";
        }

        //BY LOCATION
        if ($by == 4) {
            $sql .= " AND (LO.LOCATION_NAME LIKE '" . $txt . "%')";
        }

        $gsent = $db->prepare($sql);
        $gsent->execute();
        $res = $gsent->fetchAll(PDO::FETCH_OBJ);
        $pages = $res["0"]->PAGES;

        $pages = ceil($pages / 20);
        $db = null;

        return $pages;
    } catch (PDOException $e) {
        error_log(json_encode($e));
    }
}

function getAdds()
{

    $sql = "SELECT * FROM ADDITIONAL";

    try {

        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $adds = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;


        return $adds;
    } catch (PDOException $e) {
        error_log(json_encode($e));
    }
}

function CheckAdds($res_adds)
{

    $sql = "SELECT * FROM ADDITIONAL";

    try {

        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $adds = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $array = [];
        foreach ($adds as &$valor1) {
            $flag = false;

            foreach ($res_adds as &$valor2) {
                if ($valor1->ADDITIONAL_ID == $valor2->ADDITIONAL_ID) {
                    $flag = true;
                }
            }
            if ($flag) {
                $valor1->CHECK = true;
            } else {
                $valor1->CHECK = false;
            }
            array_push($array, $valor1);
        }

        return $array;
    } catch (PDOException $e) {
        error_log(json_encode($e));
    }
}



$app->get('/getUpcomingReservations', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $quantity = $request->getQueryParam('quantity');
    $retailer = $request->getQueryParam('retailer');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $sql = "SELECT ";
        $sql .= "RES.RESERVATION_ID,";
        $sql .= "RES.SERVICE_TYPE,";
        $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
        $sql .= "RES.STATUS,";
        $sql .= "SCHE.HOUR_12,";
        $sql .= "US.USER_NAME AS RETAILER,";
        $sql .= "LO.LOCATION_NAME AS LOCATION,";
        $sql .= "SER.SERVICE_NAME,";
        $sql .= "US2.USER_NAME AS EMPLOYEE,";
        $sql .= "RES.EMPLOYEE,";
        $sql .= "PE.NAME AS EMPLOYEE_NAME,";
        $sql .= "PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
        $sql .= "PE3.NAME AS CUSTOMER_NAME,";
        $sql .= "PE3.LAST_NAME AS CUSTOMER_LASTNAME,";
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
        $sql .= "RES.ADDRESS,";
        $sql .= "PROM.CODE,";
        $sql .= "RES.PROMO_DISCOUNT,";
        $sql .= "SER.SERVICE_CATEGORY";
        $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
        $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
        $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
        $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
        $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
        $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
        $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
        $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
        $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
        $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
        $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
        $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
        $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
        $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID ";

        if ($retailer > -1) {
            $sql .= " WHERE LO.RETAILER_ID=" . $retailer;
        }

        $sql .= " ORDER BY RES.RESERVATION_ID DESC LIMIT " . $quantity;
        error_log($sql);
        $db = new db();

        $db = $db->connect();

        $gsent = $db->prepare($sql);
        $gsent->execute();
        $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

        $cont = 0;
        foreach ($resultado as $valor) {

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
            $adds = CheckAdds($resultado2);
            $additionals = ["ADDITIONALS" => $adds, "TOTAL_ADDITIONAL" => $sum];
            $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
            $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
            $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);

            if ($resultado3) {
                $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LASTNAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
            } else {
                $res = array("EMPLOYEE_USER" => 'None', "EMPLOYEE_NAME" => 'None', "EMPLOYEE_LASTNAME" => 'None');
            }
            $resultado[$cont] = array_merge((array) $resultado[$cont], $res);

            $cont++;
        }

        $db = null;
        header("Access-Control-Allow-Origin: *");

        $dataArray = [
            "data" => $resultado,
            "adds" => getAdds(),
        ];
        return $response->withJson(utf8ize($dataArray));
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


$app->get('/getReservationEvents', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');
    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT ";
    $sql .= "RES.RESERVATION_ID,";
    $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
    $sql .= "RES.STATUS,";
    $sql .= "SCHE.HOUR,";
    $sql .= "LO.LOCATION_NAME AS LOCATION,";
    $sql .= "SER.SERVICE_NAME";
    $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
    $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
    $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
    $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
    $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE (RES.STATUS=1 OR RES.STATUS=2) AND YEAR(RES.DATE)>=YEAR(NOW())";

    if ($location > -1) {
        $sql .= " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer > -1) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
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


$app->get('/getReservationEmployeeEvents', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');
    $employee = $request->getQueryParam('employee');
    $retailer = $request->getQueryParam('retailer');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT ";
    $sql .= "RES.RESERVATION_ID,";
    $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
    $sql .= "RES.STATUS,";
    $sql .= "SCHE.HOUR,";
    $sql .= "LO.LOCATION_NAME AS LOCATION,";
    $sql .= "SER.SERVICE_NAME";
    $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
    $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
    $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
    $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
    $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.STATUS=1  AND YEAR(RES.DATE)=YEAR(NOW())";

    if ($location > -1) {
        $sql .= " AND LO.LOCATION_ID=" . $location;
    }
    if ($retailer != -1) {
        $sql .= " AND LO.RETAILER_ID=" . $retailer;
    }
    if ($employee != -1) {
        $sql .= " AND LO.RETAILER_ID IN (SELECT RETAILER_ID FROM EMPLOYEE where EMPLOYEE_ID=" . $employee . ") ORDER BY RES.LOCATION_ID DESC";
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


$app->get('/getReservationEmployeeEventsApp', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    //2020-02-16
    $sql = "SELECT DATE_FORMAT(DATE, '%Y-%m-%d') as DATE";
    $sql .= " FROM RESERVATION";
    $sql .= " WHERE STATUS=1 AND  YEAR(DATE)=YEAR(NOW()) AND SERVICE_TYPE=2 AND  LOCATION_ID=" . $location;



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

$app->get('/getReservationEmployeeEventsDate', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $location = $request->getQueryParam('location');
    $date = $request->getQueryParam('date');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    $sql = "SELECT ";
    $sql .= "RES.RESERVATION_ID,";
    $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
    $sql .= "RES.STATUS,";
    $sql .= "CM.MAKE_NAME,";
    $sql .= "CMD.MODEL_NAME,";
    $sql .= "VD.YEAR,";
    $sql .= "VD.COLOR,";
    $sql .= "VD.PLATE,";
    $sql .= "SCHE.HOUR_12,";
    $sql .= "LO.LOCATION_NAME AS LOCATION,";
    $sql .= "SER.SERVICE_NAME";
    $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
    $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
    $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
    $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
    $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
    $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
    $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
    $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.STATUS=1";
    $sql .= " AND LO.LOCATION_ID=$location";
    $sql .= " AND RES.DATE='$date'";


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


$app->get('/getEmployeePendings', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();

    $retailer = $request->getQueryParam('retailer');

    $employee = $request->getQueryParam('employeeId');

    $page = $request->getQueryParam('page');
    $page = ((int) $page) * 20;

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        //$decoded = GetTokenData($jwt['HTTP_AUTHORIZATION'][0]);
        $sql = "SELECT ";
        $sql .= "RES.RESERVATION_ID,";
        $sql .= "RES.SERVICE_TYPE,";
        $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
        $sql .= "RES.STATUS,";
        $sql .= "SCHE.HOUR,";
        $sql .= "US.USER_NAME AS RETAILER,";
        $sql .= "LO.LOCATION_NAME AS LOCATION,";
        $sql .= "SER.SERVICE_NAME,";
        $sql .= "US2.USER_NAME AS EMPLOYEE,";
        $sql .= "RES.EMPLOYEE,";
        $sql .= "PE.NAME AS EMPLOYEE_NAME,";
        $sql .= "PE.LAST_NAME AS EMPLOYEE_LASTNAME,";
        $sql .= "PE3.NAME AS CUSTOMER_NAME,";
        $sql .= "PE3.LAST_NAME AS CUSTOMER_LASTNAME,";
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
        $sql .= "RES.ADDRESS,";
        $sql .= "PROM.CODE,";
        $sql .= "RES.PROMO_DISCOUNT";
        $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
        $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
        $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
        $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
        $sql .= " LEFT JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
        $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
        $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
        $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
        $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
        $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
        $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
        $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
        $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
        $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.STATUS=2";


        if ($retailer != -1) {
            $sql .= " AND LO.RETAILER_ID ='" . $retailer . "'";
        }
        if ($employee != -1) {
            $sql .= " AND RES.EMPLOYEE ='" . $employee . "'";
        }


        $sql .= " ORDER BY RES.RESERVATION_ID DESC LIMIT " . $page . ",20";

        error_log($sql);
        $db = new db();

        $db = $db->connect();

        $gsent = $db->prepare($sql);
        $gsent->execute();
        $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);
        error_log('primero\n');
        error_log(print_r($resultado, true));
        error_log('segundo\n');

        $cont = 0;
        foreach ($resultado as $valor) {

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
            $adds = CheckAdds($resultado2);
            $additionals = ["ADDITIONALS" => $adds, "TOTAL_ADDITIONAL" => $sum];
            $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
            error_log(print_r($resultado, true));
            $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
            $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);

            $cont++;
        }

        $db = null;
        header("Access-Control-Allow-Origin: *");

        $dataArray = [
            "pages" => 1,
            "data" => $resultado,
            "adds" => getAdds(),
        ];
        return $response->withJson(utf8ize($dataArray));
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getReservationInfo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $reservation = $request->getQueryParam('reservation');


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $sql = "SELECT ";
        $sql .= "RES.RESERVATION_ID,";
        $sql .= "RES.SERVICE_TYPE,";
        $sql .= "DATE_FORMAT(RES.DATE, '%m/%d/%Y') as DATE,";
        $sql .= "RES.STATUS,";
        $sql .= "SCHE.HOUR_12,";
        $sql .= "US.USER_NAME AS RETAILER,";
        $sql .= "LO.LOCATION_NAME AS LOCATION,";
        $sql .= "LO.LOCATION_ID,";
        $sql .= "SER.SERVICE_NAME,";
        $sql .= "US2.USER_NAME AS EMPLOYEE,";
        $sql .= "RES.EMPLOYEE,";
        $sql .= "PE.NAME AS EMPLOYEE_NAME,";
        $sql .= "PE.LAST_NAME AS EMPLOYEE_LAST_NAME,";
        $sql .= "PE3.NAME AS CUSTOMER_NAME,";
        $sql .= "PE3.LAST_NAME AS CUSTOMER_LASTNAME,";
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
        $sql .= "RES.ADDRESS,";
        $sql .= "PROM.CODE,";
        $sql .= "RES.PROMO_DISCOUNT,";
        $sql .= "SER.SERVICE_CATEGORY,";
        $sql .= "RES.LAT,";
        $sql .= "RES.LNG";
        $sql .= " FROM RESERVATION RES INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID";
        $sql .= " INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID";
        $sql .= " LEFT JOIN PROMO PROM ON PROM.ID=RES.PROMO_ID";
        $sql .= " INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID";
        $sql .= " LEFT JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID";
        $sql .= " LEFT JOIN PERSON PE ON PE.PERSON_ID=US2.PERSON_ID ";
        $sql .= " INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID";
        $sql .= " INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID";
        $sql .= " INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID";
        $sql .= " INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION";
        $sql .= " INNER JOIN VEHICLE_DETAIL VD ON VD.VHE_DETAIL=RES.VEHICLE_DETAIL";
        $sql .= " INNER JOIN CAR_MAKE CM ON CM.MAKE_ID=VD.MAKE";
        $sql .= " INNER JOIN CAR_MODEL CMD ON CMD.MODEL_ID=VD.MODEL";
        $sql .= " INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RES.RESERVATION_ID=" . $reservation;



        error_log($sql);
        $db = new db();

        $db = $db->connect();

        $gsent = $db->prepare($sql);
        $gsent->execute();
        $resultado = $gsent->fetchAll(PDO::FETCH_OBJ);

        $cont = 0;
        foreach ($resultado as $valor) {

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
            $adds = CheckAdds($resultado2);
            $additionals = ["ADDITIONALS" => $adds, "TOTAL_ADDITIONAL" => $sum];
            $resultado[$cont] = array_merge((array) $resultado[$cont], $additionals);
            $emp = empty($valor->EMPLOYEE) == true ? 0 : $valor->EMPLOYEE;
            $sql = "SELECT US.USER_NAME AS EMPLOYEE,PE.NAME AS EMPLOYEE_NAME,PE.LAST_NAME AS EMPLOYEE_LAST_NAME FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE US.USER_ID=" . $emp;

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $resultado3 = $gsent->fetchAll(PDO::FETCH_OBJ);

            if ($resultado3) {
                $res = array("EMPLOYEE_USER" => $resultado3[0]->EMPLOYEE, "EMPLOYEE_NAME" => $resultado3[0]->EMPLOYEE_NAME, "EMPLOYEE_LASTNAME" => $resultado3[0]->EMPLOYEE_LAST_NAME);
            } else {
                $res = array("EMPLOYEE_USER" => 'None', "EMPLOYEE_NAME" => 'None', "EMPLOYEE_LASTNAME" => 'None');
            }
            $resultado[$cont] = array_merge((array) $resultado[$cont], $res);

            $cont++;
        }

        $db = null;
        header("Access-Control-Allow-Origin: *");

        $dataArray = [
            "data" => $resultado,
            "adds" => getAdds(),
        ];
        return $response->withJson(utf8ize($dataArray));
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});



//TAKE
$app->post('/take', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        try {
            $db = new db();
            $db = $db->connect();
            $sql = "SELECT STATUS FROM RESERVATION WHERE RESERVATION_ID='" . $dat['reservation'] . "'";

            $gsent = $db->prepare($sql);
            $gsent->execute();
            $exist = $gsent->fetchAll(PDO::FETCH_OBJ);

            if (($exist["0"]->STATUS) != 1) {
                $ret = ["error" => 1, "text" => "This reservation has already been taken"];

                header("Access-Control-Allow-Origin: *");
                return $response->withJson($ret);
            }

            $sql = "UPDATE RESERVATION SET STATUS=2,EMPLOYEE=" . $dat['employee'] . " WHERE RESERVATION_ID=" . $dat['reservation'];
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $db = null;
            $ret = ["error" => 0, "text" => "Reservation taken"];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});
//done
$app->post('/done', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
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
            $push=[];
            if ($token != '') {
                if ($type == 2 || $type == '2') {
                    sendPushTip2($token, $dat['reservation']);
                   
                } else {
                    sendPushTip($token, $dat['reservation']);
                }
            }

            $db = null;
            sendCompleted($dat['reservation'], $trans);

            $ret = ["error" => 0,"token"=>$token];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (Exception $e) {
            return $response->withJson($e);
        }
    }


    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


//UPDATE RETAILER PAID
$app->post('/updateRetailerPaid', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $dat = $request->getParsedBody();

        try {

            $db = new db();
            $db = $db->connect();
            $sql = "UPDATE RESERVATION SET STATUS=5 WHERE  RESERVATION_ID=" . $dat["RESERVATION_ID"];
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

//CANCEL RESERVATION
$app->post('/cancelReservation', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();


    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    if ($valid) {
        $dat = $request->getParsedBody();

        try {

            $db = new db();
            $db = $db->connect();
            $sql = "UPDATE RESERVATION SET STATUS=4 WHERE  RESERVATION_ID=" . $dat["reservation"];
            $gsent = $db->prepare($sql);
            $gsent->execute();

            $sql = "INSERT INTO HISTORY(USER_FK,DESCRIPTION,RESERVATION_FK) VALUES (:USER,:DESCRIPTION,:RESERVATION)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("USER", $dat['user']);
            $stmt->bindParam("DESCRIPTION", $dat['description']);
            $stmt->bindParam("RESERVATION", $dat['reservation']);

            $stmt->execute();

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
