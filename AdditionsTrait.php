<?php

namespace maybeworks\libs;

//use yii\db\ActiveRecord;

/**
 * Class AdditionsTrait
 *
 * @property array $copyUnset
 * @package maybeworks\libs
 */
trait AdditionsTrait
{
    /**
     * Получаем массив параметров для поиска, если нашли - возвращаем найденую модель, в противном случае создаем модель с параметрами поиска
     * @param $arguments
     * @param $save [default false] если true - сохраняет новую запись. проверяйте hasErrors у полученной модели
     * @return static
     */
    public static function findOrCreate($arguments = [], $save = false)
    {
        /** @var $model ActiveRecord */
        if ($model = self::find()->where($arguments)->one()) {
            return $model;
        }

        $model = self::getItem();
        $model->setAttributes($arguments);

        if ($save) {
            $model->save();
        }

        return $model;
    }

    /**
     * Получить запись по id или создать новый экземпляр класса АР (если id = null)
     * @param $id [опционально] id записи (по умолчанию null)
     * @return static если указан id, но запись не найдена, вернет null
     */
    public static function getItem($id = null)
    {
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

    /**
     * Создание копии записи на основе существующей
     * @return static
     */
    public function getCopy()
    {
        /**
         * @var $this ActiveRecord
         */
        $new = static::getItem();

        $new->setAttributes($this->attributes, false);

        // удаляем поля, которых не должно быть у новой записи
        foreach (array_merge($new::primaryKey(), $this->copyUnset) as $field) {
            if (isset($new[$field])) {
                unset($new[$field]);
            }
        }

        return $new;
    }

    public function getCopyUnset()
    {
        return [];
    }
}