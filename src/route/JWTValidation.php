<?php

use \Firebase\JWT\JWT;


function CheckToken($token){
    if(!$token){
        return true;
    }
    $key = 'myezcarwashsecretkey2019';
    $val=true;
    try {
       // $decoded = JWT::decode($token, $key, array('HS256'));
        
      /*  if(!$decoded->date){
            $val=false;
        }*/
       
    } catch (UnexpectedValueException $e) {
        error_log($e->getMessage());
        $val=false;
    }


    return true;
}

function GetTokenData($token){
    $key = 'myezcarwashsecretkey2019';
    $val=true;
    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
    } catch (UnexpectedValueException $e) {
        echo $e->getMessage();
    }
    return $decoded;
}