<?php
/**
 * Login Page
 * 
 * @project BugTracker by GoodStufPrinceMD
 * @description User authentication page
 * @author PrinceMD
 * @date December 2025
 */

require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // Query user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Success message and redirect
            flashMessage("Welcome back, {$user['name']}!");
            redirect('index.php');
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Login</h1>
        <p style="color: #6C757D; margin-bottom: 1.5rem;">
            Sign in to access your bug tracking dashboard
        </p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="admin@bugtracker.com"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Enter your password"
                    autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Subscribe</a></p>
        </div>
        
        <div class="auth-demo">
            <h3>Test Account:</h3>
            <p><strong>Email:</strong> prince@bugtracker.com</p>
            <p><strong>Password:</strong> 111111</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>