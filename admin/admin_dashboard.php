<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css"> <!-- Link to the updated CSS file -->
    <title>Admin Dashboard</title>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <span class="close-btn" onclick="closeSidebar()">&times;</span>
        <nav>
            <ul>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_dogs.php">Manage Dogs</a></li>
                <li><a href="manage_grooming.php">Manage Grooming Services</a></li>
                <li><button class="logout-btn" onclick="window.location.href='../logout.php'">Logout</button></li>
            </ul>
        </nav>
    </div>

    <!-- Main content area -->
    <div id="main-content" class="main-content">
        <!-- Hamburger icon -->
        <span class="hamburger" onclick="openSidebar()">&#9776;</span>
    </div>

    <script>
        // Open sidebar
        function openSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");
            
            sidebar.classList.add("active");
            mainContent.classList.add("shift");
        }

        // Close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");

            sidebar.classList.remove("active");
            mainContent.classList.remove("shift");
        }
    </script>
</body>
</html>
