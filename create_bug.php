<?php
/**
 * Create Bug Page
 * 
 * @project BugTracker by GoodStufPrinceMD
 * @description Create new ticket form
 * @author PrinceMD
 * @date December 2025
 */

require_once 'config/database.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = sanitize($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $priority = (int)($_POST['priority'] ?? 1);
    $status = (int)($_POST['status'] ?? 0);
    $assigned_to = $_POST['assigned_to'] ?? null;
    
    // Convert empty string to null for assigned_to
    if ($assigned_to === '') {
        $assigned_to = null;
    } else {
        $assigned_to = (int)$assigned_to;
    }
    
    // Validation
    if (empty($title)) {
        $error = 'Title is required';
    } elseif ($category_id <= 0) {
        $error = 'Please select a category';
    } elseif (strlen($title) < 5) {
        $error = 'Title must be at least 5 characters';
    } else {
        // CREATE new ticket
        $resolved_at = ($status == 2) ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO tickets (title, category_id, priority, status, created_by, assigned_to, resolved_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$title, $category_id, $priority, $status, $_SESSION['user_id'], $assigned_to, $resolved_at])) {
            flashMessage('Ticket created successfully!');
            redirect('index.php');
        } else {
            $error = 'Failed to create ticket. Please try again.';
        }
    }
}

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY title")->fetchAll();

// Get users for assignment dropdown
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

$pageTitle = 'New Ticket';
include 'includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h1>New Ticket</h1>
            <a href="index.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="ticket-form">
            <!-- Title -->
            <div class="form-group">
                <label for="title">Title *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required 
                    placeholder="Enter ticket title (min. 5 characters)"
                    value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                    minlength="5">
            </div>
            
            <div class="form-row">
                <!-- Category -->
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Priority -->
                <div class="form-group">
                    <label for="priority">Priority Level</label>
                    <select id="priority" name="priority">
                        <option value="0" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 0) ? 'selected' : ''; ?>>
                            0: Low
                        </option>
                        <option value="1" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == 1) ? 'selected' : ''; ?>>
                            1: Standard
                        </option>
                        <option value="2" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 2) ? 'selected' : ''; ?>>
                            2: High
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <!-- Status -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="0" selected>0: Open</option>
                        <option value="1">1: In Progress</option>
                        <option value="2">2: Closed</option>
                    </select>
                </div>
                
                <!-- Assigned To -->
                <div class="form-group">
                    <label for="assigned_to">Assigned To</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Not assigned</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"
                                    <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Save</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>