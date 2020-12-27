<?php

namespace TinyOrm;

class Query {

    private $type = null;
    private $connection = null;

    public $insertData = null;
    public $updateData = null;
    public $modelClass;

    public $selects = null;
    public $whereGroup;
    public $whereRelationGroup;
    public $limit;
    public $skip;
    public $orderBy;

    public $withs = [];
    private static $_relationExists = []; // $relationName => true

    const TYPE_SELECT = 1;
    const TYPE_INSERT = 2;
    const TYPE_UPDATE = 3;
    const TYPE_DELETE = 4;

    public function __construct($type, $modelClass) {
        if (!in_array($type, [1, 2, 3, 4], true)) {
            throw new \Exception('Invalid query type');
        }
        $this->type = $type;
        $this->modelClass = $modelClass;
        return $this;
    }

    public function connection($name) {
        $this->connection = $name;
    }

    private function getConnection() {
        if ($this->connection) {
            return DB::getConnection($this->connection);
        }
        return DB::getConnection();
    }

    private function getWhereGroup() {
        if ($this->whereGroup) {
            return $this->whereGroup;
        }
        $group = new WhereGroup();
        $group->type = 'AND';
        $this->whereGroup = $group;
        return $group;
    }

    private function getWhereRelationGroup() {
        if ($this->whereRelationGroup) {
            return $this->whereRelationGroup;
        }
        $group = new WhereGroup();
        $group->type = 'AND';
        $this->whereRelationGroup = $group;
        return $group;
    }

    public function orWhere(...$parts) {
        $group = $this->getWhereGroup();
        $group->parseWhereParams($parts, 'OR');
        return $this;
    }

    public function where(...$parts) {
        $group = $this->getWhereGroup();
        $group->parseWhereParams($parts, 'AND');
        return $this;
    }

    public function whereRelation(...$parts) {
        $group = $this->getWhereRelationGroup();
        $group->parseWhereParams($parts, 'AND');
        return $this;
    }

    public function orderBy(string $orderBy) {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function limit(int $limit) {
        $this->limit = $limit;
        return $this;
    }

    public function skip(int $skip) {
        $this->skip = $skip;
        return $this;
    }

    public function get() {
        if ($this->type !== static::TYPE_SELECT) {
            throw new \Exception('first() can only be used for SELECT queries');
        }
        return $this->runQuery();
    }
    public function first() {
        if ($this->type !== static::TYPE_SELECT) {
            throw new \Exception('first() can only be used for SELECT queries');
        }
        $this->limit(1);
        $result = $this->runQuery();
        return $result->items[0] ?? null;
    }

    public function confirm() {
        if ($this->type !== static::TYPE_DELETE) {
            throw new \Exception('confirm() can only be used for DELETE queries');
        }
        return $this->runQuery();
    }
    public function count() {
        if ($this->type !== static::TYPE_SELECT) {
            throw new \Exception('count() can only be used for SELECT queries');
        }
        return $this->runQuery(['isCount' => true]);
    }

    public function set(Array $data) {
        if ($this->type !== static::TYPE_UPDATE) {
            throw new \Exception('first() can only be used for SELECT queries');
        }
        $this->updateData = $data;
        return $this->runQuery();
    }

    public function runQuery($options = []) {
        $pdo = $this->getConnection();
        $result = $pdo->runQuery($this, $options);

        if ($this->type === static::TYPE_SELECT) {
            $isCount = $options['isCount'] ?? false;
            if ($isCount) {
                return $result;
            }
            $list = new Collection();
            $modelClass = $this->modelClass;
            foreach ($result as $row) {
                $m = new $modelClass();
                $m->setAttributes($row, false);
                $list->add($m);
            }
            // Load relations
            foreach ($this->withs as $name => $modifier) {
                $list->load($name, $modifier);
            }

            // Result
            return $list;
        }

        return $result;
    }

    public function getType() {
        return $this->type;
    }

    // Relations
    public function with($relations) {
        if (is_array($relations)) {
            foreach ($relations as $name => $modifier) {
                if (is_integer($name) && is_string($modifier)) {
                    $this->addWith($modifier, null);
                } else {
                    $this->addWith($name, $modifier);
                }
            }
        } else if (is_string($relations)) {
            $this->addWith($relations, null);
        } else {
            throw new \Exception('With() parameter must be a string or array');
        }

        return $this;
    }

    private function addWith(string $name, ?callable $modifier) {

        if (isset($this->withs[$name])) {
            throw new \Exception('With() already called for "' . $name . '". Overwriting relations is not allowed to limit code confusion.');
        }

        $funcName = $name . '_Relation';

        if (!isset(static::$_relationExists[$name])) {
            if (!method_exists($this->modelClass, $funcName)) {
                throw new \Exception('With("' . $name . '") relationship function does not exist "' . ($this->modelClass) . '::' . $funcName . '()"');
            }
            // $refl = new \ReflectionMethod($this->modelClass, $funcName);
            // if (!$refl->isStatic()) {
            //     throw new \Exception('Relationship function must be static "' . ($this->modelClass) . '::' . $funcName . '()"');
            // }
            static::$_relationExists[$name] = true;
        }

        $this->withs[$name] = $modifier;
        // $instance = new $modelClass();
        // $query = $instance->{$funcName}();

        // if ($modifier) {
        //     $modifier($query);
        // }

        // $this->withs[$name] = $query;
    }

}