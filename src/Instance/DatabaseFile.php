<?php

namespace PHPFuser\Instance;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use PHPFuser\File;
use PHPFuser\Path;
use PHPFuser\Utils;

/**
 * @author Senestro
 */
class DatabaseFile {

    /**
     * @var PDO The database instance
     */
    private ?PDO $db;

    /**
     * @var string The name of the database file
     */
    private string $dbFilename;

    /**
     * Constructor for initializing the SQLite database connection.
     *
     * This method attempts to connect to an SQLite database located at the specified path.
     * If the directory or file doesn't exist, it will be created. The database connection is established
     * using PDO, and error mode is set to throw exceptions in case of errors.
     *
     * @param string $databaseName The name of the database file. If `$useDatabaseNameAsPath` is true, 
     *                             this is the full file name of the database (e.g., 'mydb.db').
     * @param bool $useDatabaseNameAsPath Flag to determine whether the database name is used as a path.
     *                                    If true, the database file is directly used. If false, the 
     *                                    database file is constructed from a base directory.
     * 
     * @throws InvalidArgumentException If the database file extension is not '.db' or '.sqlite' when `$useDatabaseNameAsPath` is true.
     * @throws PDOException If the database connection fails.
     */
    public function __construct(string $databaseName, bool $useDatabaseNameAsPath = false) {
        try {
            // Check if the database name should be used as a direct path
            if ($useDatabaseNameAsPath) {
                // Ensure the database file has a valid extension (.db or .sqlite)
                if (!preg_match('/\.(db|sqlite)$/i', $databaseName)) {
                    throw new InvalidArgumentException("The database file must have a '.db' or '.sqlite' extension.");
                } else {
                    // Assign the database file name directly
                    $this->dbFilename = $databaseName;
                }
            } else {
                // Construct the path to the database file within the data directory
                $dbPath = Path::insert_dir_separator(PHPFUSER['DIRECTORIES']['DATA'] . "dabasefile");
                // Ensure that the directory exists, creating it if necessary
                File::createDir($dbPath);
                // Remove any file extension and ensure the final file path ends with ".sqlite"
                $this->dbFilename = $dbPath . "" . File::removeExtension($databaseName) . ".sqlite";
            }
            // Establish a connection to the SQLite database using PDO
            $this->db = new PDO("sqlite:" . $this->dbFilename);
            // Set the PDO error mode to exception to handle errors more gracefully
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Handle any connection errors by displaying a message and exiting
            exit("Failed to connect to SQLite database: " . $exception->getMessage());
        }
    }

    /**
     * Destructor for closing the database connection.
     *
     * This method is automatically called when the object is destroyed. It ensures that the
     * database connection is properly closed by setting the PDO instance to null.
     */
    public function __destruct() {
        // Close the database connection by setting the PDO instance to null
        $this->db = null;
    }

    /**
     * Creates a new table in the database with the specified name and columns.
     *
     * @param string $tableName The name of the table to be created.
     * @param array $columns An associative array where the key is the column name
     *                        and the value is the data type (e.g., ['id' => 'INTEGER PRIMARY KEY', 'name' => 'TEXT']).
     * @return int|false Returns the number of affected rows (as an integer) on success, or false on failure.
     */
    public function createTable(string $tableName, array $columns): int|false {
        // Array to hold the column definitions (e.g., "id INTEGER PRIMARY KEY", "name TEXT").
        $definations = [];
        // Iterate through each column definition in the input array.
        foreach ($columns as $columnName => $type) {
            // Construct the column definition string and add it to the definitions array.
            $definations[] = $columnName . " " . $type;
        }
        // Construct the SQL query to create the table with the specified columns.
        $sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (" . implode(", ", $definations) . ")";
        // Execute the SQL query and return the result.
        return $this->execute($sql);
    }

    /**
     * Deletes a table from the database if it exists.
     *
     * This method will permanently remove the table and its data. Use with caution.
     *
     * @param string $tableName The name of the table to be deleted.
     * @return int|false Returns the number of affected rows (as an integer) on success, or false on failure.
     */
    public function deleteTable(string $tableName): int|false {
        // Construct the SQL query to drop the table if it exists.
        $sql = "DROP TABLE IF EXISTS " . $tableName;
        // Execute the query and return the result.
        return $this->execute($sql);
    }

    /**
     * Truncates a table by deleting all of its rows.
     *
     * This method removes all data from the table but keeps the table structure intact.
     * It also resets the auto-increment sequence for the table if applicable.
     *
     * @param string $tableName The name of the table to be truncated.
     * @return int|false Returns the number of affected rows (as an integer) on success, or false on failure.
     */
    public function truncateTable($tableName): int|false {
        // Construct the SQL query to delete all rows from the table.
        $sql = "DELETE FROM " . $tableName;
        // Execute the delete query and store the result.
        $deleted = $this->execute($sql);
        // Reset the auto-increment sequence if the table is using SQLite auto-incrementing.
        // This is necessary to avoid reusing old auto-increment values.
        $this->execute("DELETE FROM sqlite_sequence WHERE name = '" . $tableName . "'");
        // Return the number of deleted rows (or false if the operation failed).
        return $deleted;
    }

    /**
     * Inserts data into the specified table.
     *
     * This method prepares an `INSERT INTO` SQL query and executes it with the provided data.
     * The `lastInsertId()` method is called to retrieve the ID of the last inserted row.
     *
     * @param string $tableName The name of the table to insert data into.
     * @param array $data An associative array where the key is the column name and the value is the value to insert.
     * @return string|false Returns the last inserted ID (as a string) on success, or false on failure.
     */
    public function insert(string $tableName, array $data): string | false {
        if ($this->db === null) {
            return false;
        } else { // Prepare the columns and placeholders for the insert query
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            // Construct the SQL query for insertion
            $sql = "INSERT INTO " . $tableName . " (" . $columns . ") VALUES (" . $placeholders . ")";
            // Prepare the SQL statement
            $stmt = $this->db->prepare($sql);
            // Execute the statement with the provided data
            $stmt->execute($data);
            // Return the last inserted ID
            return $this->db->lastInsertId();
        }
    }

    /**
     * Selects rows from the specified table based on conditions.
     *
     * This method constructs a `SELECT` query with optional `WHERE` conditions
     * and fetches the results as an associative array.
     *
     * @param string $tableName The name of the table to select data from.
     * @param array $conditions An associative array where the key is the column name and the value is the value to filter by.
     *                          The conditions will be applied as `column = value`.
     * @param string $columns A comma-separated string of columns to select. Defaults to "*" (all columns).
     * @return array An array of results, each as an associative array where the keys are column names.
     */
    public function select(string $tableName, array $conditions = [], string $columns = "*"): array {
        if ($this->db === null) {
            return array();
        } else {
            // Construct the base SQL query for selection
            $sql = "SELECT " . $columns . " FROM " . $tableName;
            // If conditions are provided, append the WHERE clause
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $column => $value) {
                    // Add each condition as `column = :column`
                    $where[] = $column . " = :" . $column;
                }
                // Join multiple conditions with AND
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            // Prepare the SQL statement
            $stmt = $this->db->prepare($sql);
            // Execute the statement with the provided conditions
            $stmt->execute($conditions);
            // Fetch and return all results as an associative array
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Updates data in the specified table based on conditions.
     *
     * This method prepares an `UPDATE` SQL query and executes it with the provided data and optional conditions.
     * If conditions are provided, the query will only update rows that match those conditions.
     * The method returns the number of affected rows.
     *
     * @param string $tableName The name of the table to update data in.
     * @param array $data An associative array where the key is the column name and the value is the value to update.
     * @param array $conditions An optional associative array where the key is the column name and the value is the value to match for the WHERE clause.
     * @return int The number of affected rows.
     */
    public function update(string $tableName, array $data, array $conditions = []): int {
        if ($this->db === null) {
            return 0;
        } else {
            // Prepare the SET clause for the UPDATE query.
            $clauses = [];
            foreach ($data as $column => $value) {
                $clauses[] = "$column = :$column";
            }
            // Construct the SQL query to update the table.
            $sql = "UPDATE $tableName SET " . implode(", ", $clauses);
            // If conditions are provided, add the WHERE clause.
            if (!empty($conditions)) {
                $wheres = [];
                foreach ($conditions as $column => $value) {
                    // Add each condition as `column = :where_column`
                    $wheres[] = "$column = :where_$column";
                }
                $sql .= " WHERE " . implode(" AND ", $wheres);
                // Prefix condition keys with `where_` to distinguish from the update data
                $params = array_merge($data, array_combine(array_map(fn($key) => "where_" . $key, array_keys($conditions)), $conditions));
            } else {
                // No conditions, update all rows
                $params = $data;
            }
            // Prepare and execute the SQL statement with the data and conditions
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            // Return the number of affected rows
            return $stmt->rowCount();
        }
    }

    /**
     * Deletes rows from the specified table based on conditions.
     *
     * This method constructs a `DELETE FROM` SQL query and executes it with the provided conditions.
     * The method returns the number of affected rows.
     *
     * @param string $tableName The name of the table to delete rows from.
     * @param array $conditions An associative array where the key is the column name and the value is the value to filter by.
     * @return int The number of affected rows.
     */
    public function delete(string $tableName, array $conditions): int {
        if ($this->db === null) {
            return 0;
        } else {
            // Prepare the WHERE clause for the DELETE query
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = $column . " = :" . $column;
            }
            // Construct the SQL query to delete rows from the table
            $sql = "DELETE FROM " . $tableName . " WHERE " . implode(" AND ", $where);
            // Prepare and execute the SQL statement with the conditions
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            // Return the number of affected rows
            return $stmt->rowCount();
        }
    }

    /**
     * Executes a given SQL query.
     *
     * This method executes a query that does not return results, such as `CREATE`, `DROP`, or `UPDATE` queries.
     *
     * @param string $sql The SQL query to execute.
     * @return int|false Returns the number of affected rows on success, or false on failure.
     */
    public function execute($sql): int|false {
        if ($this->db === null) {
            return false;
        } else {
            // Execute the SQL query and return the result
            return $this->db->exec($sql);
        }
    }

    /**
     * Exports the SQLite database to a SQL dump file.
     * 
     * This method creates a SQL dump file that contains the schema (CREATE TABLE statements)
     * and data (INSERT INTO statements) of all tables in the SQLite database.
     * 
     * @param string $outputFile The path to the output SQL file where the dump will be saved.
     * @return bool Returns true if the export was successful, false if an error occurred.
     */
    public function exportToSQL(string $outputFile): bool {
        if ($this->db === null) {
            return false;
        } else {
            try {
                // Open the output SQL file for writing
                $sqlFile = fopen($outputFile, 'w');
                // Check if the file was successfully opened
                if (!$sqlFile) {
                    throw new Exception("Unable to open the output file for writing.");
                } else {
                    // Write the SQL dump header at the top of the SQL file
                    fwrite($sqlFile, "-- SQLite Database Export\n\n");
                    // Query to get the list of table names from the SQLite database
                    $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_ASSOC);
                    // Loop through all tables in the database
                    foreach ($tables as $table) {
                        $tableName = $table['name'];
                        // Write the CREATE TABLE statement for the current table
                        $createTableStmt = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='" . $tableName . "'")->fetch(PDO::FETCH_ASSOC);
                        fwrite($sqlFile, $createTableStmt['sql'] . ";\n\n");
                        // Query to get all rows from the current table
                        $rows = $this->db->query("SELECT * FROM " . $tableName . "")->fetchAll(PDO::FETCH_ASSOC);
                        // Loop through all rows and create INSERT INTO statements
                        foreach ($rows as $row) {
                            // Get the column names and their respective values
                            $columns = array_keys($row);
                            $values = array_map(function ($value) {
                                // Escape null values and escape strings
                                return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                            }, array_values($row));
                            // Create the INSERT INTO statement for the current row
                            $insertStmt = "INSERT INTO " . $tableName . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                            fwrite($sqlFile, $insertStmt);
                        }
                        // Add a newline after each table's data
                        fwrite($sqlFile, "\n");
                    }
                    // Close the SQL file after writing all data
                    fclose($sqlFile);

                    // Return true indicating the export was successful
                    return true;
                }
            } catch (\Throwable $th) {
                // Return false in case of any errors
                return false;
            }
        }
    }
}
