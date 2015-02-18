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

    public function init()
    {
        if (!($this->connection = Yii::app()->getComponent($this->connectionId))) {
            throw new Exception(sprintf("Wrong database connection %s", $this->connectionId));
        }

        parent::init();
    }

    /**
     * Сохраняем состояние БД
     *
     * @return string|false имя файла с бэкапом в случае удачного сохранения
     */
    public function backup($filename = null)
    {
        if ($filename === null) {
            $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->dbname . date("Y-m-d-H-i-s") . '.sql';
        }

        $command = sprintf("mysqldump -h%s -u%s -p%s '%s' > %s",
            $this->connection->host,
            $this->connection->username,
            $this->connection->password,
            $this->connection->dbname,
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
     * @param string имя файла с бэкапом
     * @return string|false
     */
    public function restore($filename)
    {
        $command = sprintf("mysql -h%s -u%s -p%s '%s' < %s",
            $this->connection->host,
            $this->connection->username,
            $this->connection->password,
            $this->connection->dbname,
            $filename
        );

        return system($command);
    }
}