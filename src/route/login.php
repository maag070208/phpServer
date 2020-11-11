<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
date_default_timezone_set('America/Mexico_City');


$app->post('/login', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $userName = $dat['userName'];
    $pass = $dat['pass'];

    
    $sql = "SELECT US.USER_ID,PER.EMAIL,US.USER_TYPE,US.USER_NAME FROM USER US LEFT JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID  WHERE US.STATUS=1 AND US.USER_TYPE=1  AND (US.USER_NAME='" . $userName . "' OR PER.EMAIL='" . $userName . "')  AND PASS='" . $pass . "'";
    error_log($sql);
    try {

        $db = new db();

        $db = $db->connect();

        $stmt = $db->query($sql);
        $user = $stmt->fetchObject();

        // verify email address.
        if (!$user) {
            $db = null;
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match.']);
        } else {
            $key = "myezcarwashsecretkey2019";

            $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); //the expiration date will be in one hour from the current moment
            $data = [
                "date"=>date("Y/m/d"),
                "type" => $user->USER_TYPE,
                "id"    => $user->USER_ID,
                "email" => $user->EMAIL
            ];
            
            $token = JWT::encode($data, $key, "HS256");

            $sql = "UPDATE USER SET TOKEN_EXPIRATE='" . $tokenExpiration . "' WHERE USER_NAME='" . $userName . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $db = null;


            header("Access-Control-Allow-Origin: *");
            return $this->response->withJson(['error' => false, 'token' => $token, 'type' => $user->USER_TYPE,"id"=>$user->USER_ID,'user_name'=>$user->USER_NAME]);
        }
    } catch (PDOException $e) {
        header("Access-Control-Allow-Origin: *");
        echo json_encode($e);
    }
});


$app->post('/loginRetailer', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $userName = $dat['userName'];
    $pass = $dat['pass'];

    
    $sql = "SELECT US.USER_ID,PER.EMAIL,US.USER_TYPE,US.USER_NAME FROM USER US LEFT JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID  WHERE US.STATUS=1 AND US.USER_TYPE=2  AND (US.USER_NAME='" . $userName . "' OR PER.EMAIL='" . $userName . "')  AND PASS='" . $pass . "'";
    error_log($sql);
    try {

        $db = new db();

        $db = $db->connect();

        $stmt = $db->query($sql);
        $user = $stmt->fetchObject();

        // verify email address.
        if (!$user) {
            $db = null;
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match.']);
        } else {
            $key = "myezcarwashsecretkey2019";

            $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); //the expiration date will be in one hour from the current moment
            $data = [
                "date"=>date("Y/m/d"),
                "type" => $user->USER_TYPE,
                "id"    => $user->USER_ID,
                "email" => $user->EMAIL
            ];
            
            $token = JWT::encode($data, $key, "HS256");

            $sql = "UPDATE USER SET TOKEN_EXPIRATE='" . $tokenExpiration . "' WHERE USER_NAME='" . $userName . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $db = null;


            header("Access-Control-Allow-Origin: *");
            return $this->response->withJson(['error' => false, 'token' => $token, 'type' => $user->USER_TYPE,"id"=>$user->USER_ID,'user_name'=>$user->USER_NAME]);
        }
    } catch (PDOException $e) {
        header("Access-Control-Allow-Origin: *");
        echo json_encode($e);
    }
});



$app->post('/loginEmployee', function (Request $request, Response $response, array $args) {
    $dat = $request->getParsedBody();
    $userName = $dat['userName'];
    $pass = $dat['pass'];

    
    $sql = "SELECT US.USER_ID,PER.EMAIL,US.USER_TYPE,US.USER_NAME FROM USER US LEFT JOIN PERSON PER ON PER.PERSON_ID=US.PERSON_ID  WHERE US.STATUS=1 AND (USER_TYPE=4 OR USER_TYPE=2) AND (US.USER_NAME='" . $userName . "' OR PER.EMAIL='" . $userName . "')  AND PASS='" . $pass . "'";
    error_log($sql);
    try {

        $db = new db();

        $db = $db->connect();

        $stmt = $db->query($sql);
        $user = $stmt->fetchObject();

        // verify email address.
        if (!$user) {
            $db = null;
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match.']);
        } else {
            $key = "myezcarwashsecretkey2019";

            $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); //the expiration date will be in one hour from the current moment
            $data = [
                "date"=>date("Y/m/d"),
                "type" => $user->USER_TYPE,
                "id"    => $user->USER_ID,
                "email" => $user->EMAIL
            ];
            
            $token = JWT::encode($data, $key, "HS256");

            $sql = "UPDATE USER SET TOKEN_EXPIRATE='" . $tokenExpiration . "' WHERE USER_NAME='" . $userName . "'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $db = null;


            header("Access-Control-Allow-Origin: *");
            return $this->response->withJson(['error' => false, 'token' => $token, 'type' => $user->USER_TYPE,"id"=>$user->USER_ID,'user_name'=>$user->USER_NAME]);
        }
    } catch (PDOException $e) {
        header("Access-Control-Allow-Origin: *");
        echo json_encode($e);
    }
});
