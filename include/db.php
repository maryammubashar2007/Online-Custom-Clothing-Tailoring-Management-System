<?php
$serverName = "localhost";
$connectionInfo = array(
    "Database"               => "VelvaraDB",
    "UID"                    => "sa",
    "PWD"                    => "YourPassword123",
    "CharacterSet"           => "UTF-8",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    echo "<div style='background:#ff4444;color:#fff;padding:20px;font-family:monospace;'>";
    echo "<strong>Database Connection Failed:</strong><br>";
    print_r(sqlsrv_errors());
    echo "</div>";
    die();
}
?>
