<?php
declare(strict_types=1);

namespace Sirius\Orm;

class ConnectionLocator extends \Atlas\Pdo\ConnectionLocator
{
    public static function new(...$args)
    {
        if ($args[0] instanceof Connection) {
            return new ConnectionLocator(function () use ($args) {
                return $args[0];
            });
        }

        return new ConnectionLocator(Connection::factory(...$args));
    }
}
