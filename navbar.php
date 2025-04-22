<!--CSS file link-->
<link rel="stylesheet" href="style.css">

<!--font awesome cdn link-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!--Swiper link-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

</head>

<body>

    <header class="header">

        <!--Navbar-->
        <div id="menu-bar" class="fas fa-bars"></div>

        <a href="index.php" class="logo"><span>R</span>oomNest </a>

        <nav class="navbar">
            <?php
            // If the user is logged out
            if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
                // Show links when the user is logged out
                echo '<a href="index.php#home">Home</a>';
                echo '<a href="about.php">About Us</a>';
                echo '<a href="index.php#book">Location</a>';
                echo '<a href="index.php#services">Services</a>';
                echo '<a href="index.php#gallery">Gallery</a>';
                echo '<a href="index.php#review">Review</a>';
                echo '<a href="login.php" class="bttn">Login</a>';
                echo '<a href="admin/admin_login.php" class="bttn">Admin Login</a>';
            } else {
                // If the user is logged in, show user-specific links
                echo '<a href="home.php">Home</a>';
                echo '<a href="update_profile.php">Profile</a>';
                echo '<a href="view_bookings.php">Bookings</a>';
                echo '<a href="manage_reminders.php">Reminders</a>';
                echo '<a href="logout.php">Logout</a>';
            }
            ?>
        </nav>
    </header>
</body>
