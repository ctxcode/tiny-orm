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

$users = User::select('id, firstname')->limit(2)->get();

$set = new Setting();
$set->key = 'test';
$set->value = 'testv';
$id = $set->create();
var_dump($id);

var_dump($users);
