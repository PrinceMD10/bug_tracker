<?php
/**
 * Bug Details Page
 * 
 * @project BugTracker by GoodStufPrinceMD
 * @description View and edit ticket details
 * @author PrinceMD
 * @date December 2025
 */

require_once 'config/database.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$ticket_id = (int)($_GET['id'] ?? 0);

// Get ticket data
$stmt = $pdo->prepare("
    SELECT t.*, 
           c.title as category_name,
           u_creator.name as creator_name,
           u_assigned.name as assigned_name
    FROM tickets t
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN users u_creator ON t.created_by = u_creator.id
    LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
    WHERE t.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    flashMessage('Ticket not found', 'error');
    redirect('index.php');
}

$error = '';

// Handle form submission (UPDATE)
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
        // UPDATE existing ticket
        $resolved_at = $ticket['resolved_at'];
        
        // Handle resolved_at based on status change
        if ($status == 2 && $ticket['status'] != 2) {
            // Closing ticket
            $resolved_at = date('Y-m-d H:i:s');
        } elseif ($status != 2 && $ticket['status'] == 2) {
            // Reopening closed ticket
            $resolved_at = null;
        }
        
        $stmt = $pdo->prepare("
            UPDATE tickets 
            SET title = ?, category_id = ?, priority = ?, status = ?, assigned_to = ?, resolved_at = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$title, $category_id, $priority, $status, $assigned_to, $resolved_at, $ticket_id])) {
            flashMessage('Ticket updated successfully!');
            redirect('index.php');
        } else {
            $error = 'Failed to update ticket. Please try again.';
        }
    }
}

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY title")->fetchAll();

// Get users for assignment dropdown
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

$pageTitle = 'Edit Ticket #' . $ticket['id'];
include 'includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h1>Edit Ticket #<?php echo $ticket['id']; ?></h1>
            <a href="index.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Ticket Information -->
        <div class="info-box" style="margin-bottom: 1.5rem;">
            <strong>Created by:</strong> <?php echo htmlspecialchars($ticket['creator_name']); ?><br>
            <strong>Created at:</strong> <?php echo date('m/d/Y H:i', strtotime($ticket['created_at'])); ?>
            <?php if ($ticket['resolved_at']): ?>
                <br><strong>Resolved at:</strong> <?php echo date('m/d/Y H:i', strtotime($ticket['resolved_at'])); ?>
            <?php endif; ?>
        </div>
        
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
                    value="<?php echo htmlspecialchars($ticket['title']); ?>"
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
                                    <?php echo ($ticket['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Priority -->
                <div class="form-group">
                    <label for="priority">Priority Level</label>
                    <select id="priority" name="priority">
                        <option value="0" <?php echo ($ticket['priority'] == 0) ? 'selected' : ''; ?>>
                            0: Low
                        </option>
                        <option value="1" <?php echo ($ticket['priority'] == 1) ? 'selected' : ''; ?>>
                            1: Standard
                        </option>
                        <option value="2" <?php echo ($ticket['priority'] == 2) ? 'selected' : ''; ?>>
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
                        <option value="0" <?php echo ($ticket['status'] == 0) ? 'selected' : ''; ?>>
                            0: Open
                        </option>
                        <option value="1" <?php echo ($ticket['status'] == 1) ? 'selected' : ''; ?>>
                            1: In Progress
                        </option>
                        <option value="2" <?php echo ($ticket['status'] == 2) ? 'selected' : ''; ?>>
                            2: Closed
                        </option>
                    </select>
                </div>
                
                <!-- Assigned To -->
                <div class="form-group">
                    <label for="assigned_to">Assigned To</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Not assigned</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"
                                    <?php echo ($ticket['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
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