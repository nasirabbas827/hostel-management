<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$student_id = $_SESSION["id"];
$sql = "SELECT username, usertype FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 1) {
    $stmt->bind_result($username, $usertype);
    $stmt->fetch();
} else {
    header("location: index.php");
    exit;
}

$stmt->close();

// Fetch Pending Reminders
$reminder_query = "SELECT * FROM reminders WHERE user_id = ? AND is_completed = 0 ORDER BY reminder_date ASC";
$reminder_stmt = $conn->prepare($reminder_query);
$reminder_stmt->bind_param("i", $student_id);
$reminder_stmt->execute();
$reminder_result = $reminder_stmt->get_result();
$pending_reminders = [];
while ($row = $reminder_result->fetch_assoc()) {
    $pending_reminders[] = $row;
}
$reminder_stmt->close();


$search = "";
$hostels = [];
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $search_query = "SELECT * FROM hostels WHERE name LIKE ? OR location LIKE ?";
    $search_stmt = $conn->prepare($search_query);
    $search_param = "%" . $search . "%";
    $search_stmt->bind_param("ss", $search_param, $search_param);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
} else {
    $search_query = "SELECT * FROM hostels";
    $search_stmt = $conn->prepare($search_query);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
}
while ($row = $search_result->fetch_assoc()) {
    $hostels[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .hostel-image {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            flex-grow: 1;
        }
        .btn-success {
            margin-top: auto;
        }
        #map {
            height: 500px;
            width: 100%;
            margin-top: 40px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
        .search-form {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 12px;
    transition: box-shadow 0.3s ease-in-out;
}

.search-form:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.search-form input[type="text"] {
    border-radius: 8px 0 0 8px;
    border-right: none;
    box-shadow: none;
    font-size: 1.1rem;
}

.search-form .input-group-append .btn {
    border-radius: 0 8px 8px 0;
    font-weight: 500;
    background-color: #007bff;
    border-color: #007bff;
    transition: background-color 0.2s ease-in-out;
}

.search-form .input-group-append .btn:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

    </style>
</head>
<body>
<?php include('navbar.php'); ?>



<div class="container mt-5">

<?php if (!empty($pending_reminders)): ?>
    <?php foreach ($pending_reminders as $reminder): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>⏰ Reminder:</strong> <?php echo htmlspecialchars($reminder['title']); ?>
            <br><small>⏳ Due: <?php echo date("F j, Y, g:i A", strtotime($reminder['reminder_date'])); ?></small>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

<!-- Search Bar -->
<form method="POST" class="search-form p-4 mb-4 shadow-sm">
    <div class="form-group mb-3">
        <label for="search" class="font-weight-bold text-secondary">🔍 Search Hostels by Name or Location</label>
        <div class="input-group">
            <input type="text" name="search" id="search" class="form-control form-control-lg" 
                   value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="e.g. Lahore, Green Hostel, etc.">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
    
</form>

<h1>Available Hostels</h1>


    <!-- Display Hostels -->
    <div class="row mt-5">
        
        <?php
        if (!empty($hostels)) {
            foreach ($hostels as $index => $hostel):
        ?>
            <div class="col-md-4 d-flex align-items-stretch">
                <div class="card w-100">
                    <img src="admin/uploads/<?php echo htmlspecialchars($hostel['image']); ?>" class="card-img-top hostel-image" alt="Hostel Image">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($hostel['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($hostel['description']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($hostel['location']); ?></p>
                        <div class="mt-auto">
                            <a href="view_rooms.php?hostel_id=<?php echo $hostel['hostel_id']; ?>" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-door-open"></i> View Rooms
                            </a>
                            <a href="contact_manager.php?hostel_id=<?php echo $hostel['hostel_id']; ?>" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-user-tie"></i> Contact Manager
                            </a>
                            <a href="feedbacks.php?hostel_id=<?php echo $hostel['hostel_id']; ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-comments"></i> Feedbacks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        } else {
            echo "<p>No hostels found matching your search criteria.</p>";
        }
        ?>
    </div>
<h2>Hostels On Map</h2>
    <!-- Google Map -->
    <div id="map"></div>
</div>

<!-- Google Maps Script -->
<script>
function initMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 6,
        center: { lat: 30.3753, lng: 69.3451 } // Centered in Pakistan
    });

    const markers = <?php echo json_encode($hostels); ?>;

    markers.forEach(h => {
        if (h.latitude && h.longitude) {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(h.latitude), lng: parseFloat(h.longitude) },
                map: map,
                title: h.name
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<h6>${h.name}</h6><p>${h.location}</p>`
            });

            marker.addListener("click", () => {
                infoWindow.open(map, marker);
            });
        }
    });
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP4cSBJ4IHPp15oyTcJgWo7kDt06Vh4jE&callback=initMap"></script>

<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
