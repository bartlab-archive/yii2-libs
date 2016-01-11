<?php

namespace maybeworks\libs;

use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ProcessFilterEvent extends Event {

	/**
	 * @var array
	 */
	public $params;

	/**
	 * @var ActiveQuery
	 */
	public $query;

	/**
	 * @var ActiveDataProvider
	 */
	public $dataProvider;
}
