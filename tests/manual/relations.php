<?php

include __DIR__ . '/init.php';

$o = Order::select('*')->where('valid', true)->with('shop')->orderBy('created_at', 'DESC')->first();

var_dump($o->id);
var_dump($o->shop_id);
var_dump($o->shop->name);

$s = Shop::select('*')->where('id', $o->shop_id)->first();
var_dump($s->id);
var_dump($s->name);
