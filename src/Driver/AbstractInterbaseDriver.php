<?php


namespace Doctrine\DBAL\Driver;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\InterbasePlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\InterbaseSchemaManager;

/**
 * Abstract base implementation of the {@link Driver} interface for Firebird based drivers.
 */
abstract class AbstractInterbaseDriver implements Driver
{
    /**
     * {@inheritdoc}
     *
     * @return AbstractPlatform|InterbasePlatform
     */
    public function getDatabasePlatform()
    {
        return new InterbasePlatform();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractSchemaManager|InterbaseSchemaManager
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform)
    {
        return new InterbaseSchemaManager($conn, $platform);
    }

    /**
     * {@inheritdoc}
     *
     * @return ExceptionConverter
     */
    public function getExceptionConverter(): ExceptionConverter
    {
        return new Driver\API\Interbase\ExceptionConverter();
    }
}