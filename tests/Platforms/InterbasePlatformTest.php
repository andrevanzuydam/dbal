<?php


namespace Doctrine\DBAL\Tests\Platforms;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\InterbasePlatform;

class InterbasePlatformTest extends AbstractPlatformTestCase
{

    public function createPlatform(): AbstractPlatform
    {
        return new InterbasePlatform();
    }

    public function getGenerateTableSql(): string
    {
        // TODO: Implement getGenerateTableSql() method.
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql(): array
    {
        // TODO: Implement getGenerateTableWithMultiColumnUniqueIndexSql() method.
    }

    public function getGenerateIndexSql(): string
    {
        // TODO: Implement getGenerateIndexSql() method.
    }

    public function getGenerateUniqueIndexSql(): string
    {
        // TODO: Implement getGenerateUniqueIndexSql() method.
    }

    protected function getGenerateForeignKeySql(): string
    {
        // TODO: Implement getGenerateForeignKeySql() method.
    }

    public function getGenerateAlterTableSql(): array
    {
        // TODO: Implement getGenerateAlterTableSql() method.
    }

    protected function getQuotedColumnInPrimaryKeySQL(): array
    {
        // TODO: Implement getQuotedColumnInPrimaryKeySQL() method.
    }

    protected function getQuotedColumnInIndexSQL(): array
    {
        // TODO: Implement getQuotedColumnInIndexSQL() method.
    }

    protected function getQuotedNameInIndexSQL(): array
    {
        // TODO: Implement getQuotedNameInIndexSQL() method.
    }

    protected function getQuotedColumnInForeignKeySQL(): array
    {
        // TODO: Implement getQuotedColumnInForeignKeySQL() method.
    }

    protected function getQuotesReservedKeywordInUniqueConstraintDeclarationSQL(): string
    {
        // TODO: Implement getQuotesReservedKeywordInUniqueConstraintDeclarationSQL() method.
    }

    protected function getQuotesReservedKeywordInTruncateTableSQL(): string
    {
        // TODO: Implement getQuotesReservedKeywordInTruncateTableSQL() method.
    }

    protected function getQuotesReservedKeywordInIndexDeclarationSQL(): string
    {
        // TODO: Implement getQuotesReservedKeywordInIndexDeclarationSQL() method.
    }

    protected function getQuotedAlterTableRenameColumnSQL(): array
    {
        // TODO: Implement getQuotedAlterTableRenameColumnSQL() method.
    }

    protected function getQuotedAlterTableChangeColumnLengthSQL(): array
    {
        // TODO: Implement getQuotedAlterTableChangeColumnLengthSQL() method.
    }

    protected function getCommentOnColumnSQL(): array
    {
        // TODO: Implement getCommentOnColumnSQL() method.
    }

    public function getAlterTableRenameColumnSQL(): array
    {
        // TODO: Implement getAlterTableRenameColumnSQL() method.
    }

    protected function getQuotesTableIdentifiersInAlterTableSQL(): array
    {
        // TODO: Implement getQuotesTableIdentifiersInAlterTableSQL() method.
    }

    protected function getAlterStringToFixedStringSQL(): array
    {
        // TODO: Implement getAlterStringToFixedStringSQL() method.
    }

    protected function getGeneratesAlterTableRenameIndexUsedByForeignKeySQL(): array
    {
        // TODO: Implement getGeneratesAlterTableRenameIndexUsedByForeignKeySQL() method.
    }
}