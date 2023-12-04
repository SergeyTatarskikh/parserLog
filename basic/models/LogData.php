<?php

namespace app\models;

use yii\db\ActiveRecord;

class LogData extends ActiveRecord
{
    public static function tableName()
    {
        return 'logs'; // Имя таблицы в базе данных, где будут храниться данные логов
    }

    public function rules()
    {
        return [
            [['ip', 'timedate', 'url', 'os', 'architecture', 'browser'], 'string', 'max' => 500],
        ];
    }

}
