<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;
use Throwable;

/**
 * Makes `php artisan migrate` safe on a database where some tables / columns / indexes were created by hand.
 *
 *  - Schema::create() on a table that already exists adds only the columns it is missing
 *  - Schema::table()  skips columns that already exist
 *  - an index / foreign key / rename that is already there is skipped instead of aborting the run
 */
class SafeBlueprint extends Blueprint
{
    // MySQL: table exists, duplicate column, duplicate key name, duplicate FK / constraint name
    private const ALREADY_THERE = [1050, 1060, 1061, 1826, 3822];

    public function build(Connection $connection, Grammar $grammar)
    {
        $this->skipWhatExists($connection);

        foreach ($this->toSql($connection, $grammar) as $statement) {
            try {
                $connection->statement($statement);
            } catch (Throwable $e) {
                if (! $this->alreadyThere($e)) {
                    throw $e;
                }
            }
        }
    }

    private function skipWhatExists(Connection $connection): void
    {
        $schema = $connection->getSchemaBuilder();

        if (! $schema->hasTable($this->getTable())) {
            return;
        }

        // table is already there: turn "create" into "alter" and keep only the missing columns
        $wasCreating = $this->creating();

        $this->commands = array_values(array_filter(
            $this->commands,
            fn ($c) => $c instanceof ColumnDefinition || $c->name !== 'create'
        ));

        $existing = array_map('strtolower', $schema->getColumnListing($this->getTable()));

        $missing = fn ($c) => ! $c instanceof ColumnDefinition
            || $c->change
            || ! in_array(strtolower($c->name), $existing, true);

        $this->columns  = array_values(array_filter($this->columns, $missing));
        $this->commands = array_values(array_filter($this->commands, $missing));

        if ($wasCreating) {
            // while creating, columns are not in the command list; as an alter they have to be, ahead of any index
            $this->commands = [...$this->columns, ...$this->commands];
        }
    }

    private function alreadyThere(Throwable $e): bool
    {
        $code = $e->getPrevious() instanceof \PDOException
            ? ($e->getPrevious()->errorInfo[1] ?? null)
            : ($e instanceof \PDOException ? ($e->errorInfo[1] ?? null) : null);

        return in_array($code, self::ALREADY_THERE, true);
    }
}
