<?php

include __DIR__ . '/init.php';

$shops = Shop::select('id, name, active')->where(function ($q) {
    $q->where('id', 11)->where('active', true);
})->orWhere('active', false)->limit(2)->get();

var_dump($shops);
