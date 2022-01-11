
<?php
class Database{
    const DB_HOSTNAME = 'localhost';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = 'password';
    const DB_NAME = 'shop';
    protected $_db_connect;
    protected $_sql;
    protected $_result;
    protected $_row;

    function db_connect(){
        $this->_db_connect = mysql_connect(self::DB_HOSTNAME,self::DB_USERNAME,self::DB_PASSWORD) or die(mysql_error());
    }

    function slect_db(){
        mysql_select_db(self::DB_NAME) or die(mysql_error());
    }

    function sql(){
        $this->_sql = 'SELECT * FROM users';
    }

    function query(){
        $this->_result = mysql_query($this->_sql);
    }

    function fetch_array(){
        while($this->_row = mysql_fetch_array($this->_result)){
            $username = $this->_row['user_USERNAME'];

            echo "<ul>";
                echo "<li>".$username."</li>";
            echo "</ul>";
        }
    }

    function db_close(){
        mysql_close($this->_db_connect);
    }
}

$database = new Database();
$database->db_connect();
$database->slect_db();
$database->sql();
$database->query();
$database->fetch_array();
$database->db_close();
?>