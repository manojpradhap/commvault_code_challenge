<?php

// Include the data access layer
require_once 'dal.php';

// Function to authenticate a user
function authenticate_user($username, $password) {
    $conn = db_connect();
    $username = mysqli_real_escape_string($conn, $username);

    // Hash the provided password for comparison
    $hashed_password = sha1($password);

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$hashed_password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        // User is authenticated
        return true;
    } else {
        // User is not authenticated
        return false;
    }
}

// Function to check if the authenticated user is the owner of a post.
function is_post_owner($user_id, $post_id) {
    $post = get_post_by_id($post_id);
    if ($post && $post['user_id'] == $user_id) {
        return true;
    }
    return false;
}

// Function to get a specific post by ID
function get_post($post_id) {
    $post = get_post_by_id($post_id);
    echo json_encode($post);
}

// Function to list posts
function list_posts() {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $search = $_GET['search'] ?? ''; // Get the search query from the request

    if (!empty($search)) {
        // Search for posts based on the title
        $posts = search_posts($search, $page, POSTS_PER_PAGE);
        $total_count = count_searched_posts($search);
    } else {
        // List all posts
        $posts = get_posts($page, POSTS_PER_PAGE);
        $total_count = count_all_posts();
    }

    $response = array(
        'total_count' => $total_count,
        'posts' => $posts
    );

    header('Content-Type: application/json');
    echo json_encode($response);
}

// Function to create a new post
function create_post($data) {
    // Get the authenticated user's user_id from the session or request headers
    $user_id = 1; // Replace this with the actual user_id

    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';

    if (!$title || !$content) {
        http_response_code(400);
        echo json_encode(array('message' => 'Title and content are required.'));
        return;
    }

    // You should validate the input data before insertion

    $post_id = create_post_in_db($user_id, $title, $content);

    if ($post_id) {
        http_response_code(201);
        echo json_encode(array('message' => 'Post created successfully.', 'post_id' => $post_id));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to create post.'));
    }
}

// Function to edit an existing post
function edit_post($post_id, $data) {
    // Get the authenticated user's user_id from the session or request headers
    $user_id = 1; // Replace this with the actual user_id

    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';

    if (!$title || !$content) {
        http_response_code(400);
        echo json_encode(array('message' => 'Title and content are required.'));
        return;
    }

    // Check if the authenticated user is the owner of the post
    if (!is_post_owner($user_id, $post_id)) {
        http_response_code(403);
        echo json_encode(array('message' => 'You are not authorized to edit this post.'));
        return;
    }

    // You should validate the input data before updating

    $success = update_post_in_db($post_id, $title, $content);

    if ($success) {
        http_response_code(200);
        echo json_encode(array('message' => 'Post updated successfully.'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to update post.'));
    }
}

// Function to delete a post
function delete_post($post_id) {
    // Get the authenticated user's user_id from the session or request headers
    $user_id = 1; // Replace this with the actual user_id

    // Check if the authenticated user is the owner of the post
    if (!is_post_owner($user_id, $post_id)) {
        http_response_code(403);
        echo json_encode(array('message' => 'You are not authorized to delete this post.'));
        return;
    }

    $success = delete_post_from_db($post_id);

    if ($success) {
        http_response_code(200);
        echo json_encode(array('message' => 'Post deleted successfully.'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to delete post.'));
    }
}
