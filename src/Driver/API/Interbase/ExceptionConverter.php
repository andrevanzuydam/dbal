<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Driver\API\Interbase;


use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query;

class ExceptionConverter implements ExceptionConverterInterface
{

    public function convert(Exception $exception, ?Query $query): DriverException
    {
        switch ((int)$exception->getCode())
        {

            case -104:
                return new SyntaxErrorException($exception, $query);
            break;
            case -204:
                return new TableNotFoundException($exception, $query);
            break;
            case -607:
                return new TableExistsException($exception, $query);
            break;
            case -625:
                return new NotNullConstraintViolationException($exception, $query);
            break;
            case -803:
                return new UniqueConstraintViolationException($exception, $query);
            break;
            case -902:
                return new ConnectionException($exception, $query);
            break;
            default:
                return new DriverException($exception, $query);
        }

        return new DriverException($exception, $query);
    }
}