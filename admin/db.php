<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'booking_lapangan');
if ($conn->connect_error) die("Connection failed");
$conn->set_charset("utf8mb4");

// Simple Query
function q($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result() ?: true;
}

// Fetch One
function fetchOne($sql, $params = []) {
    $result = q($sql, $params);
    return $result && $result->num_rows ? $result->fetch_assoc() : null;
}

// Fetch All
function fetchAll($sql, $params = []) {
    $result = q($sql, $params);
    return $result && $result->num_rows ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>