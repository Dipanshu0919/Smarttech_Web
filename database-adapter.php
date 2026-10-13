<?php
// database-adapter.php - Lightweight fallback for development when MySQL is offline

class JsonPDOStatement {
    private $data;
    private $currentIndex = 0;

    public function __construct(array $data) {
        $this->data = array_values($data);
    }

    public function fetch($mode = PDO::FETCH_ASSOC) {
        if ($this->currentIndex < count($this->data)) {
            return $this->data[$this->currentIndex++];
        }
        return false;
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC) {
        return $this->data;
    }

    public function execute($params = null) {
        return true;
    }
}

class JsonPDO {
    private $filePath;
    private $db = [];
    private $lastId = 0;

    public function __construct($filePath) {
        $this->filePath = $filePath;
        $this->load();
    }

    private function load() {
        if (file_exists($this->filePath)) {
            $raw = file_get_contents($this->filePath);
            $this->db = json_decode($raw, true) ?: [];
        } else {
            $this->db = [];
        }

        if (!isset($this->db['users'])) $this->db['users'] = [];
        if (!isset($this->db['orders'])) $this->db['orders'] = [];
        if (!isset($this->db['enquiries'])) $this->db['enquiries'] = [];
        if (!isset($this->db['blogs'])) $this->db['blogs'] = [];

        // Ensure default admin user exists
        $hasAdmin = false;
        foreach ($this->db['users'] as $u) {
            if ($u['username'] === ADMIN_MGMT_USERNAME) {
                $hasAdmin = true;
                break;
            }
        }
        if (!$hasAdmin) {
            $this->db['users'][] = [
                'id' => 1,
                'username' => ADMIN_MGMT_USERNAME,
                'password' => ADMIN_MGMT_PASSWORD,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            $this->save();
        }
    }

    private function save() {
        file_put_contents($this->filePath, json_encode($this->db, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function getAttribute($attr) {
        if ($attr === PDO::ATTR_DRIVER_NAME) return 'json_storage';
        return null;
    }

    public function exec($sql) {
        return 0;
    }

    public function lastInsertId() {
        return $this->lastId;
    }

    public function query($sql) {
        return $this->prepare($sql);
    }

    public function prepare($sql) {
        return new class($this, $sql) {
            private $parent;
            private $sql;
            private $rows = [];
            private $index = 0;

            public function __construct($parent, $sql) {
                $this->parent = $parent;
                $this->sql = trim($sql);
            }

            public function execute($params = []) {
                $sql = $this->sql;
                $params = $params ?: [];

                // SELECT query handling
                if (stripos($sql, 'SELECT') === 0) {
                    $table = 'blogs';
                    if (stripos($sql, 'FROM users') !== false) $table = 'users';
                    elseif (stripos($sql, 'FROM orders') !== false) $table = 'orders';
                    elseif (stripos($sql, 'FROM enquiries') !== false) $table = 'enquiries';
                    elseif (stripos($sql, 'FROM blogs') !== false) $table = 'blogs';

                    $records = $this->parent->getTable($table);

                    // WHERE clause filtering
                    if (preg_match('/WHERE\s+(.+?)(?:\s+ORDER\s+BY|\s+LIMIT|$)/i', $sql, $wm)) {
                        $whereStr = trim($wm[1]);
                        $filtered = [];
                        foreach ($records as $row) {
                            $match = true;
                            if (stripos($whereStr, 'username = ?') !== false) {
                                if (($row['username'] ?? '') !== ($params[0] ?? '')) $match = false;
                            } elseif (stripos($whereStr, 'slug = ?') !== false) {
                                if (($row['slug'] ?? '') !== ($params[0] ?? '')) $match = false;
                            } elseif (stripos($whereStr, 'id = ?') !== false) {
                                if ((string)($row['id'] ?? '') !== (string)($params[0] ?? '')) $match = false;
                            } elseif (stripos($whereStr, "status = 'published'") !== false) {
                                if (($row['status'] ?? '') !== 'published') $match = false;
                            }
                            if ($match) $filtered[] = $row;
                        }
                        $records = $filtered;
                    }

                    // ORDER BY
                    if (stripos($sql, 'ORDER BY') !== false) {
                        usort($records, function($a, $b) {
                            $timeA = $a['created_at'] ?? $a['timestamp'] ?? 0;
                            $timeB = $b['created_at'] ?? $b['timestamp'] ?? 0;
                            if ($timeA === $timeB) {
                                return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
                            }
                            return strcmp($timeB, $timeA);
                        });
                    }

                    // LIMIT
                    if (preg_match('/LIMIT\s+(\d+)/i', $sql, $lm)) {
                        $records = array_slice($records, 0, (int)$lm[1]);
                    }

                    $this->rows = array_values($records);
                    $this->index = 0;
                    return true;
                }

                // INSERT query handling
                if (stripos($sql, 'INSERT INTO') === 0) {
                    $table = 'blogs';
                    if (stripos($sql, 'INTO users') !== false) $table = 'users';
                    elseif (stripos($sql, 'INTO orders') !== false) $table = 'orders';
                    elseif (stripos($sql, 'INTO enquiries') !== false) $table = 'enquiries';
                    elseif (stripos($sql, 'INTO blogs') !== false) $table = 'blogs';

                    $newId = $this->parent->getNextId($table);
                    $now = date('Y-m-d H:i:s');
                    $row = ['id' => $newId];

                    if ($table === 'users') {
                        $row['username'] = $params[0] ?? '';
                        $row['password'] = $params[1] ?? '';
                        $row['timestamp'] = $now;
                    } elseif ($table === 'enquiries') {
                        $row['name'] = $params[0] ?? '';
                        $row['email'] = $params[1] ?? '';
                        $row['phone'] = $params[2] ?? '';
                        $row['message'] = $params[3] ?? '';
                        $row['timestamp'] = $now;
                    } elseif ($table === 'orders') {
                        $row['representative_name'] = $params[0] ?? '';
                        $row['order_for'] = $params[1] ?? '';
                        $row['phone'] = $params[2] ?? '';
                        $row['email'] = $params[3] ?? '';
                        $row['date'] = $params[4] ?? '';
                        $row['remark'] = $params[5] ?? '';
                        $row['filename'] = $params[6] ?? null;
                        $row['timestamp'] = $now;
                    } elseif ($table === 'blogs') {
                        $row['title'] = $params[0] ?? '';
                        $row['slug'] = $params[1] ?? '';
                        $row['meta_description'] = $params[2] ?? '';
                        $row['author'] = $params[3] ?? 'SmarTech Team';
                        $row['category'] = $params[4] ?? 'Technology';
                        $row['featured_image'] = $params[5] ?? '';
                        $row['content'] = $params[6] ?? '';
                        $row['images_json'] = $params[7] ?? '{}';
                        $row['status'] = $params[8] ?? 'published';
                        $row['created_at'] = $params[9] ?? $now;
                        $row['updated_at'] = $params[10] ?? $now;
                    }

                    $this->parent->insertRow($table, $row);
                    $this->rows = [];
                    return true;
                }

                // UPDATE query handling
                if (stripos($sql, 'UPDATE') === 0) {
                    if (stripos($sql, 'UPDATE blogs') !== false) {
                        // UPDATE blogs SET title = ?, slug = ?, meta_description = ?, author = ?, category = ?, featured_image = ?, content = ?, images_json = ?, status = ?, updated_at = ? WHERE id = ?
                        $id = end($params);
                        $updates = [
                            'title' => $params[0] ?? '',
                            'slug' => $params[1] ?? '',
                            'meta_description' => $params[2] ?? '',
                            'author' => $params[3] ?? 'SmarTech Team',
                            'category' => $params[4] ?? 'Technology',
                            'featured_image' => $params[5] ?? '',
                            'content' => $params[6] ?? '',
                            'images_json' => $params[7] ?? '{}',
                            'status' => $params[8] ?? 'published',
                            'updated_at' => $params[9] ?? date('Y-m-d H:i:s')
                        ];
                        $this->parent->updateRow('blogs', $id, $updates);
                    }
                    return true;
                }

                // DELETE query handling
                if (stripos($sql, 'DELETE FROM') === 0) {
                    $table = 'blogs';
                    if (stripos($sql, 'FROM users') !== false) $table = 'users';
                    elseif (stripos($sql, 'FROM orders') !== false) $table = 'orders';
                    elseif (stripos($sql, 'FROM enquiries') !== false) $table = 'enquiries';
                    elseif (stripos($sql, 'FROM blogs') !== false) $table = 'blogs';

                    $id = $params[0] ?? 0;
                    $this->parent->deleteRow($table, $id);
                    return true;
                }

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
        };
    }

    public function getTable($table) {
        $this->load();
        return $this->db[$table] ?? [];
    }

    public function getNextId($table) {
        $rows = $this->getTable($table);
        $max = 0;
        foreach ($rows as $r) {
            if (($r['id'] ?? 0) > $max) $max = (int)$r['id'];
        }
        $this->lastId = $max + 1;
        return $this->lastId;
    }

    public function insertRow($table, $row) {
        $this->load();
        if (!isset($this->db[$table])) $this->db[$table] = [];
        $this->db[$table][] = $row;
        $this->save();
    }

    public function updateRow($table, $id, $updates) {
        $this->load();
        if (!isset($this->db[$table])) return;
        foreach ($this->db[$table] as &$row) {
            if ((string)$row['id'] === (string)$id) {
                foreach ($updates as $k => $v) {
                    $row[$k] = $v;
                }
                break;
            }
        }
        $this->save();
    }

    public function deleteRow($table, $id) {
        $this->load();
        if (!isset($this->db[$table])) return;
        $this->db[$table] = array_values(array_filter($this->db[$table], function($r) use ($id) {
            return (string)$r['id'] !== (string)$id;
        }));
        $this->save();
    }
}

