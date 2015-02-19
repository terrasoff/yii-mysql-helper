<?php
/**
 * Author: terrasoff
 * Email: terrasoff@terrasoff.ru
 * Skype: tarasov.konstantin
 */

namespace terrasoff\yii\components;

use CApplicationComponent;
use CDbConnection;
use Exception;
use Yii;

/**
 * Set of mysql database routines
 *
 * Class MysqlHelperComponent
 * @package terrasoff\yii\components
 */
class MysqlHelperComponent extends CApplicationComponent
{
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @var string component identifier
     */
    public $connectionId = 'db';

    /**
     * Component to connect mysql database with identifier $connectionId
     *
     * @var CDbConnection|null
     */
    public $connection = null;

    /**
     * Set custom connection
     *
     * @param CDbConnection $connection
     * @return $this
     */
    public function setConnection(CDbConnection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Current connection
     *
     * @return CDbConnection
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

    /**
     * Get specified connection config by DSN connection string of component
     * For example,
     * <code>
     * self::getConnectionConfig($connection, 'host');
     * </code>
     *
     * @param CDbConnection $connection
     *
     * @param $param
     * @return mixed
     */
    public static function getConnectionConfig(CDbConnection $connection, $param)
    {
        preg_match("/{$param}=([^;|^$]+)/", $connection->connectionString, $matches);

        return $matches[1];
    }

    /**
     * Save database state into file
     *
     * @var $filename string имя файла с дампом
     *
     * @return string|false имя файла с бэкапом в случае удачного сохранения
     */
    public function backup($filename = null)
    {
        $connection = $this->getConnection();
        $host = self::getConnectionConfig($connection, 'host');
        $dbname = self::getConnectionConfig($connection, 'dbname');

        if ($filename === null) {
            $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
                $dbname . '-' . date("Y-m-d-H-i-s") . '.sql';
        }

        $command = sprintf("mysqldump -h%s -u%s -p%s '%s' > %s",
            $host,
            $connection->username,
            $connection->password,
            $dbname,
            $filename
        );

        $result = system($command);

        return $result !== false
            ? $filename
            : false;
    }

    /**
     * Restore database state from file
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