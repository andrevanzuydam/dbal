<?php

namespace Doctrine\DBAL\Tests\Driver\Interbase;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Interbase\Driver;
use Doctrine\DBAL\Tests\Driver\AbstractInterbaseDriverTest;

class DriverTest extends AbstractInterbaseDriverTest
{
    protected function createDriver(): DriverInterface
    {
        return new Driver();
    }
}
