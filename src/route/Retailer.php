<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;


$container = $app->getContainer();
$container['upload_directory'] = '../logos/';

$app->post('/upload_logo', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $directory = $this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();
    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['logo'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);

        try {
            $db = new db();

            $db = $db->connect();
            //search last path for delete
            $sql = "SELECT RETAILER_LOGO  FROM USER WHERE USER_ID=" . $data['id'];
            $stmt1 = $db->query($sql);
            $last_path = $stmt1->fetchAll(PDO::FETCH_OBJ);

            //update file path
            $sql = "UPDATE USER SET RETAILER_LOGO=:logo where USER_ID=:id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $data['id']);
            $stmt->bindParam("logo", $filename);
            $stmt->execute();
            //if last_path exist delete
            if (isset($last_path["0"]->RETAILER_LOGO)) {
                $file_pointer = "../logos/" . $last_path["0"]->RETAILER_LOGO;
                error_log($filename);
                if (!unlink($file_pointer)) {
                    error_log("$file_pointer cannot be deleted due to an error");
                } else {
                    error_log("$file_pointer has been deleted");
                }
            }
            $db = null;
            $ret = ["error" => 0, "text" => "Successful update", "logo" => $filename];

            return $response->withJson($ret);
        } catch (Exception $e) {
            $ret = ["error" => 1, "message" => $e->getMessage()];
            return $response->withJson($ret);
        }
        return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
    }
});


$app->post('/upload_logo2', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    //$directory = $this->get('upload_directory');
    //$uploadedFiles = $request->getUploadedFiles();
    // handle single input with single file upload
    //$uploadedFile = $uploadedFiles['logo'];
    try {
        echo "inicio";
        $file_name = $_FILES['logo']['name'];
        $dir_subida = '../logos/';
        $basename = rand(1000, 1000000)  . $file_name;
        echo "todo bien";
        $fichero_subido = $dir_subida . $basename;
        move_uploaded_file($_FILES['logo']['tmp_name'], $fichero_subido);
        echo "todo bien2";
        /*
        $db = new db();

        $db = $db->connect();
        //search last path for delete
        $sql = "SELECT RETAILER_LOGO  FROM USER WHERE USER_ID=" . $data['id'];
        $stmt1 = $db->query($sql);
        $last_path = $stmt1->fetchAll(PDO::FETCH_OBJ);

        //update file path
        $sql = "UPDATE USER SET RETAILER_LOGO=:logo where USER_ID=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $data['id']);
        $stmt->bindParam("logo", $basename);
        $stmt->execute();
        //if last_path exist delete
        if (isset($last_path["0"]->RETAILER_LOGO)) {
            $file_pointer = "../logos/" . $last_path["0"]->RETAILER_LOGO;
            if (!unlink($file_pointer)) {
                error_log("$file_pointer cannot be deleted due to an error");
            } else {
                error_log("$file_pointer has been deleted");
            }
        }
        $db = null;
        $ret = ["error" => 0, "text" => "Successful update", "logo" => $basename];

        return $response->withJson($ret);
        */
    } catch (Exception $e) {
        echo $e->getMessage();
        $ret = ["error" => 1, "message" => $e->getMessage()];
        return $response->withJson($ret);
    }
    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});


function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
   // $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = rand(1000, 1000000); // see http://php.net/manual/en/function.random-bytes.php
    $filename =  $basename.$uploadedFile->getClientFilename();

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}



$app->get('/getAllRetailers', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $page = $request->getQueryParam('page');
    $txt = $request->getQueryParam('txt');
    $page = ((int) $page) * 20;
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT *  FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_TYPE=2 AND STATUS=1";

    if ($txt != "") {
        $sql .= " AND CONCAT( PE.NAME,  ' ', PE.LAST_NAME, ' ', US.USER_NAME, ' ', PE.EMAIL,' ', PE.PHONE ) LIKE '%" . $txt . "%'";
    }
    error_log($txt);
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
            $db = null;
            $n = count($pages);
            error_log($n);
            $ret = ["data" => $data, "pages" => ceil($n / 20)];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            header("Access-Control-Allow-Origin: *");
            $ret = ["error" => 1, "text" => $e->getMessage()];

            return $response->withJson($ret);
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getRetailerItems', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT US.USER_NAME AS name, US.USER_ID AS value FROM USER US INNER JOIN PERSON PE ON PE.PERSON_ID=US.PERSON_ID WHERE USER_TYPE=2 AND STATUS=1";

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

$app->get('/getRetailerLogo', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $user_id = $request->getQueryParam('user_id');

    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT RETAILER_LOGO FROM USER WHERE USER_ID=".$user_id;

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

$app->post('/addRetailer', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $jwt = $request->getHeaders();
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);

    if ($valid) {
        try {

            $db = new db();

            $db = $db->connect();

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
            $stmt->bindParam("phone", $dat['directPhone']);
            $stmt->bindParam("companyName", $dat['companyName']);
            $stmt->bindParam("phone1", $dat['officePhone']);
            $stmt->bindParam("phone2", $dat['officePhone2']);
            $stmt->bindParam("fax", $dat['fax']);
            $stmt->bindParam("no", $dat['num']);
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
            $stmt->bindParam("id", $userID);
            $stmt->bindParam("userName", $dat['userName']);
            $stmt->bindParam("pass", $dat['pass']);
            $stmt->bindParam("type", $dat['userType']);
            $stmt->bindParam("personId", $idPerson);

            $stmt->execute();
            $db = null;
            $ret = ["user_id" => $userID, "error" => "0"];

            header("Access-Control-Allow-Origin: *");
            return $response->withJson($ret);
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }

    return $this->response->withJson(['valid' => false, 'message' => 'Your session expired']);
});

$app->get('/getNotRetailers', function (Request $request, Response $response, array $args) {
    $jwt = $request->getHeaders();
    $employee = $request->getQueryParam('employee');
    $valid = CheckToken($jwt['HTTP_AUTHORIZATION'][0]);
    $sql = "SELECT USER_ID,USER_NAME FROM USER WHERE USER_TYPE=2 AND STATUS=1 AND USER_ID NOT IN (SELECT RETAILER_ID FROM EMPLOYEE WHERE EMPLOYEE_ID=" . $employee . ")";

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
