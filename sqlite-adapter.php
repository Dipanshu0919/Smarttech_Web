<?php
// sqlite-adapter.php - Zero-overhead local SQLite3 database engine

class SqliteCliStatement {
    private $parent;
    private $sql;
    private $rows = [];
    private $index = 0;

    public function __construct($parent, $sql) {
        $this->parent = $parent;
        $this->sql = trim($sql);
    }

    public function execute($params = null) {
        $sql = $this->sql;
        if (!empty($params) && is_array($params)) {
            // Replace positional ? placeholders with safely escaped SQL values
            $offset = 0;
            foreach ($params as $param) {
                $pos = strpos($sql, '?', $offset);
                if ($pos === false) break;

                if ($param === null) {
                    $escaped = 'NULL';
                } elseif (is_int($param) || is_float($param)) {
                    $escaped = (string)$param;
                } else {
                    $escaped = "'" . str_replace("'", "''", (string)$param) . "'";
                }

                $sql = substr_replace($sql, $escaped, $pos, 1);
                $offset = $pos + strlen($escaped);
            }
        }

        $res = $this->parent->rawQuery($sql);
        $this->rows = $res;
        $this->index = 0;
        return true;
    }

    public function fetch($mode = PDO::FETCH_ASSOC) {
        if ($this->index < count($this->rows)) {
            return $this->rows[$this->index++];
        }
        return false;
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC) {
        return $this->rows;
    }
}

class SqliteCliPDO {
    private $dbPath;
    private $lastId = 0;

    public function __construct($dbPath) {
        $this->dbPath = $dbPath;
    }

    public function getAttribute($attr) {
        if ($attr === PDO::ATTR_DRIVER_NAME) return 'sqlite_cli';
        return null;
    }

    public function lastInsertId() {
        return $this->lastId;
    }

    public function exec($sql) {
        $this->rawQuery($sql);
        return 1;
    }

    public function query($sql) {
        $stmt = $this->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function prepare($sql) {
        return new SqliteCliStatement($this, $sql);
    }

    public function rawQuery($sql) {
        $sql = trim($sql);
        $isSelect = (stripos($sql, 'SELECT') === 0 || stripos($sql, 'PRAGMA') === 0);
        $isInsert = (stripos($sql, 'INSERT') === 0);

        if ($isInsert && stripos($sql, 'last_insert_rowid') === false) {
            $sql .= "; SELECT last_insert_rowid() AS last_id;";
            $isSelect = true; // Read last_id result
        }

        $cmd = 'sqlite3 -json ' . escapeshellarg($this->dbPath);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Failed to execute sqlite3 command.');
        }

        fwrite($pipes[0], $sql . "\n");
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        if (!empty($stderr) && stripos($stderr, 'error') !== false) {
            error_log('SQLite Error: ' . $stderr . ' Query: ' . $sql);
        }

        if ($isSelect && !empty(trim($stdout))) {
            $data = json_decode($stdout, true);
            if (is_array($data)) {
                if ($isInsert && !empty($data)) {
                    $lastItem = end($data);
                    if (isset($lastItem['last_id'])) {
                        $this->lastId = (int)$lastItem['last_id'];
                    }
                }
                return $data;
            }
        }

        return [];
    }
}

