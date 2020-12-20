<?php

include __DIR__ . '/../../vendor/autoload.php';

TinyOrm\DB::addConnection('default', [
    'read' => [
        'host' => [
            '127.0.0.1',
        ],
    ],
    'write' => [
        'host' => [
            '127.0.0.1',
        ],
    ],
    'driver' => 'mysql',
    'database' => 'bakeronline',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

class User extends TinyOrm\Model {
    static $table = 'users';
}
class Setting extends TinyOrm\Model {
    static $table = 'settings';
}
class Order extends TinyOrm\Model {
    static $table = 'orders';
}

$start = microtime(true);
$shopid = 520;
$orders = Order::select('*')->where('shop_id', $shopid)->where('created_at', '>=', date('Y-01-01 00:00:00'))->where('created_at', '<', date('Y-01-01 00:00:00', strtotime('+1 year')))->where('valid', true)->orderBy('order_nr DESC')->limit(10)->get();
var_dump((microtime(true) - $start) . 's');

$users = User::select('id, firstname')->limit(2)->get();
// var_dump($users);

$set = new Setting();
$set->key = 'test';
$set->value = 'testv';
$id = $set->create();
var_dump($id);

var_dump(Setting::select()->where('value', 'testv')->count());
Setting::delete()->where('value', 'testv')->confirm();
var_dump(Setting::select()->where('value', 'testv')->count());
