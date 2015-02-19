<?php
/**
 *
 * Author: terrasoff
 * Email: terrasoff@terrasoff.ru
 * Skype: tarasov.konstantin
 */

namespace terrasoff\yii\components;

use CApplicationComponent;
use CDbConnection;
use Exception;
use Yii;

class MysqlHelperComponent extends CApplicationComponent
{
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @var string идентификатор компонента для соединения с БД
     */
    public $connectionId = 'db';

    /**
     * Компонент для соединения с БД, соответствующее идентификатору
     *
     * @var CDbConnection|null
     */
    public $connection = null;

    /**
     * @param CDbConnection $connection
     * @return $this
     */
    public function setConnection(CDbConnection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return CDbConnection
     *
     * @throws Exception
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = Yii::app()->{$this->connectionId};
            if ($this->connection === null) {
                throw new Exception(sprintf("Wrong database connection %s", $this->connectionId));
            }
        }

        return $this->connection;
    }

    public static function getConnectionConfig(CDbConnection $connection, $param)
    {
        preg_match("/{$param}=([^;|^$]+)/", $connection->connectionString, $matches);

        return $matches[1];
    }

    /**
     * Сохраняем состояние БД
     *
     * @return string|false имя файла с бэкапом в случае удачного сохранения
     */
    public function backup($filename = null)
    {
        $connection = $this->getConnection();
        $host = self::getConnectionConfig($connection, 'host');
        $dbname = self::getConnectionConfig($connection, 'dbname');

        if ($filename === null) {
            $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dbname . date("Y-m-d-H-i-s") . '.sql';
        }

        $command = sprintf("mysqldump -h%s -u%s -p%s '%s' > %s",
            $host,
            $connection->username,
            $connection->password,
            $dbname,
            $filename
        );

        $result = system($command);

        return $result
            ? $filename
            : false;
    }

    /**
     * Восстанавливаем сохраненое состояние БД
     *
     * @param $filename string имя файла с бэкапом
     *
     * @return string|false
     */
    public function restore($filename)
    {
        $connection = $this->getConnection();
        $host = self::getConnectionConfig($connection, 'host');
        $dbname = self::getConnectionConfig($connection, 'dbname');
        
        $command = sprintf("mysql -h%s -u%s -p%s '%s' < %s",
            $host,
            $connection->username,
            $connection->password,
            $dbname,
            $filename
        );

        return system($command);
    }
}