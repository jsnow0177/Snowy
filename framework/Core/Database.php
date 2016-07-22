<?php
namespace Snowy\Core;
use Snowy\Core\Classes\Singleton;

/**
 * Class Database
 * @package Snowy\Core
 */
final class Database extends Singleton{

    const MYSQL_DSN = "mysql:dbname=%s;host=%s";

    private $connection_configs = [];

    private $connections = [];

    /**
     * Конструктор
     */
    protected function __construct(){
        $config = Config::instance();
        $mysqlConnectionConfigs = $config->get("mysql", []);
        $necessaryKeys = ["host", "user", "pass", "db"];
        if(count($mysqlConnectionConfigs)>0){
            $toDelete = [];
            foreach($mysqlConnectionConfigs as $name=>$config){
                foreach($necessaryKeys as $k){
                    if(!isset($config[$k]))
                        $toDelete[] = $name;
                }
            }
            foreach($toDelete as $k)
                unset($mysqlConnectionConfigs[$k]);
        }

        $this->connection_configs = $mysqlConnectionConfigs;
    }

    /**
     * @param string $connName Имя соединения
     * @param string $host Хост базы данных
     * @param string $user Пользователь базы данных
     * @param string $pass Пароль пользователя базы данных
     * @param string $db База данных
     * @throws \InvalidArgumentException
     */
    public function addConnectionData($connName, $host = "", $user = "", $pass = "", $db = ""){
        if(isset($this->connection_configs[$connName]))
            throw new \InvalidArgumentException('Имя подключения уже используется', 0);
        $this->connection_configs[$connName] = [
            "host" => $host,
            "user" => $user,
            "pass" => $pass,
            "db" => $db
        ];
    }

    /**
     * Возвращает данные подключения
     * @param string $connName
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getConnectionData($connName){
        if(isset($this->connection_configs[$connName]))
            return $this->connection_configs[$connName];
        throw new \InvalidArgumentException("Соединение с именем " . $connName . " не найдено");
    }

    /**
     * Возвращает PDO-объект соединения с базой данных
     * @param string $connName
     * @throws \InvalidArgumentException
     * @return \PDO
     */
    public function getConnection($connName = ""){
        if($connName === "") $connName = "default";
        if(!isset($this->connection_configs[$connName]))
            throw new \InvalidArgumentException("Соединение с именем " . $connName . " не найдено");
        $connInfo = $this->getConnectionData($connName);
        if(!isset($this->connections[$connName])) {
            $dsn = sprintf(self::MYSQL_DSN, $connInfo['db'], $connInfo['host']);
            $this->connections[$connName] = new \PDO($dsn, $connInfo['user'], $connInfo['pass'], [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF-8'"
            ]);
        }

        return $this->connections[$connName];
    }

}
?>