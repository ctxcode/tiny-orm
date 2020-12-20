<?php

include __DIR__ . '/init.php';

$start = microtime(true);
$shopid = 520;

// Reads
$orders = Order::select('*')->where('shop_id', $shopid)->where('created_at', '>=', date('Y-01-01 00:00:00'))->where('created_at', '<', date('Y-01-01 00:00:00', strtotime('+1 year')))->where('valid', true)->orderBy('order_nr DESC')->limit(10)->get();
var_dump((microtime(true) - $start) . 's');

$users = User::select('id, firstname')->limit(2)->get();

// Create
$set = new Setting();
$set->key = 'test';
$set->value = 'testv';
$id = $set->create();
var_dump($id);

// Delete
var_dump(Setting::select()->where('value', 'testv')->count());
Setting::delete()->where('value', 'testv')->confirm();
var_dump(Setting::select()->where('value', 'testv')->count());
