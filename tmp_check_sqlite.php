<?php
try {
    $p = new PDO('sqlite::memory:');
    echo "ok\n";
} catch (Exception $e) {
    echo "error: " . $e->getMessage() . "\n";
}
