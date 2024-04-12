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

    //form submission redirection
    if (isset($_GET['submitted']) && $_GET['submitted'] === 'true') 
    {
        header("Location: index.php");
        exit();
    }

    //form edit and delet actions
    if (isset($_GET['action'])) 
    {
        $action = $_GET['action'];
        $animal_id = $_GET['id'];
        
    // Edit action
    if ($action === 'edit') 
    {
        // Fetch the animal record from the database
        $sql = "SELECT * FROM animals WHERE id = $animal_id";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) 
        {
            $animal = mysqli_fetch_assoc($result);
?>
    <!-- Display edit form -->
    <h2>Edit Animal</h2>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $animal_id; ?>">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($animal['name']); ?>"><br>
        <label>Category:</label><br>
        <input type="radio" name="category" value="herbivores" <?php if ($animal['category'] === 'herbivores') echo 'checked'; ?>> Herbivores<br>
        <input type="radio" name="category" value="omnivores" <?php if ($animal['category'] === 'omnivores') echo 'checked'; ?>> Omnivores<br>
        <input type="radio" name="category" value="carnivores" <?php if ($animal['category'] === 'carnivores') echo 'checked'; ?>> Carnivores<br>
        <label>Description:</label>
        <textarea name="description"><?php echo htmlspecialchars($animal['description']); ?></textarea><br>
        <label>Life Expectancy:</label><br>
        <input type="radio" name="life_expectancy" value="0-1 year" <?php if ($animal['life_expectancy'] === '0-1 year') echo 'checked'; ?>> 0-1 year<br>
        <input type="radio" name="life_expectancy" value="1-5 years" <?php if ($animal['life_expectancy'] === '1-5 years') echo 'checked'; ?>> 1-5 years<br>
        <input type="radio" name="life_expectancy" value="5-10 years" <?php if ($animal['life_expectancy'] === '5-10 years') echo 'checked'; ?>> 5-10 years<br>
        <input type="radio" name="life_expectancy" value="10+ years" <?php if ($animal['life_expectancy'] === '10+ years') echo 'checked'; ?>> 10+ years<br>
        <input type="submit" name="edit_animal" value="Save">
    </form>
    <?php
    }
}

// Handle form submission for editing
if (isset($_POST['edit_animal'])) 
{
    $animal_id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $life_expectancy = $_POST['life_expectancy'];

    // Update the animal record in the database
    $sql = "UPDATE animals SET name = ?, category = ?, description = ?, life_expectancy = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $category, $description, $life_expectancy, $animal_id);
    if (mysqli_stmt_execute($stmt)) 
    {
    
        echo '<script>alert("Animal with ID ' . $animal_id . ' updated successfully.");</script>';
        // Redirect to the updated page
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating animal: " . mysqli_error($conn);
    }
}

    
    // Delete action
    elseif ($action === 'delete') {
        // Delete the animal record from the database
        $sql = "DELETE FROM animals WHERE id = $animal_id";
        if (mysqli_query($conn, $sql)) {
            echo "Animal with ID $animal_id deleted successfully.";
            
        } else {
            echo "Error deleting animal: " . mysqli_error($conn);
        }
    }
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
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-top: 20px;
}

p {
    text-align: center;
}

form {
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

a {
    text-decoration: none;
    color: blue;
}

a:hover {
    color: red;
}
.edit-link:hover {
    color: #006400; /* Darker green color when hovered over */
}
.delete-link:hover {
    color: red; 
}

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
    <a href="?action=edit&id=<?php echo $row['id']; ?>" class="edit-link">Edit</a><br><br>
    <a href="?action=delete&id=<?php echo $row['id']; ?>" class="delete-link">Delete</a>
</td>

            </tr>
        <?php } ?>
    </table>
</body>
</html>
