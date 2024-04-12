<?php
session_start();

// Connection to the database.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "animal_database";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) 
{
    die("Connection failed: " . $conn->connect_error);
}

// Function to get the filtered and sorted list of animals
function getAnimalList($conn, $categoryFilter = null, $lifeExpectancyFilter = null, $sortBy = null) 
{
    $sql = "SELECT * FROM animals WHERE 1=1"; // Start with a true condition
    
    // Apply filters 
    if ($categoryFilter !== null || $lifeExpectancyFilter !== null) //checking condition
    {
        $sql .= " AND (";
        if ($categoryFilter !== null) 
        {
            $sql .= "category = '$categoryFilter'";
        }
        if ($lifeExpectancyFilter !== null) 
        {
            if ($categoryFilter !== null) 
            {
                $sql .= " OR ";
            }
            $sql .= "life_expectancy = '$lifeExpectancyFilter'";
        }
        $sql .= ")";
    }
    
    // Apply sorting 
    if ($sortBy !== null) 
    {
        switch ($sortBy) 
        {
            case 'date':
                $sql .= " ORDER BY submission_date DESC";
                break;
            case 'alphabetical':
                $sql .= " ORDER BY name";
                break;
            default:
                // Default sorting, do nothing
                break;
        }
    }
    $result = mysqli_query($conn, $sql);
    if (!$result) 
    {
        echo "Error: " . mysqli_error($conn);
    }
    return $result;
}

/// Get filter parameters 
$categoryFilter = isset($_GET['categoryFilter']) ? $_GET['categoryFilter'] : null;
$lifeExpectancyFilter = isset($_GET['lifeExpectancyFilter']) ? $_GET['lifeExpectancyFilter'] : null;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : null;

// Query to fetch filtered and sorted animals
$result = getAnimalList($conn, $categoryFilter, $lifeExpectancyFilter, $sortBy);

// Counting visitors
$visitor_count = 0;
if (isset($_COOKIE['visitor_count'])) 
{
    $visitor_count = $_COOKIE['visitor_count'];
}
$visitor_count++;
setcookie('visitor_count', $visitor_count, time() + (86400 * 30), "/");

// Handling form submission redirection
if (isset($_GET['submitted']) && $_GET['submitted'] === 'true') 
{
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Listing</title>
    <style>
        /* CSS styles for table and visitor count */
    </style>
</head>
<body>
    <h1>Animal Listing</h1>
    <p>Visitor Count: <?php echo $visitor_count; ?></p>
    
    <!-- Form for filtering -->
    <form action="" method="get">
    <label>Filter by Category:</label>
    <select name="categoryFilter">
        <option value="">All</option>
        <option value="herbivores">Herbivores</option>
        <option value="omnivores">Omnivores</option>
        <option value="carnivores">Carnivores</option>
    </select>
    <label>Filter by Life Expectancy:</label>
    <select name="lifeExpectancyFilter">
        <option value="">All</option>
        <option value="0-1 year">0-1 year</option>
        <option value="1-5 years">1-5 years</option>
        <option value="5-10 years">5-10 years</option>
        <option value="10+ years">10+ years</option>
    </select>
    <input type="submit" value="Apply Filters">
</form>
<!-- Display current filter parameters -->
<p>Current Filter: Category - <?php echo $categoryFilter ?? 'All'; ?>, Life Expectancy - <?php echo $lifeExpectancyFilter ?? 'All'; ?></p>
    
    <table border="1">
        <tr>
            <th>Image</th>
            <th><a href="?sortBy=name">Name</a></th>
            <th><a href="?sortBy=category">Category</a></th>
            <th>Description</th>
            <th><a href="?sortBy=life_expectancy">Life Expectancy</a></th>
            <th>Options</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" width="100"></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['life_expectancy']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
