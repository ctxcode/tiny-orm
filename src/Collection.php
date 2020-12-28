<?php

namespace TinyOrm;

class Collection {

    public $modelClass;
    public $items = [];

    public function add($item) {
        if (!($item instanceof Model)) {
            throw new \Exception('Only model instances can be added to a collection');
        }
        if (!isset($this->modelClass)) {
            $this->modelClass = get_class($item);
        } else if (get_class($item) !== $this->modelClass) {
            throw new \Exception('You can only add items to a collection of the same class, collection: "' . ($this->modelClass) . '", tried to add "' . get_class($item) . '"');
        }

        $this->items[] = $item;
    }

    public function load($relationName, $modifier) {

        if (!$this->modelClass) {
            return;
        }

        $firstItem = $this->items[0];

        $func = $relationName . '_Relation';
        $relation = $firstItem->$func();
        $query = $relation->query;

        $localKey = $relation->localKey;
        $foreignKey = $relation->foreignKey;

        if ($relation->type == 'belongsTo') {

            $foreignIn = [];
            foreach ($this->items as $item) {
                $lValue = $item->getAttribute($localKey);
                $foreignIn[] = $lValue;
            }
            $foreignIn = array_values(array_unique($foreignIn));

            $query->whereRelation($foreignKey, 'IN', $foreignIn);
            if ($modifier) {
                $modifier($query);
            }

            $subItems = $query->get()->keyBy($foreignKey);

            foreach ($this->items as $item) {
                $item->$relationName = $subItems->items[$item->$localKey] ?? null;
            }

        }
        if ($relation->type == 'hasMany') {

            $foreignIn = [];
            foreach ($this->items as $item) {
                $lValue = $item->getAttribute($localKey);
                $foreignIn[] = $lValue;
            }
            $foreignIn = array_values(array_unique($foreignIn));

            $query->whereRelation($foreignKey, 'IN', $foreignIn);
            if ($modifier) {
                $modifier($query);
            }

            $byLocalKey = $this->keyBy($localKey);
            $subItems = $query->get();

            $list = [];
            foreach ($subItems->items as $item) {
                if (!isset($list[$item->$foreignKey])) {
                    $list[$item->$foreignKey] = new Collection();
                }
                $list[$item->$foreignKey]->add($item);
            }

            foreach ($this->items as $key => $item) {
                $this->items[$key]->$relationName = $list[$item->$localKey] ?? (new Collection());
            }

            $this->keyBy(null);
        }

    }

    public function keyBy($field) {
        if ($field === null) {
            $this->items = array_values($this->items);
            return $this;
        }
        $newList = [];
        foreach ($this->items as $item) {
            $newList[$item->$field] = $item;
        }
        $this->items = $newList;
        return $this;
    }

    public function count() {
        return count($this->items);
    }

    public function __serialize() {
        return $this->items;
    }
}
