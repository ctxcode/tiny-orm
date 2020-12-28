<?php

namespace TinyOrm;

class Model {

    private $_data = [];
    private $_attributes = [];
    private $_changed = [];

    protected $_connection = null;

    public static function select($selects = null) {
        $q = new Query(Query::TYPE_SELECT, get_called_class());
        $q->selects = $selects;
        return $q;
    }
    public static function insert(Array $data) {
        $q = new Query(Query::TYPE_INSERT, get_called_class());
        $q->insertData = $data;
        $id = $q->runQuery();
        return $id;
    }
    public static function update() {
        $q = new Query(Query::TYPE_UPDATE, get_called_class());
        return $q;
    }
    public static function delete() {
        $q = new Query(Query::TYPE_DELETE, get_called_class());
        return $q;
    }

    public function __get($key) {
        return $this->getAttribute($key);
    }

    public function getAttribute($key) {
        if (!array_key_exists($key, $this->_attributes)) {
            throw new \Exception('Trying to get property "' . $key . '", but this attribute hasnt been set yet');
        }
        return $this->_attributes[$key];
    }

    public function __set($key, $value) {
        $this->setAttributes([$key => $value]);
    }

    public function setAttributes(Array $attributes, bool $markAsChanged = true) {
        foreach ($attributes as $key => $value) {
            $this->_attributes[$key] = $value;
        }
        if ($markAsChanged) {
            $this->setAttributesAsChanged(array_keys($attributes));
        }
    }

    public function setAttributesAsChanged(Array $keys) {
        foreach ($keys as $key) {
            $this->_changed[] = $key;
        }
        $this->_changed = array_unique($this->_changed);
    }

    public static function getPrimaryKeys(): Array{
        if (!isset(static::$primaryKey)) {
            throw new \Exception('Missing ' . get_called_class() . '::$primaryKey');
        }
        $key = static::$primaryKey;
        if (is_string($key)) {
            return [$key];
        }
        if (is_array($key)) {
            return $key;
        }
        throw new \Exception(get_called_class() . '::primaryKey be of type string or array');
    }

    public function save() {
        $changes = [];
        if (count($this->_changed) === 0) {
            return true;
        }
        foreach ($this->_changed as $key) {
            $changes[] = $this->_attributes[$key];
        }
        $primaries = static::getPrimaryKeys();
        $query = static::update();
        foreach ($primaries as $primary) {
            if (!isset($this->_attributes[$primary])) {
                throw new \Exception('The primaryKey value "' . $primary . '" was not found in the attributes');
            }
            $query->where($primary . ' = ', $this->_attributes[$primary]);
        }
        return $query->limit(1)->set($changes);
    }

    public function create() {
        $id = static::insert($this->_attributes);
        return $id;
    }

    // Relations
    public function belongsTo($model, $localKey, $foreignKey) {
        $query = $model::select('*');
        $relation = new Relation('belongsTo', $query);
        $relation->localKey = $localKey;
        $relation->foreignKey = $foreignKey;
        return $relation;
    }
    public function belongsToMany($model, $localKey, $pivotTable, $pivotLocalKey, $pivotForeignKey, $foreignKey) {
        $query = $model::select('*');
        $relation = new Relation('belongsToMany', $query);
        $relation->localKey = $localKey;
        $relation->pivotTable = $pivotTable;
        $relation->pivotLocalKey = $pivotLocalKey;
        $relation->pivotForeignKey = $pivotForeignKey;
        $relation->foreignKey = $foreignKey;
        return $relation;
    }
    public function hasMany($model, $localKey, $foreignKey) {
        $query = $model::select('*');
        $relation = new Relation('hasMany', $query);
        $relation->localKey = $localKey;
        $relation->foreignKey = $foreignKey;
        return $relation;
    }
}
