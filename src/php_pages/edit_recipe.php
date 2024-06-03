<?php 

require_once 'connection.php';

$recipe_id = $_GET["recipe_id"] ?? null;

// Initialize variables
$recipe = [
    'id' => '',
    'titel' => '',
    'writer_id' => '',
    'img_url' => '',
    'inhoud' => ''
];
$current_tags = [];

if ($recipe_id) {
    // Fetch the current recipe data
    try {
        $sql = 'SELECT * FROM recipes WHERE id = :recipe_id';
        $stmt = $db_conn->prepare($sql);
        $stmt->execute(['recipe_id' => $recipe_id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recipe) {
            // Fetch the current tags
            $sql = 'SELECT t.titel FROM tags t INNER JOIN recipe_tags rt ON t.id = rt.tag_id WHERE rt.recipe_id = :recipe_id';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['recipe_id' => $recipe_id]);
            $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $current_tags = $tags;
        }
    } catch (PDOException $e) {
        echo "Fetching recipe failed: " . $e->getMessage();
    }
}

if (isset($_POST["submit"])) {
    $recipe_id = $_POST["recipe_id"];
    $titel = $_POST["titel"];
    $writer_id = $_POST["writers"];
    $tags = array_map('strtolower', array_map('trim', explode(',', $_POST["tags"])));
    $inhoud = $_POST["inhoud"];
    $foto = $_POST["img_url"];
    $dateWritten = date("Y/m/d h:i:s");

    if (!empty($recipe_id)) {
        // Update the existing recipe
        try {
            $sql = 'UPDATE recipes SET titel = :titel, writer_id = :writer_id, datum = :datum, img_url = :img_url, inhoud = :inhoud WHERE id = :recipe_id';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute([
                'titel' => $titel,
                'writer_id' => $writer_id,
                'datum' => $dateWritten,
                'img_url' => $foto,
                'inhoud' => $inhoud,
                'recipe_id' => $recipe_id
            ]);
        } catch (PDOException $e) {
            echo "Updating recipe failed: " . $e->getMessage();
        }

        // Clear existing tags for the recipe
        try {
            $sql = 'DELETE FROM recipe_tags WHERE recipe_id = :recipe_id';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['recipe_id' => $recipe_id]);
        } catch (PDOException $e) {
            echo "Clearing existing tags failed: " . $e->getMessage();
        }
    } else {
        // Handle the case where recipe_id is not set
        echo "No recipe ID provided for update.";
        return;
    }

    // Add the tags.
    foreach ($tags as $tag) {
        try {
            // Try to add the tags into the list of tags.
            $sql = 'INSERT INTO tags (titel) VALUES (:titel)';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['titel' => $tag]);
            $tag_id = $db_conn->lastInsertId();
        } catch (PDOException $e) {
            // If the tag already exists, add only the ID.
            $sql = 'SELECT id FROM tags WHERE titel = :titel';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['titel' => $tag]);
            $tag_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        }

        $sql = 'INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (:recipe_id, :tag_id)';
        $stmt = $db_conn->prepare($sql);
        $stmt->execute(['recipe_id' => $recipe_id, 'tag_id' => $tag_id]);
    }

    // Return to the main page
    header("Location: ../../index.php");
    exit();
}

?>
<html>
    <head>
        <title>New recipe</title>
        <link rel="apple-touch-icon" sizes="180x180" href="../../apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="../../favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="../../favicon-16x16.png">
        <link rel="manifest" href="../../site.webmanifest">
        <link rel="stylesheet" href="./src/styling/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </head>
    <body class="bg-black text-light">

        <div class="container">            
        <div class="container mb-5  mt-4 border-bottom" >
            <h1 class=" mt-4"><a href="../../index.php" class=" text-decoration-none text-light">The best list of recipes</a></h1>
            <h3><a class="btn text-decoration-none text-light border border-light p-2" href="./src/php_pages/new_recipe.php">New recipe?</a></h3>
        </div>

            <!-- Here is the menu with all the data required -->
            <form class="container" action="edit_recipe.php" method="post">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id']; ?>">

                <div class="col">
                    <div class="d-flex flex-column mt-3 w-25">
                        <label for="titel">Title:</label>
                        <input type="text" id="titel" name="titel" value="<?= $recipe['titel']; ?>">
                    </div>
                            
                    <div class="d-flex flex-column mt-3 w-25">
                        <label for="writers">Writers:</label>
                        <select name="writers">
                            <option value="1">Mounir Toub</option>
                            <option value="2">Miljuschka</option>
                            <option value="3">Wim Ballieu</option>
                        </select>
                    </div>
                            
                    <div class="d-flex flex-column mt-3 w-50">
                        <label for="tags">Tags, (split by each comma)</label>
                        <input type="text" id="tags" name="tags" value="<?= implode(', ', $current_tags); ?>">
                    </div>

                    <div class="d-flex flex-column mt-3 w-50">
                        <label for="img_url">URL image:</label>
                        <input type="text" id="img_url" name="img_url" value="<?= $recipe['img_url']; ?>">
                    </div>

                    <div class="d-flex flex-column my-3">
                        <label for="inhoud">Content:</label>
                        <textarea id="inhoud" name="inhoud" rows="10" cols="100"><?= $recipe['inhoud']; ?></textarea>
                    </div>
                            
                    <input class="btn btn-success p-2 border-white mb-2" type="submit" name="submit" value="Publish">
                </div>
            </form>
            <div class="container">
                    <a class="btn btn-info p-2 border border-white text-decoration-none text-white" href="../../index.php">Back to home page</a>
                </div>
        </div>
    </body>
</html>
