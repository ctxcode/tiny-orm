<?php

namespace TinyOrm;

class Relation {

    public string $type;
    public array $details;
    public Query $query;

    public string $localKey;
    public string $foreightKey;

    public string $pivotTable;
    public string $pivotLocalKey;
    public string $pivotForeignKey;

    public function __construct(string $type, Query $query) {
        $this->type = $type;
        $this->query = $query;
    }

    public function __call($method, $args) {
        $this->query->$method($args);
        return $this;
    }

}