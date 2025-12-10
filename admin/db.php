<?php
/**
 * Database Connection & Query Wrapper
 * Futsal Reservation Admin Panel
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'booking_lapangan');

// Global Database Connection
$conn = null;

/**
 * Initialize Database Connection
 */
function initDB() {
    global $conn;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to UTF-8
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

/**
 * Query Wrapper Function with Prepared Statement
 * 
 * @param string $sql SQL query with placeholders (?)
 * @param array $params Array of parameters to bind
 * @return mysqli_result|bool Query result or false on error
 */
function q($sql, $params = []) {
    global $conn;
    
    // Initialize connection if not exists
    if ($conn === null) {
        initDB();
    }
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Bind parameters if provided
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b'; // blob
            }
            $bindParams[] = $param;
        }
        
        // Bind parameters dynamically
        $stmt->bind_param($types, ...$bindParams);
    }
    
    // Execute statement
    $execute = $stmt->execute();
    
    if ($execute === false) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    // Get result for SELECT queries
    $result = $stmt->get_result();
    
    // For INSERT/UPDATE/DELETE, return true/false
    if ($result === false) {
        return $execute;
    }
    
    return $result;
}

/**
 * Fetch single row as associative array
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|null Single row or null
 */
function fetchOne($sql, $params = []) {
    $result = q($sql, $params);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Fetch all rows as associative array
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array Array of rows
 */
function fetchAll($sql, $params = []) {
    $result = q($sql, $params);
    $rows = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

/**
 * Get last inserted ID
 * 
 * @return int Last insert ID
 */
function lastInsertId() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Get affected rows from last query
 * 
 * @return int Number of affected rows
 */
function affectedRows() {
    global $conn;
    return $conn->affected_rows;
}

/**
 * Get last error message
 * 
 * @return string Error message
 */
function getError() {
    global $conn;
    return $conn->error;
}

/**
 * Escape string (use sparingly, prefer prepared statements)
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function escape($string) {
    global $conn;
    if ($conn === null) {
        initDB();
    }
    return $conn->real_escape_string($string);
}

/**
 * Close database connection
 */
function closeDB() {
    global $conn;
    if ($conn !== null) {
        $conn->close();
        $conn = null;
    }
}

// Initialize connection on include
initDB();
?>