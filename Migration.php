<?php

namespace maybeworks\libs;

/**
 * Class Migration
 * @property $tableName string
 * @property $tableOptions string
 */
class Migration extends \yii\db\Migration {

    public function getTableName(){
        return '';
    }

    public function getTableOptions(){
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $tableOptions;
    }

}