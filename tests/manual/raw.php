<?php

include __DIR__ . '/init.php';

$result = TinyOrm\DB::rawQuery('read', 'SELECT id, firstname FROM users WHERE id = 1 LIMIT 1');
var_dump($result);

TinyOrm\DB::rawQuery('write', 'UPDATE users SET firstname = :fn WHERE id = 1 LIMIT 1', ['fn' => 'Test']);

$result = TinyOrm\DB::rawQuery('read', 'SELECT id, firstname FROM users WHERE id = 1 LIMIT 1');
var_dump($result);

TinyOrm\DB::rawQuery('write', 'UPDATE users SET firstname = :fn WHERE id = 1 LIMIT 1', ['fn' => 'Stef']);
