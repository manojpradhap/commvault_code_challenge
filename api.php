<?php
// Include the business logic layer
require_once 'bll.php';

// Define the number of posts to display per page
define("POSTS_PER_PAGE", 10);

// Function to check if the user is authenticated before allowing access to certain APIs.
function authorize_user() {
    $username = $_SERVER['PHP_AUTH_USER'] ?? '';
    $password = $_SERVER['PHP_AUTH_PW'] ?? '';
    return authenticate_user($username, $password);
}

// Main router for handling API requests
if (authorize_user()) {
    $request_method = $_SERVER['REQUEST_METHOD'];

    switch ($request_method) {
        case 'GET':
            $post_id = $_GET['post_id'] ?? null;
            if ($post_id) {
                // Retrieve a specific post by ID
                get_post($post_id);
            } else {
                // List all posts
                list_posts();
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            create_post($data);
            break;
        case 'PUT':
            $post_id = $_GET['post_id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true);
            edit_post($post_id, $data);
            break;
        case 'DELETE':
            $post_id = $_GET['post_id'] ?? null;
            delete_post($post_id);
            break;
        default:
            // Handle unsupported request methods
            http_response_code(405);
            break;
    }
} else {
    // Unauthorized access
    http_response_code(401);
}
?>
