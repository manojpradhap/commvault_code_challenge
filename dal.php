<?php

// Function to establish database connection
function db_connect() {
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = 'root123';
    $db_name = 'demo';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to count all posts in the database
function count_all_posts() {
    $conn = db_connect();

    $sql = "SELECT COUNT(*) as total_count FROM posts";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return intval($row['total_count']);
    } else {
        return 0;
    }
}

// Function to get a post by its ID
function get_post_by_id($post_id) {
    $conn = db_connect();
    $sql = "SELECT * FROM posts WHERE post_id = $post_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows == 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Function to get a paginated list of posts
function get_posts($page, $postsPerPage) {
    $conn = db_connect();

    // Calculate the offset based on the current page and posts per page
    $offset = ($page - 1) * $postsPerPage;

    // Fetch only the desired number of records per page
    $sql = "SELECT * FROM posts LIMIT $offset, $postsPerPage";
    $result = $conn->query($sql);

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    return $posts;
}

// Function to search for posts by title
function search_posts($search, $page, $postsPerPage) {
    $conn = db_connect();

    $offset = ($page - 1) * $postsPerPage;

    $search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM posts WHERE title LIKE '%$search%' LIMIT $offset, $postsPerPage";
    $result = $conn->query($sql);

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    return $posts;
}

// Function to count the total number of searched posts
function count_searched_posts($search) {
    $conn = db_connect();

    $search = $conn->real_escape_string($search);
    $sql = "SELECT COUNT(*) as total_count FROM posts WHERE title LIKE '%$search%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_count'];
}

// Function to create a new post in the database
function create_post_in_db($user_id, $title, $content) {
    $conn = db_connect();
    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);

    $sql = "INSERT INTO posts (user_id, title, content, created_at, updated_at)
            VALUES ('$user_id', '$title', '$content', NOW(), NOW())";

    if ($conn->query($sql)) {
        return $conn->insert_id;
    } else {
        return false;
    }
}

// Function to update an existing post in the database
function update_post_in_db($post_id, $title, $content) {
    $conn = db_connect();
    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);

    $sql = "UPDATE posts SET title = '$title', content = '$content', updated_at = NOW()
            WHERE post_id = '$post_id'";

    return $conn->query($sql);
}

// Function to delete a post from the database
function delete_post_from_db($post_id) {
    $conn = db_connect();
    $sql = "DELETE FROM posts WHERE post_id = '$post_id'";
    return $conn->query($sql);
}
