<?php
$connectionString = "host=127.0.0.1 port=5432 dbname=ina_zaoui_test user=postgres password=winterborn";
$conn = pg_connect($connectionString);

if (!$conn) {
    echo "An error occurred while connecting to the database.\n";
} else {
    echo "Connection to the database was successful.\n";
}
?>