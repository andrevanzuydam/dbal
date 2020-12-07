<?php


namespace Doctrine\DBAL\Tests\Driver;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractInterbaseDriver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\InterbasePlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\InterbaseSchemaManager;

class AbstractInterbaseDriverTest extends AbstractDriverTest
{

    protected function createDriver(): Driver
    {
        return $this->getMockForAbstractClass(AbstractInterbaseDriver::class);
    }

    protected function createPlatform(): AbstractPlatform
    {
        return new InterbasePlatform();
    }

    protected function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        return new InterbaseSchemaManager(
            $connection,
            $this->createPlatform()
        );
    }

    protected function createExceptionConverter(): ExceptionConverter
    {
        return new Driver\API\Interbase\ExceptionConverter();
    }
}