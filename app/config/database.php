<?php
/**
 * Database Configuration
 *
 * Project: BugTracker
 * Description: Database connection and common helper functions
 * Author: PrinceMD10
 * Date: December 2025
 */

/**
 * Start session only if not already started
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Database configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'bug_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * PDO database connection
 */
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Log technical error
    error_log('[Database error] ' . $e->getMessage());

    // Display generic message to user
    die('Connection error. Please try again later.');
}

/* ======================================================
   Helper functions
   ====================================================== */

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to a page
 */
function redirect(string $page): void
{
    header('Location: ' . $page);
    exit;
}

/**
 * Set flash message
 */
function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Display flash message
 */
function displayFlash(): void
{
    if (!empty($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        echo '<div class="alert alert-' . htmlspecialchars($type) . '">';
        echo htmlspecialchars($_SESSION['flash_message']);
        echo '</div>';

        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * Sanitize input
 */
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Priority label
 */
function getPriorityLabel(int $priority): string
{
    return match ($priority) {
        0 => 'Low',
        1 => 'Standard',
        2 => 'High',
        default => 'Standard',
    };
}

/**
 * Status label
 */
function getStatusLabel(int $status): string
{
    return match ($status) {
        0 => 'Open',
        1 => 'In Progress',
        2 => 'Closed',
        default => 'Open',
    };
}

/**
 * Priority CSS class
 */
function getPriorityClass(int $priority): string
{
    return match ($priority) {
        0 => 'low',
        1 => 'standard',
        2 => 'high',
        default => 'standard',
    };
}

/**
 * Status CSS class
 */
function getStatusClass(int $status): string
{
    return match ($status) {
        0 => 'open',
        1 => 'progress',
        2 => 'closed',
        default => 'open',
    };
}
