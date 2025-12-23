<?php
/**
 * Dashboard Page - Bug List
 * 
 * @project BugTracker by GoodStufForDev
 * @description Main dashboard with ticket list
 * @author [Your Name]
 * @date December 2025
 */

require_once __DIR__ . '/../app/config/database.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
    $new_status = (int)($_POST['status'] ?? 0);
    
    // Get current ticket status
    $stmt = $pdo->prepare("SELECT status FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $current = $stmt->fetch();
    
    if ($current) {
        $resolved_at = null;
        
        // If closing ticket, set resolved_at
        if ($new_status == 2 && $current['status'] != 2) {
            $resolved_at = date('Y-m-d H:i:s');
        }
        // If reopening closed ticket, clear resolved_at
        elseif ($new_status != 2 && $current['status'] == 2) {
            $resolved_at = null;
        }
        
        // Update ticket
        if ($resolved_at !== null || ($current['status'] == 2 && $new_status != 2)) {
            $stmt = $pdo->prepare("UPDATE tickets SET status = ?, resolved_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $resolved_at, $ticket_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $ticket_id]);
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle ticket deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    flashMessage('Ticket deleted successfully');
    redirect('index.php');
}

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Build query with filter
$sql = "SELECT t.*, 
               c.title as category_name,
               u_creator.name as creator_name,
               u_assigned.name as assigned_name
        FROM tickets t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN users u_creator ON t.created_by = u_creator.id
        LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
        WHERE 1=1";

$params = [];

// Apply filters
switch ($filter) {
    case 'my_tickets':
        $sql .= " AND t.created_by = ?";
        $params[] = $_SESSION['user_id'];
        break;
    case 'frontend':
        $sql .= " AND c.title = 'Front-end'";
        break;
    case 'backend':
        $sql .= " AND c.title = 'Back-end'";
        break;
    case 'infrastructure':
        $sql .= " AND c.title = 'Infrastructure'";
        break;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// Calculate statistics
$stats = [
    'total' => count($tickets),
    'open' => 0,
    'progress' => 0,
    'closed' => 0
];

foreach ($tickets as $ticket) {
    switch ($ticket['status']) {
        case 0: $stats['open']++; break;
        case 1: $stats['progress']++; break;
        case 2: $stats['closed']++; break;
    }
}

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <a href="create_bug.php" class="btn btn-primary">+ New</a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Tickets</div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-value"><?php echo $stats['open']; ?></div>
            <div class="stat-label">Open Tickets</div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-value"><?php echo $stats['progress']; ?></div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-value"><?php echo $stats['closed']; ?></div>
            <div class="stat-label">Closed Tickets</div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <label for="filter">Filter:</label>
        <select id="filter" onchange="window.location.href='index.php?filter=' + this.value">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Tickets</option>
            <option value="my_tickets" <?php echo $filter === 'my_tickets' ? 'selected' : ''; ?>>My Tickets</option>
            <option value="frontend" <?php echo $filter === 'frontend' ? 'selected' : ''; ?>>Category Front-end</option>
            <option value="backend" <?php echo $filter === 'backend' ? 'selected' : ''; ?>>Category Back-end</option>
            <option value="infrastructure" <?php echo $filter === 'infrastructure' ? 'selected' : ''; ?>>Category Infrastructure</option>
        </select>
    </div>
    
    <!-- Tickets Table -->
    <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <p>No tickets have been created yet.</p>
            <a href="create_bug.php" class="btn btn-primary">Create your first ticket</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Resolved At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                            <td>
                                <a href="bug_details.php?id=<?php echo $ticket['id']; ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($ticket['title']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-category">
                                    <?php echo htmlspecialchars($ticket['category_name']); ?>
                                </span>
                            </td>
                            <td><?php echo date('m/d/Y H:i', strtotime($ticket['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($ticket['creator_name']); ?></td>
                            <td>
                                <select 
                                    class="status-select status-<?php echo getStatusClass($ticket['status']); ?>" 
                                    data-ticket-id="<?php echo $ticket['id']; ?>"
                                    onchange="updateStatus(this)">
                                    <option value="0" <?php echo $ticket['status'] == 0 ? 'selected' : ''; ?>>Open</option>
                                    <option value="1" <?php echo $ticket['status'] == 1 ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="2" <?php echo $ticket['status'] == 2 ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </td>
                            <td>
                                <span class="badge badge-priority-<?php echo getPriorityClass($ticket['priority']); ?>">
                                    <?php echo getPriorityLabel($ticket['priority']); ?>
                                </span>
                            </td>
                            <td><?php echo $ticket['assigned_name'] ? htmlspecialchars($ticket['assigned_name']) : '-'; ?></td>
                            <td><?php echo $ticket['resolved_at'] ? date('m/d/Y H:i', strtotime($ticket['resolved_at'])) : '-'; ?></td>
                            <td class="actions-cell">
                                <a href="bug_details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
/**
 * Update ticket status via AJAX
 * @param {HTMLSelectElement} selectElement - The select element that triggered the change
 */
function updateStatus(selectElement) {
    const ticketId = selectElement.dataset.ticketId;
    const newStatus = selectElement.value;
    
    // Update visual state immediately
    selectElement.className = 'status-select status-' + getStatusClass(newStatus);
    
    // Send AJAX request to update database
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_status&ticket_id=${ticketId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update resolved_at date
            location.reload();
        } else {
            alert('Failed to update status. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

/**
 * Get CSS class for status
 * @param {string} status - Status value
 * @returns {string} CSS class name
 */
function getStatusClass(status) {
    const classes = {
        '0': 'open',
        '1': 'progress',
        '2': 'closed'
    };
    return classes[status] || 'open';
}
</script>

<?php include 'includes/footer.php'; ?>