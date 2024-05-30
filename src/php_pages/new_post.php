<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>

    <!-- NOTE: This page mainly needs a styling update since it doesn't align with the other pages yet. -->
        <div class="container">

            <div id="header">
                <h1>Nieuwe post</h1>
                <a href="index.php"><button>Alle posts</button></a>
            </div>
            <?php
            echo PHP_VERSION;
            include 'connection.php';
            $dateWritten = date("Y/m/d h:i:s");
            
            if (isset($_POST["submit"])) {
                $titel = $_POST["titel"];
                $auteur_id = $_POST["auteurs"];
                // Strip the tags and make them lower case
                $tags = array_map('strtolower', array_map('trim', explode(',', $_POST["tags"])));
                $inhoud = $_POST["inhoud"];
                $foto = $_POST["img_url"];

                // Add the post into the table
                try {
                    $sql = 'INSERT INTO posts(titel, auteur_id, datum, img_url, inhoud, likes) VALUES (:titel, :auteur_id, :datum, :img_url,  :inhoud, 0)';
                    $stmt = $db_conn->prepare($sql);
                    $stmt->execute(['titel' => $titel, 'auteur_id' => $auteur_id, 'datum' => $dateWritten,'img_url' => $foto ,'inhoud' => $inhoud]);
                    $post_id = $db_conn->lastInsertId();
                    echo 'Post gepubliceerd';
                } catch (PDOException $e) {
                    echo "Post publiceren mislukt: " . $e->getMessage();
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

                    $sql = 'INSERT INTO posts_tags(post_id, tag_id) VALUES (:post_id, :tag_id)';
                    $stmt = $db_conn->prepare($sql);
                    $stmt->execute(['post_id' => $post_id, 'tag_id' => $tag_id[0]]);
                }
            } else {
                ?>
                    <!-- Here is the menu with all the data required -->
                    <form action="new_post.php" method="post">
                    Titel:<br/> <input type="text" name="titel"><br/><br/>
                    Auteurs:<br/>
                    <select name="auteurs">
                    <option value="1">Mounir Toub</option>
                    <option value="2">Miljuschka</option>
                    <option value="3">Wim Ballieu</option>
                    </select><br/><br/>

                    Tags (door komma gescheiden):<br/> <input type="text" name="tags"><br/><br/>
                    <label for="img_url">URL afbeelding:</label><br/>
                    <input type="text" name="img_url" id="img_url"><br/><br/>
                    Inhoud:<br/> <textarea name="inhoud" rows="10" cols="100"></textarea>
                    <br/><br/>
                    <input type="submit" name="submit" value="Publiceer">
                    </form>
                <?php
            }
            ?>

        </div>
    </body>
</html>