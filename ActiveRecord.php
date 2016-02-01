<?php

namespace maybeworks\libs;

use Yii;

class ActiveRecord extends \yii\db\ActiveRecord implements SearchInterface
{
    use BootstrapTrait, AdditionsTrait, SearchTrait;

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }
}