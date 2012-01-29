<?php

if (!dl('php_azure')) {
    echo 'Failed to load php_azure.dll.'.PHP_EOL;

    exit(1);
}

try {
    $dsn = sprintf('sqlsrv:Server=%s; Database=%s;', azure_getconfig('OPENPNE_DB_HOST'), azure_getconfig('OPENPNE_DB_NAME'));
    $pdo = new PDO($dsn, azure_getconfig('OPENPNE_DB_USER'), azure_getconfig('OPENPNE_DB_PASSWORD'));
} catch (PDOException $e) {
    echo 'Connecting to database was failed with the following error(s): '.PHP_EOL.$e->getMessage().PHP_EOL;

    exit(1);
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM sysobjects WHERE xtype = ?');
$stmt->execute(array('U'));
$result = $stmt->fetchColumn();

exit((0 < $result) ? 0 : 1);
