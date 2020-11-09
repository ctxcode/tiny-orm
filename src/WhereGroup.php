<?php

namespace TinyOrm;

class WhereGroup {

    public $type;
    public $wheres = [];

    public function parseWhereParams($parts, $type) {
        $count = count($parts);
        if ($count == 1) {
            if (!is_callable($parts[0])) {
                throw new \Exception('Invalid param in where()');
            }
            $group = new WhereGroup();
            $group->type = $type;
            $parts[0]($group);
            $this->wheres[] = $group;
        }
        if ($count < 2) {
            throw new \Exception('Missing params in where()');
        }
        if ($count > 3) {
            throw new \Exception('Too many params in where()');
        }
        if ($count == 2) {
            $parts[2] = $parts[1];
            $parts[1] = '=';
        }
        if (!in_array($parts[1], ['=', '!=', '<', '>', '<=', '>='], true)) {
            throw new \Exception('Invalid operator "' . $parts[1] . '" in where()');
        }
        $this->wheres[] = [$type, $parts[0], $parts[1], $parts[2]];
    }

    public function orWhere(...$parts) {
        $this->parseWhereParams($parts, 'OR');
    }

    public function where(...$parts) {
        $this->parseWhereParams($parts, 'AND');
    }
}