<?php

namespace TinyOrm;

class DB {

    private static $useConnection = null;
    private static $connections = [];
    private static $drivers = [
        'mysql' => '\\TinyOrm\\Connections\\Mysql',
    ];

    public static function addConnection(string $name, Array $options) {
        if (static::connectionExists($name)) {
            throw new \Exception('There is already a connection named "' . $name . '"');
        }

        if (!isset($options['driver'])) {
            throw new \Exception('Missing option: driver');
        }

        $driver = $options['driver'];
        if (!isset(static::$drivers[$driver])) {
            throw new \Exception('Unsupported driver: ' . $driver);
        }

        $connectionClass = static::$drivers[$driver];

        if (!class_exists($connectionClass)) {
            throw new \Exception('Class does not exist: ' . $connectionClass);
        }

        $connection = new $connectionClass($options);
        if (!is_subclass_of($connection, '\\TinyOrm\\Connection')) {
            throw new \Exception('Connection class must extend TinyOrm\\Connection: ' . $connectionClass);
        }

        static::$connections[$name] = $connection;

        if (static::$useConnection === null) {
            static::defaultConnection($name);
        }
    }

    public static function connectionExists(string $name) {
        return isset(static::$connections[$name]);
    }

    public static function removeConnection(string $name) {
        if (static::connectionExists($name)) {
            unset(static::$connections[$name]);
            return true;
        }
        throw new \Exception('No connection named "' . $name . '"');
    }

    public static function getConnection(string $name = null) {
        if ($name == null) {
            $name = static::$useConnection;
        }
        if (static::connectionExists($name)) {
            return static::$connections[$name];
        }
        throw new \Exception('No connection named "' . $name . '"');
    }

    public static function defaultConnection(string $name) {
        if (!static::connectionExists($name)) {
            throw new \Exception('No connection named "' . $name . '"');
        }
        static::$useConnection = $name;
    }

    public static function rawQuery(string $type, string $query, array $bindings = []) {

        $types = [
            'read' => \TinyOrm\Connection::READ,
            'write' => \TinyOrm\Connection::WRITE,
        ];
        if (!isset($types[$type])) {
            throw new \Exception('Invalid rawQuery type: ' . $type);
        }
        $type = $types[$type];

        $conn = static::getConnection();
        return $conn->rawQuery($type, $query, $bindings);
    }
}