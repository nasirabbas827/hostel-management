<?php
include('config.php');

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Home</title>

    <!--CSS file link-->
    <link rel="stylesheet" href="style.css">

    <!--font awesome cdn link-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!--Swiper link-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<style>
            #map {
            height: 500px;
            width: 100%;
            margin-top: 40px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
</style>

</head>

<body>

<?php include('navbar.php'); ?>


    <!--Home section-->
<section class="home" id="home">
    <div class="content">
        <h3>Home away from home</h3>
        <p>discover new places with us, Shared rooms, shared stories</p>
        <a href="about.php" class="btn">discover more</a>
    </div>
</section>

<style>
.home {
    background: url('images/hotel.jpg') no-repeat center center/cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 60px 20px;
    color: #fff;
    position: relative;
    z-index: 1;
}

.home::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* dark overlay */
    z-index: -1;
}

.home .content {
    max-width: 600px;
    z-index: 2;
}
</style>



    <!--about-->
    <section class="about">
        <h1 class="heading">
            <span>a</span>
            <span>b</span>
            <span>o</span>
            <span>u</span>
            <span>t</span>
            <span class="space"></span>
            <span>u</span>
            <span>s</span>
        </h1>

        <div class="about-us">
    <div class="text">
        <p>Tired of endlessly scrolling through booking sites, unsure of which hostel is the perfect fit for your next adventure? Look no further! Our website is your one-stop shop for finding the ideal hostel near you. We understand that location is key, so we've designed our platform to make it incredibly easy to discover hostels in your desired area. Simply enter your current location or your destination, and our powerful search engine will instantly pull up a comprehensive list of hostels nearby. But we're more than just a directory</p>
        <p>We provide detailed information about each hostel, including photos, amenities, guest reviews, and pricing, empowering you to make an informed decision. Whether you're a budget backpacker looking for a social hub, a solo traveler seeking a quiet retreat, or a group of friends planning an epic getaway, our website will help you find the perfect place to lay your head and connect with fellow travelers. Start exploring now and unlock a world of affordable and unforgettable travel experiences!</p>
        <a href="about.html" class="btn">read more</a>
    </div>

    <!-- Hostel icon -->
    <div class="box" style="color: #ffa500;">
        <i class="fas fa-hotel fa-9x"></i>
    </div>
</div>


    </section>


    <!--Location-->
    <section class="book" id="book">
        <h1 class="heading">
            <span>l</span>
            <span>o</span>
            <span>c</span>
            <span>a</span>
            <span>t</span>
            <span>i</span>
            <span>o</span>
            <span>n</span>
        </h1>

        <div class="row">

            <div class="image">
                <img src="../Picture/booking.png" alt="">
            </div>

            <form action="">

                <div class="inputBox">
                    <h3>where to</h3>
                    <input type="place" placeholder="place name">
                </div>
                <div class="inputBox">
                    <h3>arrivals</h3>
                    <input type="date">
                </div>
                <input type="submit" class="btn" value="search">
            </form>
        </div>
    </section>


    <!--Services-->
    <section class="services" id="services">
        <h1 class="heading">
            <span>s</span>
            <span>e</span>
            <span>r</span>
            <span>v</span>
            <span>i</span>
            <span>c</span>
            <span>e</span>
            <span>s</span>
        </h1>

        <div class="box-container">
            <div class="box">
                <i class="fas fa-hotel"></i>
                <h3>affordable hostels</h3>
                <p>Discover a wide range of budget-friendly hostels that suit your living needs on our website. Enjoy comfortable and affordable accommodations with various amenities to enhance your stay. Book now and experience a memorable journey without
                    breaking the bank.</p>
            </div>

            <div class="box">
                <i class="fas fa-utensils"></i>
                <h3>food and drinks</h3>
                <p>Hostel food and drinks can be a surprisingly diverse and social experience. While some hostels offer basic, budget-friendly meals, the food and drink scene in hostels often fosters a sense of community and creates lasting memories. Be prepared for everything from simple pasta nights to unexpected culinary delights, all shared in a spirit of adventure. join us now!</p>
            </div>

            <div class="box">
                <i class="fas fa-bullhorn"></i>
                <h3>safety guide</h3>
                <p>Hostels often provide a base level of security to ensure a safe environment for their guests.  This typically includes secure access to the building, often with key cards or coded entry.  Many hostels offer individual lockers or safes for storing valuables, and some may have 24-hour reception or security personnel.  Common areas are usually monitored, and staff are generally available to assist with any safety concerns.</p>
            </div>

            <div class="box">
                <i class="fas fa-camera"></i>
                <h3>Security Cameras</h3>
                <p>Security cameras are increasingly common in hostels, often placed in common areas like lobbies, hallways. These cameras serve as a deterrent to theft and can provide valuable evidence in case of incidents.  While security cameras can enhance safety, it's important to be aware of their presence and understand their purpose. 
                </p>
            </div>

    </section>

<!--Gallery-->
<section class="gallery" id="gallery">
    <h1 class="heading">
        <span>g</span>
        <span>a</span>
        <span>l</span>
        <span>l</span>
        <span>e</span>
        <span>r</span>
        <span>y</span>
    </h1>

    <div class="box-container">
        <?php
        include 'config.php';

        $query = "SELECT * FROM hostels";
        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            $imagePath = "admin/uploads/" . htmlspecialchars($row['image']);
            $altText = htmlspecialchars($row['name']);
            echo '<div class="box">';
            echo '<img src="' . $imagePath . '" alt="' . $altText . '">';
            echo '</div>';
        }
        ?>
    </div>
</section>

<section >
<h1 class="heading">
        <span>H</span>
        <span>o</span>
        <span>s</span>
        <span>t</span>
        <span>e</span>
        <span>l</span>
        <span>s</span>
    </h1>
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
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_OWN_API_KEY&callback=initMap"></script>


</section>



<!--Review-->
<section class="review" id="review">
    <h1 class="heading">
        <span>r</span>
        <span>e</span>
        <span>v</span>
        <span>i</span>
        <span>e</span>
        <span>w</span>
    </h1>

    <div class="swiper review-slider">
        <div class="swiper-wrapper">
            <?php
            include 'config.php';

            $query = "SELECT f.*, u.username AS user_name 
                      FROM feedbacks f 
                      JOIN users u ON f.user_id = u.id 
                      ORDER BY f.created_at DESC LIMIT 10";
            $result = $conn->query($query);

            while ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row['user_name']);
                $message = htmlspecialchars($row['message']);
                $rating = (int)$row['rating'];

                echo '<div class="swiper-slide">
                        <div class="boxe">
                            <!-- Use a user icon if no image is available -->
                            <i class="fas fa-user-circle" style="font-size: 50px;"></i>
                            <h3>' . $name . '</h3>
                            <p>' . $message . '</p>
                            <div class="stars">';
                
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $rating) {
                        echo '<i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }

                echo        '</div>
                        </div>
                    </div>';
            }
            ?>
        </div>
        <br><br>
        <div class="swiper-pagination"></div>
    </div>
</section>

<script>
    var swiper = new Swiper(".review-slider", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            }
        }
    });
</script>



    <!--Subscribe-->
    <section class="subsribe">
        <div class="hero">
            <h3>Coming soon!</h3>
            <h1><span>new hostels </span>are on its way</h1>
            <p>Subscribe for more details.</p>
            <div class="form-container">
            <input type="email" name="Email" placeholder="Enter your Email" required 
       style="width: 40%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;"> <br>
                <button type="submit">Subscribe now</button>
            </div>
        </div>
    </section>


    <!--Footer-->

    <section class="footer">
        <div class="box-container">

            <div class="box">
                <h3>about us</h3>
                <p>we always make our customers happy by providing as many choices as possible.</p>
            </div>

            <div class="box">
                <h3>quick links</h3>
                <a href="index.php">Home</a>
                <a href="about.html">About us</a>
                <a href="#book">book</a>
                <a href="#services">services</a>
                <a href="#gallery">gallery</a>
                <a href="#review">review</a>

            </div>

            <div class="box">
                <h3>company</h3>
                <a href="contact.html">contact us</a>
                <a href="privacy.html">privacy policy</a>
                <a href="faqs.html">faqs</a>
                <a href="support.html">supports</a>
                <a href="terms.html">terms & conditions</a>
            </div>

            <div class="box">
                <h3>follow us</h3>
                <a href="#"><i class="fab fa-facebook-f"></i>  facebook</a>
                <a href="#"><i class="fab fa-instagram"></i>  instagram</a>
                <a href="#"><i class="fab fa-twitter"></i>  twitter</a>
                <a href="#"><i class="fab fa-linkedin"></i>  linkedin</a>
            </div>

        </div>

        <h1 class="credit"> © 2025. All Rights Reserved by <span> ROOMNEST </span></h1>
    </section>



    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <!--JavaScript file link-->
    <script src="script.js"></script>


</body>

</html>