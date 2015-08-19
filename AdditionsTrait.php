<?php

namespace maybeworks\libs;

//use yii\db\ActiveRecord;

/**
 * Class AdditionsTrait
 *
 * @property array $copyUnset
 * @package maybeworks\libs
 */
trait AdditionsTrait {

	/**
	 * Создание копии записи на основе существующей
	 * @return ActiveRecord
	 */
	public function getCopy() {
		/**
		 * @var $this ActiveRecord
		 */
		$new = static::getItem();

		$new->setAttributes($this->attributes, false);

		// удаляем поля, которых не должно быть у новой записи
		foreach (array_merge($new::primaryKey(),$this->copyUnset) as $field) {
			if (isset($new[$field])) {
				unset($new[$field]);
			}
		}

		return $new;
	}

	public function getCopyUnset(){
		return [];
	}

	/**
	 * Получить запись по id или создать новый экземпляр класса АР (если id = null)
	 * @param $id [опционально] id записи (по умолчанию null)
	 * @return ActiveRecord если указан id, но запись не найдена, вернет null
	 */
	public static function getItem($id = null) {
		if ($id) {
			$item = static::findOne($id);
			$scenario = 'update';
		} else {
			$item = new static();
			$scenario = 'insert';
		}

		// если есть сценарий insert/update, применяем его
		if ($item && array_key_exists($scenario, $item->scenarios())) {
			$item->setScenario($scenario);
		}

		return $item;
	}

	public static function findOrCreate($arguments = [], $save = false){
		/** @var $model ActiveRecord */
		if($model = self::find()->where($arguments)->one()){
			return $model;
		}


		$model = new static();
		if ($model && array_key_exists('insert', $model->scenarios())) {
			$model->setScenario('insert');
		}
		$model->setAttributes($arguments);

		if($save){
			$model->save();
		}

		return $model;
	}
}