<?php

namespace common\db;

use maybeworks\libs\AdditionsTrait;
use maybeworks\libs\SearchTrait;
use Yii;

class ActiveRecord extends \yii\db\ActiveRecord {
    use AdditionsTrait, SearchTrait;

    public function init() {
		parent::init();
        $this->searchInit();
    }

    public static function find() {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

}