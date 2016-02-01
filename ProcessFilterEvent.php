<?php

namespace maybeworks\libs;

use yii\base\Event;
use yii\data\ActiveDataProvider;

class ProcessFilterEvent extends Event
{
    /**
     * @var array
     */
    public $params;

    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

    /**
     * @var ActiveDataProvider
     */
    public $dataProvider;
}
