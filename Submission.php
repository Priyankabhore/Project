<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();

    // Function to generate random math captcha
    function generateCaptcha() 
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_sum'] = $num1 + $num2;
        return "$num1 + $num2 = ?";
    }

    $captcha_error = ''; // Variable to store captcha error message

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        // Validate form data
        $name = $_POST['name'];
        $category = $_POST['category'];
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $description = $_POST['description'];
        $life_expectancy = $_POST['life_expectancy'];
        $captcha_input = $_POST['captcha'];
        
        // Perform server-side validation
        if ($captcha_input != $_SESSION['captcha_sum']) 
        {
            $captcha_error = "Captcha is incorrect"; //if user enter wrong captcha it will show the Captcha is incorrect message.
        }
    
        // If no errors, proceed to insert data into the database
        if (empty($captcha_error)) 
        {
            // Connection to the database 
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

            // Prepare SQL statement to insert data
            $image_path = "./image/$image"; 
            move_uploaded_file($image_tmp, $image_path); //image path for storing uploaded image.

            $insert_query = "INSERT INTO animals (name, category, Image, description, life_expectancy)
                    VALUES ('$name', '$category','$image_path','$description','$life_expectancy')"; //store inserted data into database.
        
            if ($conn->query($insert_query) === TRUE) 
            {
                echo "<script>alert('Data Enter successful')</script>";
                header("Location: index.php");
                exit;
            } else {
                echo "Error: " . $insert_query . "<br>" . $conn->error;
            }
            
            $conn->close();
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>Animal Submission Form</title>
    
</head>
<body>
    <h2>Animal Submission Form</h2>
    <?php if (!empty($captcha_error)) : ?>
    <script>alert('<?php echo $captcha_error; ?>');</script>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Name of the animal:</label><br>
        <input type="text" name="name" ><br>

        <label>Category:</label><br>
<input type="radio" id="herbivores" name="category" value="herbivores">
<label for="herbivores">Herbivores</label><br>
<input type="radio" id="omnivores" name="category" value="omnivores">
<label for="omnivores">Omnivores</label><br>
<input type="radio" id="carnivores" name="category" value="carnivores">
<label for="carnivores">Carnivores</label><br>


        <label>Image:</label><br>
        <input type="file" name="image" placeholder="Upload Image" accept=".pdf, .jpg, .png" required><br>

        <label>Description:</label><br>
        <textarea name="description"></textarea><br>

        <label>Life Expectancy:</label><br>
        <select name="life_expectancy">
            <option value="0-1 year">0-1 year</option>
            <option value="1-5 years">1-5 years</option>
            <option value="5-10 years">5-10 years</option>
            <option value="10+ years">10+ years</option>
        </select><br>

        <label>Captcha:</label><br>
        <?php echo generateCaptcha(); ?><br>
        <input type="text" name="captcha"><br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
