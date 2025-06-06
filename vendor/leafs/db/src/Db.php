<?php

declare(strict_types=1);

namespace Leaf;

use Leaf\Db\Builder;
use Leaf\Db\Utils;

/**
 * Leaf Db
 * -----
 * Simple database interactions
 *
 * @version 3.0
 * @since v2.1.0
 */
class Db extends Db\Core
{
    /**
     * Create a database if it doesn't exist
     *
     * @param string $db The name of the database to create
     */
    public function create(string $db): self
    {
        $this->query("CREATE DATABASE $db");

        return $this;
    }

    /**
     * Drop a database if it exists
     *
     * @param string $db The name of the database to drop
     */
    public function drop(string $db): self
    {
        $this->query("DROP DATABASE $db");

        return $this;
    }

    /**
     * Check if a database table exists
     *
     * @return bool true if the table exists
     */
    public function tableExists(string $table)
    {
        $this->connection($this->currentConnection);

        switch ($this->config['dbtype']) {
            case 'sqlite':
                $schema = $this->select('sqlite_master')->where(['type' => 'table', 'name' => $table])->all();
                break;

            case 'mysql':
                $schema = $this->select('INFORMATION_SCHEMA.TABLES')->where(['TABLE_SCHEMA' => $this->config['dbname'], 'TABLE_NAME' => $table])->all();
                break;

            case 'pgsql':
                $schema = $this->select('pg_catalog.pg_tables')->where(['schemaname' => 'public', 'tablename' => $table])->all();
                break;

            case 'sqlsrv':
                $schema = $this->select('information_schema.tables')->where(['table_schema' => 'dbo', 'table_name' => $table])->all();
                break;

            default:
                $schema = $this->select('INFORMATION_SCHEMA.SCHEMATA')->where('SCHEMA_NAME', $table)->all();
                break;
        }

        return count($schema) > 0;
    }

    /**
     * Create database table
     *
     * @param string $table The name of the database table to create
     * @param array $fields The fields to create
     */
    public function createTable(string $table, array $fields = [])
    {
        $parsed = '';

        if (count($fields) > 0) {
            foreach ($fields as $k => $v) {
                $parsed .= "$k $v, ";
            }

            $parsed = rtrim($parsed, ', ');
        }

        $this->query("CREATE TABLE $table ($parsed);");

        return $this;
    }

    /**
     * Create database table
     *
     * @param string $table The name of the database table to create
     * @param array $fields The fields to create
     */
    public function createTableIfNotExists(string $table, array $fields = [])
    {
        $this->createTable($table, $fields);
        $this->query(str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $this->query));

        return $this;
    }

    /**
     * Drop a database table
     *
     * @params string $table The name of the table to drop
     */
    public function dropTable(string $table)
    {
        return $this->query("DROP TABLE $table")->execute();
    }

    /**
     * Backup a database
     *
     * @param string $dbName The name of the database to backup
     * @param string $destination The path to backup database to
     * @param string $withDifferential Whether to use differential backups or not
     */
    public function backup(string $dbName, string $destination, bool $withDifferential = false)
    {
        return $this->query("BACKUP DATABASE $dbName TO DISK = '$destination' " .
            $withDifferential ? 'WITH DIFFERENTIAL' : '')->execute();
    }

    /**
     * Add a find by id clause to query
     *
     * @param string|int $id The id of the row to find
     */
    public function find($id)
    {
        $this->where('id', $id);

        return $this->first();
    }

    /**
     * Find the first matching item for current query
     */
    public function first()
    {
        $this->query .= ' ORDER BY id ASC LIMIT 1';

        return $this->fetchAssoc();
    }

    /**
     * Find the last matching item for current query
     */
    public function last()
    {
        $this->query .= ' ORDER BY id DESC LIMIT 1';

        return $this->fetchAssoc();
    }

    /**
     * Order query items by a specific
     *
     * @param string $column The column to order results by
     * @param string $direction The direction to order [DESC, ASC]
     */
    public function orderBy(string $column, string $direction = 'desc')
    {
        $this->query = Builder::orderBy($this->query, $column, $direction);

        return $this;
    }

    /**
     * Group query results by a column
     *
     * @param string $column The column to group results by
     * @author Milos Lukic <https://github.com/iammiloslukic>
     */
    public function groupBy($column)
    {
        $this->query = Builder::groupBy($this->query, $column);

        return $this;
    }

    /**
     * Limit query items by a specific number
     *
     * @param string|number $limit The number to limit by
     */
    public function limit($limit)
    {
        $this->query = Builder::limit($this->query, $limit);

        return $this;
    }

    /**
     * Offset query items by a specific number
     *
     * @param string|number $offset The number to offset by
     */
    public function offset($offset)
    {
        $this->query = Builder::offset($this->query, $offset);

        return $this;
    }

    /**
     * Retrieve a row from table
     *
     * @param string $table Db Table
     * @param string $items Specific table columns to fetch
     */
    public function select(string $table, string $items = '*')
    {
        $this->query("SELECT $items FROM $table");
        $this->table = $table;

        return $this;
    }

    /**
     * Add a new row in a db table
     *
     * @param string $table Db Table
     */
    public function insert(string $table): self
    {
        $this->query("INSERT INTO $table");
        $this->table = $table;

        return $this;
    }

    /**
     * Update a row in a db table
     *
     * @param string $table Db Table
     */
    public function update(string $table): self
    {
        $this->query("UPDATE $table");
        $this->table = $table;

        return $this;
    }

    /**
     * Delete a table's records
     *
     * @param string $table: Db Table
     */
    public function delete(string $table): self
    {
        $this->query("DELETE FROM $table");
        $this->table = $table;

        return $this;
    }

    /**
     * Pass in parameters into your query
     *
     * @param array|string $params Key or params to pass into query
     * @param string|null $value Value for key
     */
    public function params($params): self
    {
        $this->query = Builder::params($this->query, $params);
        $this->bind(...(Builder::$bindings));
        $this->params = $params;

        return $this;
    }

    /**
     * Add a where clause to db query
     *
     * @param string|array $condition The condition to evaluate
     * @param mixed $comparator Condition value or comparator
     * @param mixed $value The value of condition if comparator is passed
     */
    public function where($condition, $comparator = null, $value = null): self
    {
        $this->query = Builder::where(
            $this->query,
            $condition,
            $value === null ? $comparator : $value,
            $value === null ? '=' : $comparator
        );

        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Add a where clause with OR comparator to db query
     *
     * @param string|array $condition The condition to evaluate
     * @param mixed $comparator Condition value or comparator
     * @param mixed $value The value of condition if comparator is passed
     */
    public function orWhere($condition, $comparator = null, $value = null): self
    {
        $this->query = Builder::where(
            $this->query,
            $condition,
            $value === null ? $comparator : $value,
            $value === null ? '=' : $comparator,
            'OR'
        );
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Add a JSON where clause to the query
     *
     * @param string $column The JSON column
     * @param string $jsonKey The key within the JSON structure
     * @param mixed $value The value to compare against
     * @param string $comparator The comparison operator (default '=')
     */
    public function whereJson(string $column, string $jsonKey, $value, string $comparator = '='): self
    {
        // Check if the value is an integer, and cast the JSON value to an integer
        $jsonExpression = is_int($value)
            # just incase: ? "CAST(JSON_EXTRACT($column, '$.$jsonKey') AS UNSIGNED)";
            ? "JSON_EXTRACT($column, '$.$jsonKey') + 0"
            : "JSON_EXTRACT($column, '$.$jsonKey')";

        $this->query = Builder::where($this->query, $jsonExpression, $value, $comparator);
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Add a JSON where clause with OR comparator to the query
     *
     * @param string $column The JSON column
     * @param string $jsonKey The key within the JSON structure
     * @param mixed $value The value to compare against
     * @param string $comparator The comparison operator (default '=')
     */
    public function orWhereJson(string $column, string $jsonKey, $value, string $comparator = '='): self
    {
        $jsonExpression = is_int($value)
            ? "JSON_EXTRACT($column, '$.$jsonKey') + 0"
            : "JSON_EXTRACT($column, '$.$jsonKey')";

        $this->query = Builder::where($this->query, $jsonExpression, $value, $comparator, 'OR');
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Add a JSON contains clause to the query
     *
     * @param string $column The JSON column
     * @param mixed $value The value to check for
     * @param string|null $jsonKey The key within the JSON structure (optional)
     */
    public function whereJsonContains(string $column, $value, ?string $jsonKey = null): self
    {
        $jsonValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $jsonExpression = $jsonKey
            ? "JSON_CONTAINS($column, ?, '$.$jsonKey')"
            : "JSON_CONTAINS($column, ?)";

        
        Builder::$bindings[] = $jsonValue;
        $this->query = Builder::where($this->query, $jsonExpression, 1, '=');
        $this->bind(...(Builder::$bindings));

        return $this;
    }
    
    /**
     * Add a JSON contains clause with OR comparator to the query
     *
     * @param string $column The JSON column
     * @param mixed $value The value to check for
     * @param string|null $jsonKey The key within the JSON structure (optional)
     */
    public function orWhereJsonContains(string $column, $value, ?string $jsonKey = null): self
    {
        $jsonValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $jsonExpression = $jsonKey
            ? "JSON_CONTAINS($column, ?, '$.$jsonKey')"
            : "JSON_CONTAINS($column, ?)";

        Builder::$bindings[] = $jsonValue;
        $this->query = Builder::where($this->query, $jsonExpression, 1, '=', 'OR');
        $this->bind(...(Builder::$bindings));

        return $this;
    }

    /**
     * Fetch current query with all related data
     *
     * @param string $table The table to join
     * @param string $foreignKey The foreign key to use
     *
     * @return self
     */
    public function with(string $table, ?string $foreignKey = null)
    {
        $foreignKey ??= Utils::basicSingularize($table) . '_id';

        $this->eager[] = [
            'table' => $table,
            'foreignKey' => $foreignKey
        ];

        return $this;
    }

    /**
     * Hide particular fields from the final value returned
     *
     * @param mixed $values The value(s) to hide
     */
    public function hidden(...$values): self
    {
        $this->hidden = Utils::flatten($values);

        return $this;
    }

    /**
     * Make sure a value doesn't already exist in a table to avoid duplicates.
     *
     * @param mixed $uniques Items to check for
     */
    public function unique(...$uniques)
    {
        $this->uniques = Utils::flatten($uniques);

        return $this;
    }

    /**
     * Add particular fields to the final value returned
     *
     * @param string|array $name What to add
     * @param string $value The value to add
     */
    public function add($name, $value = null): self
    {
        if (is_array($name)) {
            $this->added = $name;
        } else {
            $this->added[$name] = $value;
        }

        return $this;
    }

    /**
     * Search a db table for a value
     *
     * @param string $row The item to search for in table
     * @param string $value The keyword to search for
     * @param array|null $hidden The items to hide from returned result
     */
    public function search(string $row, string $value, ?array $hidden = []): ?array
    {
        return $this->select($this->table)->where($row, 'LIKE', Utils::includes($value))->hidden($hidden)->all();
    }

    /**
     * Begin a database transaction
     *
     * @return self
     */
    public function beginTransaction(): self
    {
        $this->query('START TRANSACTION');
        $this->execute();

        return $this;
    }

    /**
     * Commit the current transaction
     *
     * @return self
     */
    public function commit(): self
    {
        $this->query('COMMIT');
        $this->execute();

        return $this;
    }

    /**
     * Rollback the current transaction
     *
     * @return self
     */
    public function rollback(): self
    {
        $this->query('ROLLBACK');
        $this->execute();

        return $this;
    }

    /**
     * Run a database transaction
     *
     * @param callable $callback The callback to run
     *
     * @return bool Whether the transaction completed successfully
     */
    public function transaction($callback): bool
    {
        try {
            $this->beginTransaction();
            $callback($this);
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->errorsArray = $e;

            return false;
        }
    }
}
