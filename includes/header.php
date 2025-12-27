<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BugTracker - Simple bug tracking for development teams">
    <meta name="author" content="GoodStufForDev">
    <title><?php echo $pageTitle ?? 'BugTracker'; ?> | GoodStufForDev</title>
    
    <!-- Google Fonts: Space Grotesk -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Navigation Bar (Private Pages Only) -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">
                    <span class="logo-icon">🐞</span>
                    <span class="logo-text">BugTracker</span>
                </a>
                <span class="company-tag">by GoodStuf_PrinceMD</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li class="user-info">
                    <span class="user-icon">🧑‍💼</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container">
        <?php displayFlash(); ?>