<?php

/**
 * AUTOR:EDGAR VALDEZ
 */
class db
{
  //propiedades

  private $dbhost='localhost';
  private $dbuser="javier_2019";
  private $dbpass="Qwerty1234";
  private $dbname="javier_carwash";
  //Coneccion
  public function connect(){
    $mysql_connect_str="mysql:host=$this->dbhost;dbname=$this->dbname;charset=UTF8";
    $dbConnection=new PDO($mysql_connect_str,$this->dbuser,$this->dbpass);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
     return $dbConnection;
  }

}
