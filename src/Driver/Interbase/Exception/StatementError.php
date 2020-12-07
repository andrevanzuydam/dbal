<?php


namespace Doctrine\DBAL\Driver\Interbase\Exception;


use Doctrine\DBAL\Driver\AbstractException;

class StatementError extends AbstractException
{
    /**
     * @param resource $dbh
     */
    public static function new($dbh): self
    {
        $errorCode = ibase_errcode();
        $errorMessage = ibase_errmsg();

        if ($errorCode !== false) {
            return new self($errorMessage, $errorCode, $errorCode);
        } else {
            return new self ("Unknown Error", 0, 0);
        }
    }

    /**
     * Used for generic error messages
     * @param $message
     * @return static
     */
    public static function error($message):self
    {
        return new self($message);
    }

}