<?php 

require_once 'connection.php';

// Get the current date and time for post
$dateWritten = date("Y/m/d h:i:s");

if (isset($_POST["submit"])) {
    $titel = $_POST["titel"];
    $writer_id = $_POST["writers"];
    // Strip the tags and make them lower case
    $tags = array_map('strtolower', array_map('trim', explode(',', $_POST["tags"])));
    $inhoud = $_POST["inhoud"];
    $foto = $_POST["img_url"];

    // Add the recipe into the table
    try {
        $sql = 'INSERT INTO recipes(titel, writer_id, datum, img_url, inhoud, likes) VALUES (:titel, :writer_id, :datum, :img_url,  :inhoud, 0)';
        $stmt = $db_conn->prepare($sql);
        $stmt->execute(['titel' => $titel, 'writer_id' => $writer_id, 'datum' => $dateWritten,'img_url' => $foto ,'inhoud' => $inhoud]);
        $recipe_id = $db_conn->lastInsertId();
    } catch (PDOException $e) {
        echo "Adding recipe failed" . $e->getMessage();
    }

    // Add the tags
    foreach ($tags as $tag) {
        try {
            // Try to add the tags into the list of tags.
            $sql = 'INSERT INTO tags(titel) VALUES (:titel)';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['titel' => $tag]);
            $tag_id = [$db_conn->lastInsertId()];
        } catch (PDOException $e) {
            // If the tag already exists, add only the ID.
            $sql = 'SELECT id FROM tags WHERE titel=:titel';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['titel' => $tag]);
            $tag_id = $stmt->fetch();
        }

        $sql = 'INSERT INTO recipe_tags(recipe_id, tag_id) VALUES (:recipe_id, :tag_id)';
        $stmt = $db_conn->prepare($sql);
        $stmt->execute(['recipe_id' => $recipe_id, 'tag_id' => $tag_id[0]]);
    }


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

    <!-- NOTE: This page mainly needs a styling update since it doesn't align with the other pages yet. -->
        <div class="container">
            
        <div class="header container" >
                <h1 class=" mt-4"><a href="../../index.php" class=" text-decoration-none text-light">The best list of recipes</a></h1>
                <h2>New Recipe</h2>
            </div>

                    <!-- Here is the menu with all the data required -->
                <form class="container" action="new_recipe.php" method="post">

                <div class="col">

                    <div class="d-flex flex-column mt-3 w-25">
                        <label for="titel">Title:</label>
                        <input type="text" name="titel">
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
                        <input type="text" name="tags">  
                    </div>

                    <div class="d-flex flex-column mt-3 w-50">
                        <label for="img_url">URL image:</label>
                        <input type="text" name="img_url" id="img_url">
                    </div>

                    <div class="d-flex flex-column my-3">
                        <label for="inhoud">Content:</label>
                        <textarea name="inhoud" rows="10" cols="100"></textarea>
                    </div>
                        

                    <input class="text-bg-success p-2 border-white" type="submit" name="submit" value="Publish">
                </div>
                </form>
        </div>
    </body>
</html>
