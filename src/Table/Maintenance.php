<?php

declare(strict_types=1);

namespace PhpMyAdmin\Table;

use PhpMyAdmin\Dbal\DatabaseInterface;
use PhpMyAdmin\Dbal\Warning;
use PhpMyAdmin\Identifiers\DatabaseName;
use PhpMyAdmin\Identifiers\TableName;
use PhpMyAdmin\Indexes\Index;
use PhpMyAdmin\Table\Maintenance\Message;
use PhpMyAdmin\Util;

use function __;
use function htmlspecialchars;
use function implode;
use function sprintf;

final class Maintenance
{
    public function __construct(private DatabaseInterface $dbi)
    {
    }

    /**
     * @param TableName[] $tables
     *
     * @return array<int, array<string, Message[]>|string>
     * @psalm-return array{array<string, Message[]>, string}
     */
    public function getAnalyzeTableRows(DatabaseName $db, array $tables): array
    {
        $backQuotedTables = [];
        foreach ($tables as $table) {
            $backQuotedTables[] = Util::backquote($table->getName());
        }

        $query = 'ANALYZE TABLE ' . implode(', ', $backQuotedTables) . ';';

        $this->dbi->selectDb($db);
        /** @var array<int, array<string, string>> $result */
        $result = $this->dbi->fetchResultSimple($query);

        $rows = [];
        foreach ($result as $row) {
            $message = Message::fromArray($row);
            $rows[$message->table][] = $message;
        }

        return [$rows, $query];
    }

    /**
     * @param TableName[] $tables
     *
     * @return array<int, array<string, Message[]>|string>
     * @psalm-return array{array<string, Message[]>, string}
     */
    public function getCheckTableRows(DatabaseName $db, array $tables): array
    {
        $backQuotedTables = [];
        foreach ($tables as $table) {
            $backQuotedTables[] = Util::backquote($table->getName());
        }

        $query = 'CHECK TABLE ' . implode(', ', $backQuotedTables) . ';';

        $this->dbi->selectDb($db);
        /** @var array<int, array<string, string>> $result */
        $result = $this->dbi->fetchResultSimple($query);

        $rows = [];
        foreach ($result as $row) {
            $message = Message::fromArray($row);
            $rows[$message->table][] = $message;
        }

        return [$rows, $query];
    }

    /**
     * @param TableName[] $tables
     *
     * @return array<int, array<string, array<int, array<string, string|null>>>|string>
     * @psalm-return array{array<int, array<string, string|null>>, string, Warning[]}
     */
    public function getChecksumTableRows(DatabaseName $db, array $tables): array
    {
        $backQuotedTables = [];
        foreach ($tables as $table) {
            $backQuotedTables[] = Util::backquote($table->getName());
        }

        $query = 'CHECKSUM TABLE ' . implode(', ', $backQuotedTables) . ';';

        $this->dbi->selectDb($db);
        /** @var array<int, array<string, string|null>> $rows */
        $rows = $this->dbi->fetchResultSimple($query);
        $warnings = $this->dbi->getWarnings();

        return [$rows, $query, $warnings];
    }

    /** @param TableName[] $tables */
    public function getIndexesProblems(DatabaseName $db, array $tables): string
    {
        $indexesProblems = '';

        foreach ($tables as $table) {
            $check = Index::findDuplicates($table->getName(), $db->getName());

            if ($check === '') {
                continue;
            }

            $indexesProblems .= htmlspecialchars(sprintf(__('Problems with indexes of table `%s`'), $table->getName()));
            $indexesProblems .= $check;
        }

        return $indexesProblems;
    }

    /**
     * @param TableName[] $tables
     *
     * @return array<int, array<string, Message[]>|string>
     * @psalm-return array{array<string, Message[]>, string}
     */
    public function getOptimizeTableRows(DatabaseName $db, array $tables): array
    {
        $backQuotedTables = [];
        foreach ($tables as $table) {
            $backQuotedTables[] = Util::backquote($table->getName());
        }

        $query = 'OPTIMIZE TABLE ' . implode(', ', $backQuotedTables) . ';';

        $this->dbi->selectDb($db);
        /** @var array<int, array<string, string>> $result */
        $result = $this->dbi->fetchResultSimple($query);

        $rows = [];
        foreach ($result as $row) {
            $message = Message::fromArray($row);
            $rows[$message->table][] = $message;
        }

        return [$rows, $query];
    }

    /**
     * @param TableName[] $tables
     *
     * @return array<int, array<string, Message[]>|string>
     * @psalm-return array{array<string, Message[]>, string}
     */
    public function getRepairTableRows(DatabaseName $db, array $tables): array
    {
        $backQuotedTables = [];
        foreach ($tables as $table) {
            $backQuotedTables[] = Util::backquote($table->getName());
        }

        $query = 'REPAIR TABLE ' . implode(', ', $backQuotedTables) . ';';

        $this->dbi->selectDb($db);
        /** @var array<int, array<string, string>> $result */
        $result = $this->dbi->fetchResultSimple($query);

        $rows = [];
        foreach ($result as $row) {
            $message = Message::fromArray($row);
            $rows[$message->table][] = $message;
        }

        return [$rows, $query];
    }
}
