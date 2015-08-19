<?php

namespace maybeworks\libs;

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