<?php

namespace maybeworks\libs;

use Yii;

class ActiveRecord extends \yii\db\ActiveRecord {
	use AdditionsTrait, SearchTrait;

	public function init() {
		parent::init();

		$class = $this;
		$traits = [];
		do {
			$traits = array_merge(class_uses($class), $traits);
		} while ($class = get_parent_class($class));

		foreach ($traits as $trait => $same) {
			$traits = array_merge(class_uses($trait), $traits);
		}

		foreach(array_unique($traits) as $trait){
			$rc = new \ReflectionClass($trait);
			$methodName = $rc->getShortName().'Init';
			if ($rc->hasMethod($methodName)){
				call_user_func([$this,$methodName]);
			}
		}
	}

	public static function find() {
		return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
	}

}