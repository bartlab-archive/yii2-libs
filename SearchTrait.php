<?php

namespace maybeworks\libs;

use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\Query;
use yii\validators\Validator;

/**
 * Class AdditionsTrait
 * @package maybeworks\libs
 */
trait SearchTrait
{
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

    /**
     * Прокси к методу search
     *
     * @param array $params массив значений
     * @param bool|string $formName [опционально] имя формы для метода load
     * @param array $options [опционально] дополнительные параметры
     *
     * @return ActiveDataProvider
     */
    public static function forSearch($params = [], $formName = false, $options = [])
    {
        return (new static)->search($params, $formName, $options);
    }

    /**
     * Выборка строк на основе полученных данных
     *
     * @param array $params array массив значений, который будет передан в метод load
     * @param bool|string $formName [опционально] имя формы для метода load
     * @param array $options [опционально] дополнительные параметры
     *
     * @return ActiveDataProvider
     */
    public function search($params = [], $formName = false, $options = [])
    {
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
         * @var $dataProvider ActiveDataProvider
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
            if ($this->filterText && !empty($like)) {
                $filterText = is_array($this->filterText) ? $this->filterText : (array)$this->filterText;

                foreach ($filterText as $n => $ft) {
                    $query->andWhere(
                        $this->filterTextField == '' ?
                            join(' OR ', array_map(
                                function ($value) use ($n) {
                                    return $value . ' LIKE :text' . $n;
                                },
                                $like
                            )) :
                            $this->filterTextField . ' LIKE :text' . $n,
                        [':text' . $n => '%' . trim($ft) . '%']
                    );
                }
            }

            // доп.операции
            $this->processFilterQuery($params, $query, $dataProvider);
        }

        return $dataProvider;
    }

    /**
     * Получение DataProvider
     *
     * @param array $options [опционально] опции для DataProvider
     *
     * @return object|\yii\data\ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function dataProvider($options = [])
    {

        if (!is_array($options)) {
            throw new InvalidConfigException('Object configuration must be an array');
        }

        $config = [
            'class' => isset($options['class']) ? $options['class'] : $this->dataProviderClassName(),
            'query' => isset($options['query']) ? $options['query'] : static::find(),
            'pagination' => isset($options['pagination']) ? $options['pagination'] : $this->pagination(),
            'sort' => isset($options['sort']) ? $options['sort'] : $this->sort(),
        ];

        unset(
            $options['class'],
            $options['query'],
            $options['pagination'],
            $options['sort']
        );

        return \Yii::createObject(array_merge($config, $options));
    }

    /**
     * Имя класса дата-провайдера
     * @return string
     */
    public function dataProviderClassName()
    {
        return ActiveDataProvider::className();
    }

    /**
     * Получения пагинатора
     * @return object|Pagination
     * @throws \yii\base\InvalidConfigException
     */
    public function pagination()
    {
        return \Yii::createObject(
            [
                'class' => $this->paginationClassName(),
                'pageSize' => $this->pageSize,
                'defaultPageSize' => $this->pageSize,
            ]
        );
    }

    /**
     * Имя класса пагинатора
     * @return string
     */
    public function paginationClassName()
    {
        return Pagination::className();
    }

    /**
     * Получения параметров сортировки
     * @return object|Sort
     */
    public function sort()
    {
        return \Yii::createObject(
            [
                'class' => $this->sortClassName(),
                'defaultOrder' => [
                    'id' => SORT_ASC
                ]
            ]
        );
    }

    /**
     * Имя класса сортировки
     * @return string
     */
    public function sortClassName()
    {
        return Sort::className();
    }

    /**
     * Массив аттрибутов для поиска с точным совпадением
     * @return array
     */
    public function filterAttributes()
    {
        return [];
    }

    /**
     * Массив аттрибутов для поиска через LIKE
     * @return array
     */
    public function filterLikeAttributes()
    {
        return [];
    }

    /**
     * Дополнительные операции с запросом
     *
     * @param $params array
     * @param $query Query
     * @param $dataProvider ActiveDataProvider
     *
     * @return bool
     */
    public function processFilterQuery($params, $query, $dataProvider)
    {
        $this->trigger(
            self::EVENT_PROCESS_FILTER,
            new ProcessFilterEvent(
                [
                    'params' => $params,
                    'query' => $query,
                    'dataProvider' => $dataProvider
                ]
            )
        );
    }

    public function createValidators()
    {
        /** @var $validators \ArrayObject */
        $validators = parent::createValidators();

        /** @var $this ActiveRecord */
        $validators->append(Validator::createValidator(
            'default',
            $this,
            array_merge(
                ['pageSize', 'filterText', 'filterTextField'],
                $this->filterLikeAttributes(),
                $this->filterAttributes()
            ),
            ['on' => 'search']
        ));

        return $validators;
    }
}
