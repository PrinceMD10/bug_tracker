<?php
/**
 * Register Page (Subscribe)
 * 
 * @project BugTracker by GoodStufForDev
 * @description User registration page
 * @author [Your Name]
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
    // Sanitize inputs
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Please fill all fields';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $hashed_password])) {
                flashMessage('Account created successfully! You can now login.');
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Subscribe';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Subscribe</h1>
        <p style="color: #6C757D; margin-bottom: 1.5rem;">
            Create your account to start tracking bugs
        </p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    placeholder="John Doe"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    autocomplete="name">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="john@example.com"
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
                    placeholder="At least 6 characters"
                    minlength="6"
                    autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Password Verification</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required
                    placeholder="Confirm your password"
                    autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Subscribe</button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>