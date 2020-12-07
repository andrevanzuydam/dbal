<?php


namespace Doctrine\DBAL\Driver\Interbase;


use Doctrine\DBAL\Driver\AbstractInterbaseDriver;


final class Driver extends AbstractInterbaseDriver
{

    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function connect(array $params): ?Connection
    {
        return new Connection($params["host"], $params["dbname"], $params["user"], $params["password"], $params["port"]);
    }

}