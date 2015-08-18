<?php

namespace maybeworks\libs;

use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class AdditionsTrait
 *
 * @property array $validators
 *
 * @package maybeworks\libs
 */
trait SearchTrait {

	/**
	 * Значение для поиска по всем текстовым аттрибутам или только в указанном filter_text_field
	 * @var string
	 */
	public $filterText;

	/**
	 * Имя атрибута, в котором нужно искать значение filter_text.
	 * Если не указано, поиск будет по всем текстовым аттрибутам.
	 * @var string
	 */
	public $filterTextField;

	/**
	 * Кол-во записей на страницу для дата провайдера
	 * @var bool|int
	 */
	public $pageSize = false;// = 20;

	public function searchInit() {
		$this->validators[] = Validator::createValidator(
			'default',
			$this,
			['filterText', 'filterTextField'],
			['on' => 'search']
		);

		$this->validators[] = Validator::createValidator(
			'default',
			$this,
			['pageSize'],
			['on' => 'search']
		);

		// todo: проверить корректность работы
		$this->validators[] = Validator::createValidator(
			'safe',
			$this,
			$this->filterLikeAttributes(),
			['on' => 'search']
		);

		$this->validators[] = Validator::createValidator(
			'safe',
			$this,
			$this->filterAttributes(),
			['on' => 'search']
		);
	}

	/**
	 * Имя класса дата-провайдера
	 * @return string
	 */
	public function dataProviderClassName() {
		return ActiveDataProvider::className();
	}

	/**
	 * Имя класса пагинатора
	 * @return string
	 */
	public function paginationClassName() {
		return Pagination::className();
	}

	/**
	 * Имя класса сортировки
	 * @return string
	 */
	public function sortClassName() {
		return Sort::className();
	}

	/**
	 * Получение DataProvider
	 *
	 * @param array $options [опционально] опции для DataProvider
	 *
	 * @return \yii\data\ActiveDataProvider
	 */
	/**
	 * @param array $options
	 *
	 * @return object
	 * @throws InvalidConfigException
	 */
	public function dataProvider($options = []) {

		if (!is_array($options)) {
			throw new InvalidConfigException('Object configuration must be an array');
		}

		$config = [
			'class' => isset($options['class']) ? $options['class'] : $this->dataProviderClassName(),
			'query' => isset($options['query']) ? $options['query'] : static::find(),
			'pagination' => isset($options['pagination']) ? $options['pagination'] : $this->pagination(),
			'sort' => isset($options['sort']) ? $options['sort'] : $this->sort(),
		];

		return \Yii::createObject($config);
	}

	/**
	 * Получения пагинатора
	 * @return Pagination
	 * @throws \yii\base\InvalidConfigException
	 */
	public function pagination() {
		return \Yii::createObject(
			[
				'class' => $this->paginationClassName(),
				'pageSize' => $this->pageSize,
				'defaultPageSize' => $this->pageSize,
			]
		);
	}

	/**
	 * Получения параметров сортировки
	 * @return Sort
	 */
	public function sort() {
		return \Yii::createObject(
			[
				'class' => $this->sortClassName(),
				'defaultOrder' => [
					'id' => SORT_ASC
				]
			]
		);
//		return [
//			'defaultOrder' => [
//				'id' => SORT_ASC
//			]
//		];
	}

	/**
	 * Массив аттрибутов для поиска через LIKE
	 * @return array
	 */
	public function filterLikeAttributes() {
		return [];
	}

	/**
	 * Массив аттрибутов для поиска с точным совпадением
	 * @return array
	 */
	public function filterAttributes() {
		return [];
	}

	/**
	 * Дополнительные операции с запросом
	 *
	 * @param $params Array
	 * @param $query Query
	 * @param $dataProvider ActiveDataProvider
	 */
	public function processFilterQuery($params, $query, $dataProvider) {
	}

	/**
	 * Прокси к методу search
	 *
	 * @param array $params массив значений
	 * @param bool|string $formName [опционально] имя формы для метода load
	 * @param array $options [опционально] дополнительные параметры
	 *
	 * @return ActiveDataProvider
	 */
	public static function forSearch($params = [], $formName = false, $options = []) {
		return (new static)->search($params, $formName, $options);
	}

	/**
	 * Выборка строк на основе полученных данных
	 *
	 * @param $params array массив значений, который будет передан в метод load
	 * @param bool|string $formName [опционально] имя формы для метода load
	 * @param array $options [опционально] дополнительные параметры
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params = [], $formName = false, $options = []) {
		/**
		 * @var $query Query
		 * @var $model ActiveRecord
		 */
		$model = $this;

		// если есть сценарий "search", применяем его
		if (array_key_exists('search', $model->scenarios())) {
			$model->setScenario('search');
		}

		// если имя формы не используем, в метод отправляем пустую строку
		$model->load($params, ($formName === false ? '' : $formName));

		/**
		 * Создаём DataProvider, указываем ему запрос, настраиваем пагинацию
		 */
		$dataProvider = $this->dataProvider($options);
		$query = $dataProvider->query;

		// загружаем и проверяем данные перед фильтрацией
		if ($model->validate()) {
			// точные совпадения в значениях
			foreach ($this->filterAttributes() as $attribute) {
				$query->andFilterWhere([$attribute => $model->{$attribute}]);
			}

			$like = $this->filterLikeAttributes();

			// ... AND LIKE
			foreach ($like as $attribute) {
				$query->andFilterWhere(['like', $attribute, $model->{$attribute}]);
			}

			// ... OR LIKE
			// фильтруем по тестовым данным
			if ($this->filterText != '' && !empty($like)) {
				$like = array_map(
					function ($value) {
						return $value . ' LIKE :text';
					},
					$like
				);
				$query->andWhere(
					$this->filterTextField == '' ? '(' . join(' OR ', $like) . ')' : $this->filterTextField . ' LIKE :text',
					[':text' => '%' . trim($this->filterText) . '%']
				);
			}

			// доп.операции
			$this->processFilterQuery($params, $query, $dataProvider);
		}

		return $dataProvider;
	}
}
