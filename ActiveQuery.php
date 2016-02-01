<?php

namespace maybeworks\libs;

use yii\helpers\ArrayHelper;

class ActiveQuery extends \yii\db\ActiveQuery
{
    public function lists($key, $value, $group = null)
    {
        return ArrayHelper::map($this->all(), $key, $value, $group);
    }
}