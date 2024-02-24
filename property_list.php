<?php
session_start();
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$city_name = $_GET["city"];

// Fetch city details
$sql_1 = "SELECT * FROM cities WHERE name = '$city_name'";
$result_1 = mysqli_query($conn, $sql_1);
if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo "Sorry! We do not have any PG listed in this city.";
    return;
}
$city_id = $city['id'];

// Fetch all properties in the city
$orderBy = '';

// Check if sorting is specified in the URL
if (isset($_GET['sort'])) {
    $sortType = $_GET['sort'];

    switch ($sortType) {
        case 'highest_price':
            $orderBy = 'ORDER BY rent DESC';
            break;
        case 'lowest_price':
            $orderBy = 'ORDER BY rent ASC';
            break;
        default:
            // Handle other cases or provide a default behavior
            break;
    }

    // Store sorting option in session
    $_SESSION['sort'] = $sortType;
} elseif (isset($_SESSION['sort'])) {
    // Use sorting option from session
    $sortType = $_SESSION['sort'];
    switch ($sortType) {
        case 'highest_price':
            $orderBy = 'ORDER BY rent DESC';
            break;
        case 'lowest_price':
            $orderBy = 'ORDER BY rent ASC';
            break;
        default:
            // Handle other cases or provide a default behavior
            break;
    }
}

// Filter properties based on gender (You can extend this as needed)
if (isset($_GET['gender'])) {
    $selectedGender = $_GET['gender'];
    if ($selectedGender != 'all') {
        // Store filter option in session
        $_SESSION['gender'] = $selectedGender;

        // Filter properties based on gender
        $sql_2 = "SELECT * FROM properties WHERE city_id = $city_id AND gender = '$selectedGender' $orderBy";
    } else {
        // Clear gender filter from session
        unset($_SESSION['gender']);
        
        // Fetch all properties without gender filter
        $sql_2 = "SELECT * FROM properties WHERE city_id = $city_id $orderBy";
    }
} elseif (isset($_SESSION['gender'])) {
    // Use gender filter from session
    $selectedGender = $_SESSION['gender'];
    $sql_2 = "SELECT * FROM properties WHERE city_id = $city_id AND gender = '$selectedGender' $orderBy";
} else {
    // Fetch all properties without gender filter
    $sql_2 = "SELECT * FROM properties WHERE city_id = $city_id $orderBy";
}

$result_2 = mysqli_query($conn, $sql_2);

if (!$result_2) {
    echo "Something went wrong!";
    return;
}
$properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);

// Fetch interested users properties
$sql_3 = "SELECT * 
            FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE p.city_id = $city_id";
$result_3 = mysqli_query($conn, $sql_3);
if (!$result_3) {
    echo "Something went wrong!";
    return;
}
$interested_users_properties = mysqli_fetch_all($result_3, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo $city_name ?> | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo $city_name; ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="filter-bar row justify-content-around">
            <div class="col-auto" data-toggle="modal" data-target="#filter-modal">
                <img src="img/filter.png" alt="filter" />
                <span>Filter</span>
            </div>
            <div class="col-auto">
                <a href="?city=<?= $city_name ?>&sort=highest_price">
                    <img src="img/desc.png" alt="sort-desc" />
                    <span>Highest rent first</span>
                </a>
            </div>
            <div class="col-auto">
                <a href="?city=<?= $city_name ?>&sort=lowest_price">
                    <img src="img/asc.png" alt="sort-asc" />
                    <span>Lowest rent first</span>
                </a>
            </div>
        </div>

        <?php
        foreach ($properties as $property) {
            $property_images = glob("img/properties/" . $property['id'] . "/*");
        ?>
            <div class="property-card property-id-<?= $property['id'] ?> row">
                <div class="image-container col-md-4">
                    <img src="<?= $property_images[0] ?>" />
                </div>
                <div class="content-container col-md-8">
                    <div class="row no-gutters justify-content-between">
                        <?php
                        $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                        $total_rating = round($total_rating, 1);
                        ?>
                        <div class="star-container" title="<?= $total_rating ?>">
                            <?php
                            $rating = $total_rating;
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="interested-container">
                            <?php
                            $interested_users_count = 0;
                            $is_interested = false;
                            foreach ($interested_users_properties as $interested_user_property) {
                                if ($interested_user_property['property_id'] == $property['id']) {
                                    $interested_users_count++;

                                    if ($interested_user_property['user_id'] == $user_id) {
                                        $is_interested = true;
                                    }
                                }
                            }

                            if ($is_interested) {
                            ?>
                                <i class="is-interested-image fas fa-heart" property_id="<?= $property['id'] ?>"></i>
                            <?php
                            } else {
                            ?>
                                <i class="is-interested-image far fa-heart" property_id="<?= $property['id'] ?>"></i>
                            <?php
                            }
                            ?>
                            <div class="interested-text">
                                <span class="interested-user-count"><?= $interested_users_count ?></span> interested
                            </div>
                        </div>
                    </div>
                    <div class="detail-container">
                        <div class="property-name"><?= $property['name'] ?></div>
                        <div class="property-address"><?= $property['address'] ?></div>
                        <div class="property-gender">
                            <?php
                            if ($property['gender'] == "male") {
                            ?>
                                <img src="img/male.png" />
                            <?php
                            } elseif ($property['gender'] == "female") {
                            ?>
                                <img src="img/female.png" />
                            <?php
                            } else {
                            ?>
                                <img src="img/unisex.png" />
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="rent-container col-6">
                            <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                            <div class="rent-unit">per month</div>
                        </div>
                        <div class="button-container col-6">
                            <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }

        if (count($properties) == 0) {
        ?>
            <div class="no-property-container">
                <p>No PG to list</p>
            </div>
        <?php
        }
        ?>
    </div>

    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="filter-heading">Filters</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Gender</h5>
                    <hr />
                    <div>
                        <a href="?city=<?= $city_name ?>&gender=all" class="btn btn-outline-dark btn-active">
                            No Filter
                        </a>
                        <a href="?city=<?= $city_name ?>&gender=unisex" class="btn btn-outline-dark">
                            <i class="fas fa-venus-mars"></i>Unisex
                        </a>
                        <a href="?city=<?= $city_name ?>&gender=male" class="btn btn-outline-dark">
                            <i class="fas fa-mars"></i>Male
                        </a>
                        <a href="?city=<?= $city_name ?>&gender=female" class="btn btn-outline-dark">
                            <i class="fas fa-venus"></i>Female
                        </a>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-success">Okay</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>

    <script type="text/javascript" src="js/property_list.js"></script>
</body>

</html>
