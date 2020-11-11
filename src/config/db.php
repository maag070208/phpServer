<?php

/**
 * AUTOR:EDGAR VALDEZ
 */
class db
{
  //propiedades
//Meta2019@
  private $dbhost='localhost';
  private $dbuser="rideadmi_javier";
  private $dbpass="Meta2019@";
  private $dbname="rideadmi_carwash";
  //Coneccion
  public function connect(){
    $mysql_connect_str="mysql:host=$this->dbhost;dbname=$this->dbname;charset=UTF8";
    $dbConnection=new PDO($mysql_connect_str,$this->dbuser,$this->dbpass);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
     return $dbConnection;
  }

}
