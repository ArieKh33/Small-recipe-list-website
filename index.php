<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once './src/php_pages/connection.php';

    // You join the likes from the posts to the artist.
    function fetchPosts($db_conn, &$sqlDataPosts) {
        $sqlDataPosts = [];
        $sqlPosts = "SELECT posts.*, auteurs.naam FROM posts
                     INNER JOIN auteurs ON posts.auteur_id = auteurs.id
                     ORDER BY posts.likes DESC";
        $sqlDataPosts = $db_conn->query($sqlPosts)->fetchall();    
    }

        $tag = $_GET['tag']; 

    // You join the likes from the posts to the artist.
    function fetchPosts2($db_conn, $tag, &$sqlDataPosts) {
        $sqlDataPosts = [];
        $sqlPosts = "SELECT posts.*, auteurs.naam 
                     FROM posts
                     INNER JOIN auteurs ON posts.auteur_id = auteurs.id
                     INNER JOIN posts_tags ON posts.id = posts_tags.post_id
                     INNER JOIN tags ON posts_tags.tag_id = tags.id
                     WHERE tags.titel = :tag
                     ORDER BY posts.likes DESC";
        
        $stmt = $db_conn->prepare($sqlPosts);
        $stmt->execute(['tag' => $tag]);
        $sqlDataPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Whenever a cheff has posts total to 10 likes or more they get added to the "popular chefs" list.
    function addPopularCheff($db_conn, &$sqlCheffPosts) {
        $sqlCheffPosts = [];
        $sqlChefs =  "SELECT * FROM posts INNER JOIN auteurs ON posts.auteur_id = auteurs.id GROUP BY auteur_id HAVING SUM(likes) > 10";
        $sqlCheffPosts = $db_conn->query($sqlChefs)->fetchall();
    }


    // Here you join the tags that are related to the posts.
    function getTagData($db_conn, &$sqlDataPosts) {
        foreach ($sqlDataPosts as $index => $post) {
            $sql = "SELECT tags.* FROM tags 
                    INNER JOIN posts_tags pt ON pt.tag_id = tags.id 
                    WHERE pt.post_id = :id";
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(['id' => $post['id']]);
            $sqlDataPosts[$index]['tags'] = $stmt->fetchAll();
        }
    }



    // Here you call all the functions.
    fetchPosts($db_conn,$sqlDataPosts);
    addPopularCheff($db_conn, $sqlCheffPosts);
    getTagData($db_conn, $sqlDataPosts);


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

            <div class="header container" >
                <h1 class=" mt-4"><a href="index.php" class=" text-decoration-none text-light">The best list of recipes</a></h1>
                <h3><a class="text-decoration-none text-light" href="./src/php_pages/new_recipe.php">New post</a></h3>
            </div>
            
            <!-- the 3 boxes containing filters by tag and by writer -->
            <div class="container mb-5">
                <div class="row">
                    <div class=" col-12 col-lg-4 border border-light-subtle p-2">
                        <h3>Populaire chefs</h3>
                        <ul>
                            <?php foreach ($sqlCheffPosts as $cheff) { ?>
                                <li><?= $cheff[8]; ?></li>
                            <?php } ?>
                        </ul>
                    </div>

                    <div class=" col-12 col-lg-4  border border-light-subtle p-2">
                        <h3>Populaire chefs</h3>
                        <ul>
                            <?php foreach ($sqlCheffPosts as $cheff) { ?>
                                <li><?= $cheff[8]; ?></li>
                            <?php } ?>
                        </ul>
                    </div>

                    <div class=" col-12 col-lg-4  border border-light-subtle p-2">
                        <h3>Populaire chefs</h3>
                        <ul>
                            <?php foreach ($sqlCheffPosts as $cheff) { ?>
                                <li><?= $cheff[8]; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    

                </div>

            </div>


        <script>
            $(document).ready(function() {
                $(".addlike").click(function(event) {
                    event.preventDefault();
                    const button = $(this);
                    const post_id = button.val();

                    $.ajax({
                        type: "POST",
                        url: "src/php_pages/load_Likes.php",
                        data: { post_id: post_id },
                        success: function(response) {
                            const data = JSON.parse(response);
                            button.text(data.likes + ' likes');
                        }
                    });
                });
            });
        </script>
<script>
    $(document).ready(function() {
        $(".deleteRecipe").click(function(event) {
            event.preventDefault();
            var button = $(this);
            var post_id = button.val();
            var postDiv = button.closest('.post');

            $.ajax({
                type: "POST",
                url: "src/php_pages/delete_recipe.php",
                data: { post_id: post_id },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        postDiv.remove();
                    } else {
                        alert('Failed to delete the post.');
                    }
                }
            });
        });
    });
</script>


            <!-- This container contains all the posts -->
            <div id="recipes" class="container">
                <div class="row">
                    <?php foreach ($sqlDataPosts as $post) { ?>
                        <div class="post card bg-black bg-gradient border border-light-subtle col-12 col-md-6 col-lg-4 mb-3 rounded-0">
                            <img class="border border-light-subtle" src="<?= $post['img_url']; ?>" alt="<?= $post['titel']; ?>">

                            <div class="card-body m-1">
                                <div class="row ">
                                    <h5 class="post_title text-center col"><?= $post['titel']; ?></h2>

                                    <form class="col" action="#" method="post">
                                        <input type="hidden" name="deleteRecipe" value="<?= $post['id']; ?>">
                                        <button class="text-bg-danger bg-gradient border border-light-subtle deleteRecipe" type="button" value="<?= $post['id']; ?>">Delete</button>
                                    </form>

                                    <form class="col" action="index.php" method="post">
                                            <button class="text-bg-primary border-light-subtle addlike" type="submit" value="<?= $post['id']; ?>" name="like">
                                                <?= $post['likes']; ?> likes
                                            </button>
                                        </form>
                                </div>
                                
                                <span class="details text-light">Geschreven op: <?= $post['datum']; ?> door <b> <?= $post['naam']; ?></b></span>

                                <div class="container">
                                    <div class="row mt-2 mb-4 ml-0">
                                        <form class="col" action="index.php" method="get">
                                            <?php foreach ($post['tags'] as $tag) { ?>
                                                <button type="submit" value="<?=$tag['titel']; ?>" name="tag">
                                                    <?= $tag['titel']; ?> 
                                                </button>
                                            <?php } ?>
                                        </form>


                                    </div>
                                </div>
                                

                                <p class="inhoud border border-light-subtle  text-light p-2"><?= $post['inhoud']; ?></p>

                            </div>
                            
                        </div>
                        
                    <?php } ?>
                </div>

            </div>      
    </body>
</html>
