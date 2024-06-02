<?php
    require_once './src/php_pages/connection.php';

    // You join the likes from the recipes to the artist.
    function fetchRecipes($db_conn, &$sqlDataRecipes) {
        $sqlDataRecipes = [];
        $sqlRecipes = "SELECT recipes.*, writers.writerName FROM recipes
                     INNER JOIN writers ON recipes.writer_id = writers.id
                     ORDER BY recipes.likes DESC";
        $sqlDataRecipes = $db_conn->query($sqlRecipes)->fetchall();
        getTagData($db_conn, $sqlDataRecipes);
    
    }


    // You join the likes from the recipes to the artist.
    function fetchRecipesByTag($db_conn,&$sqlDataRecipes) {
    if (isset($_GET['tag'])) {
        $tag = $_GET['tag']; 
        $sqlDataRecipes = [];
        

        try {
            $sqlRecipes = "SELECT recipes.*, writers.writerName 
                FROM recipes
                INNER JOIN writers ON recipes.writer_id = writers.id
                INNER JOIN recipe_tags ON recipes.id = recipe_tags.recipe_id
                INNER JOIN tags ON recipe_tags.tag_id = tags.id
                WHERE tags.titel = :tag
                ORDER BY recipes.likes DESC";
            $stmt = $db_conn->prepare($sqlRecipes);
            $stmt->execute(['tag' => $tag]);
            $sqlDataRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            getTagData($db_conn, $sqlDataRecipes);
        } catch (PDOException $e) {
            echo "Updating recipe failed: " . $e->getMessage();

        }
    }
    }


    // Whenever a cheff has recipes total to 10 likes or more they get added to the "popular chefs" list.
    function addPopularCheff($db_conn, &$sqlCheffRecipes) {
        $sqlCheffRecipes = [];
        $sqlChefs =  "SELECT * FROM recipes INNER JOIN writers ON recipes.writer_id = writers.id GROUP BY writer_id HAVING SUM(likes) > 10";
        $sqlCheffRecipes = $db_conn->query($sqlChefs)->fetchall();
    }


    // Here you join the tags that are related to the recipes.
    function getTagData($db_conn, &$sqlDataRecipes) {
        foreach ($sqlDataRecipes as $index => $recipe) {
            $sql = "SELECT tags.* FROM tags 
                    INNER JOIN recipe_tags pt ON pt.tag_id = tags.id 
                    WHERE pt.recipe_id = :id";
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['id' => $recipe['id']]);
            $sqlDataRecipes[$index]['tags'] = $stmt->fetchAll();
        }
    }



    // Here you call all the functions.
    fetchRecipes($db_conn,$sqlDataRecipes);
    addPopularCheff($db_conn, $sqlCheffRecipes);
    fetchRecipesByTag($db_conn, $sqlDataRecipes);

?>
<!DOCTYPE html>
<html lang="nl">
    <head>
        <title>All of the recipes</title>
        <!-- NOTE: this favicon is a placeholder, it will be changed eventually -->
        <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
        <link rel="manifest" href="site.webmanifest">
        <link rel="stylesheet" href="./src/styling/style.css">
        <script src="src/javascript/loading.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>



    </head>
    <body class="bg-black text-light">

            <div class="container mb-5  mt-4" >
                <h1 class="mb-3"><a href="index.php" class=" text-decoration-none text-light">The best list of recipes</a></h1>
                <h2 class="mb-4">Selected tag: <?php if (isset($_GET['tag'])) {echo $_GET['tag'];} else {echo "None";} ?></h2>
                <h3><a class="text-decoration-none text-light border border-light p-2" href="./src/php_pages/new_recipe.php">New recipe?</a></h3>
            </div>
            
            <!-- the 3 boxes containing filters by tag and by writer -->
            <div class="container mb-4">
                <div class="row">
                    <div class="col col-12 col-lg-4 border border-light-subtle p-2">
                        <h3>Popular writers</h3>
                        <ul>
                            <?php foreach ($sqlCheffRecipes as $cheff) { ?>
                                <li><?= $cheff[8]; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

            </div>


        <!-- Run this script every time you add a like to the post -->
        <script>
            $(document).ready(function() {
                $(".addlike").click(function(event) {
                    event.preventDefault();
                    const button = $(this);
                    const recipe_id = button.val();

                    $.ajax({
                        type: "POST",
                        url: "src/php_pages/load_Likes.php",
                        data: { recipe_id: recipe_id },
                        success: function(response) {
                            const data = JSON.parse(response);
                            button.text(data.likes + ' likes');
                        }
                    });
                });
            });
        </script>


        <!-- Run this script every time you delete a post without having to refresh the page -->
        <script>
            $(document).ready(function() {
                $(".deleteRecipe").click(function(event) {
                    event.preventDefault();
                    var button = $(this);
                    var recipe_id = button.val();
                    var recipeDiv = button.closest('.recipe');

                    $.ajax({
                        type: "POST",
                        url: "src/php_pages/delete_recipe.php",
                        data: { recipe_id: recipe_id },
                        success: function(response) {
                            var data = JSON.parse(response);
                            if (data.success) {
                                recipeDiv.remove();
                            } else {
                                alert('Failed to delete the recipe.');
                            }
                        }
                    });
                });
            });
        </script>


            <!-- This container contains all the recipes -->
            <div id="recipes" class="container">
                <div class="row">
                    <?php foreach ($sqlDataRecipes as $recipe) { ?>
                        <div class="recipe card bg-black bg-gradient border border-light-subtle col-12 col-md-6 col-lg-4 mb-3 rounded-0">
                            <img class="border border-light-subtle" src="<?= $recipe['img_url']; ?>" alt="<?= $recipe['titel']; ?>">

                                <div class="card-body m-1">
                                    <div class="row ">
                                        <h5 class="recipe_title text-center col"><?= $recipe['titel']; ?></h2>


                                        <!-- NOTE: THE EDIT PAGE DOES NOT WORK FULLY YET, IT DISPLAYS THE CURRENT DATA BUT DOES NOT UPDATE IT, IT ONLY ADDS THE DATA TO A NEW POST -->
                                        <!-- <form class="col" action="./src/php_pages/edit_recipe.php" method="get">
                                            <input type="hidden" name="recipe_id" value="<?= $recipe['id']; ?>">
                                            <button class="text-bg-success bg-gradient border border-light-subtle" type="submit">Edit</button>
                                        </form> -->

                                        <form class="col" action="index.php" method="post">
                                            <input type="hidden" name="deleteRecipe" value="<?= $recipe['id']; ?>">
                                            <button class="text-bg-danger bg-gradient border border-light-subtle deleteRecipe" type="button" value="<?= $recipe['id']; ?>">Delete</button>
                                        </form>

                                        <form class="col" action="index.php" method="post">
                                                <button class="text-bg-primary border-light-subtle addlike" type="submit" value="<?= $recipe['id']; ?>" name="like">
                                                    <?= $recipe['likes']; ?> likes
                                                </button>
                                            </form>
                                    </div>
                                    
                                    <span class="details text-light">Written on: <?= $recipe['datum']; ?> by <b> <?= $recipe['writerName']; ?></b></span>

                                    <!-- Here you load in every tag with a foreach loop -->
                                    <div class="container">
                                        <div class="row mt-2 mb-4 ml-0">
                                            <form class="col" action="index.php" method="get">
                                                <label class="text-white">Tags: </label>
                                                <?php foreach ($recipe['tags'] as $tag) { ?>
                                                    <button class="bg-black text-white border border-light light" type="submit" value="<?=$tag['titel']; ?>" name="tag">
                                                        <?= $tag['titel']; ?> 
                                                    </button>
                                                <?php } ?>
                                            </form>


                                        </div>
                                    </div>
                                    
                                    <p class="inhoud border border-light-subtle  text-light p-2"><?= $recipe['inhoud']; ?></p>

                                </div>
                            </div>
                        <?php } ?>
                </div>
            </div>      
    </body>
</html>
