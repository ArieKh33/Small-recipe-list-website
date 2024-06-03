DROP DATABASE IF EXISTS many_recipes;

CREATE DATABASE many_recipes;

USE many_recipes;


CREATE TABLE writers (
    id int NOT NULL AUTO_INCREMENT,
    writerName varchar(64),
    PRIMARY KEY(id)
);

CREATE TABLE recipes (
    id int NOT NULL UNIQUE  AUTO_INCREMENT,
    titel varchar(64),
    datum datetime,
    likes int,
    writer_id int NOT NULL,
    img_url varchar(256),
    inhoud text,
    PRIMARY KEY(id),
    FOREIGN KEY (writer_id) REFERENCES writers(id)
);

CREATE TABLE tags (
    id int NOT NULL UNIQUE AUTO_INCREMENT,
    titel varchar(32),
    PRIMARY KEY(id)
);

CREATE TABLE recipe_tags (
    recipe_id int,
    tag_id int,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);


INSERT INTO writers (writerName)
VALUES
    ("Wim Ballieu"),
    ("Mounir Toub"),
    ("Miljuschka");
