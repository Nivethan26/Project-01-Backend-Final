<?php


class DbConnector {
    
    private $host="localhost";
    private $dbname="employee";
     private $dbuser="root";
      private $dbpwd="";
      
      
      
      public function getConnection()
      {
          $dsn="mysql:host=".$this->host.";dbname=".$this->dbname;
          try {
              $con=new PDO($dsn, $this->dbuser, $this->dbpwd);
              $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          } catch (PDOException $exc) {
              die('error'.$exc->getMessage());
          }
            
    
    
    return $con;
    
}
}