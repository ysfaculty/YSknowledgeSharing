<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

class Database
{
    private $connection;

    public function __construct()
    {
        $host = env('ORACLE_HOST', 'localhost');
        $port = env('ORACLE_PORT', '1521');
        $service = env('ORACLE_SERVICE', 'FREEPDB1');
        $username = env('ORACLE_USER', 'community');
        $password = env('ORACLE_PASSWORD', 'community123');

        $dsn = "//{$host}:{$port}/{$service}";
        $conn = @oci_connect($username, $password, $dsn, 'AL32UTF8');
        if (!$conn) {
            $e = oci_error();
            throw new RuntimeException('Oracle DB 연결 실패: ' . ($e['message'] ?? 'unknown error'));
        }

        $this->connection = $conn;
    }

    public function conn()
    {
        return $this->connection;
    }

    public function fetchAll(string $sql, array $bindings = []): array
    {
        $stmt = $this->prepare($sql, $bindings);
        oci_execute($stmt);
        $rows = [];
        while (($row = oci_fetch_assoc($stmt)) !== false) {
            $rows[] = array_change_key_case($row, CASE_LOWER);
        }
        oci_free_statement($stmt);

        return $rows;
    }

    public function fetchOne(string $sql, array $bindings = []): ?array
    {
        $rows = $this->fetchAll($sql, $bindings);
        return $rows[0] ?? null;
    }

    public function execute(string $sql, array $bindings = []): void
    {
        $stmt = $this->prepare($sql, $bindings);
        $ok = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) {
            $e = oci_error($stmt);
            oci_free_statement($stmt);
            throw new RuntimeException('SQL 실행 오류: ' . ($e['message'] ?? 'unknown error'));
        }
        oci_free_statement($stmt);
    }

    private function prepare(string $sql, array $bindings)
    {
        $stmt = oci_parse($this->connection, $sql);
        if (!$stmt) {
            $e = oci_error($this->connection);
            throw new RuntimeException('SQL 파싱 오류: ' . ($e['message'] ?? 'unknown error'));
        }

        foreach ($bindings as $key => $value) {
            $name = str_starts_with($key, ':') ? $key : ':' . $key;
            oci_bind_by_name($stmt, $name, $bindings[$key]);
        }

        return $stmt;
    }
}
