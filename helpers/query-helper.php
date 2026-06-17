<?php
function get_saldo(PDO $pdo): float
{
    $stmt = $pdo->query('SELECT id, saldo FROM saldo ORDER BY id ASC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->exec('INSERT INTO saldo (saldo) VALUES (0.00)');
        return 0.0;
    }

    return (float) $row['saldo'];
}

function update_saldo(PDO $pdo, float $delta): float
{
    $stmt = $pdo->query('SELECT id, saldo FROM saldo ORDER BY id ASC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $newSaldo = $delta;
        $insert = $pdo->prepare('INSERT INTO saldo (saldo) VALUES (:saldo)');
        $insert->execute(['saldo' => $newSaldo]);
        return $newSaldo;
    }

    $newSaldo = (float) $row['saldo'] + $delta;
    $update = $pdo->prepare('UPDATE saldo SET saldo = :saldo WHERE id = :id');
    $update->execute(['saldo' => $newSaldo, 'id' => $row['id']]);

    return $newSaldo;
}

function get_pengaturan(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM pengaturan ORDER BY id ASC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->exec("INSERT INTO pengaturan (nama_organisasi) VALUES ('Karang Taruna 001 Lebak Bulus')");
        $stmt = $pdo->query('SELECT * FROM pengaturan ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return $row ?: [];
}

function table_has_column(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $cacheKey = $table . '.' . $column;

    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table_name
           AND COLUMN_NAME = :column_name'
    );
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);

    $cache[$cacheKey] = (int) $stmt->fetchColumn() > 0;

    return $cache[$cacheKey];
}
