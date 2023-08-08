const API_URL = 'http://localhost/assignment/api.php';
const POSTS_PER_PAGE = 10; // Number of posts to display per page

// Function to fetch data from the API
async function fetchData(endpoint, method = 'GET', data = {}) {
    // Prepare the options for the fetch request
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Basic ' + btoa('manoj:Pass@123')
        }
    };
    // Include the request body for non-GET and non-HEAD methods
    if (method !== 'GET' && method !== 'HEAD') {
        options.body = JSON.stringify(data);
    }
    try {
        // Send the fetch request to the specified endpoint
        const response = await fetch(`${API_URL}/${endpoint}`, options);
        // Check if the response indicates an error
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        // Parse the response body as JSON
        const responseData = await response.json();
        // Return the response status and data
        return {
            status: response.status,
            data: responseData
        };
    } catch (error) {
        console.error(error);
        throw error;
    }
}

// Function to create a new post
async function createPost() {
    // Get the values of the post title and content from the input fields
    const title = document.getElementById('post-title').value;
    const content = document.getElementById('post-content').value;

    // Check if both title and content are provided
    if (!title || !content) {
        alert('Please fill in both title and content.');
        return;
    }

    // Create an object with the post data
    const postData = {
        title,
        content
    };
    try {
        // Send the post data to the API using the 'POST' method
        const response = await fetchData('posts', 'POST', postData);
        console.log(response); // Log the response object for debugging

        // Check if the post was successfully created
        if (response.status === 201) {
            alert('Post created successfully. Post ID: ' + response.data.post_id);
            displayPosts('', 1); // Refresh the post list after successful creation
        } else {
            alert('Failed to create post.');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred while creating the post.');
    }
}

// Function to search posts based on title
async function searchPosts() {
    // Get the search query from the input field
    const searchQuery = document.getElementById('search-input').value;

    try {
        // Fetch posts that match the search query using the 'GET' method
        const response = await fetchData(`posts?search=${encodeURIComponent(searchQuery)}`);
        
        // Display the fetched posts that match the search query
        displayPosts(response);
    } catch (error) {
        console.error(error);
        alert('An error occurred while searching for posts.');
    }
}


// Function to edit a post
async function editPost(postId) {
    try {
        // Fetch the post with the specified ID using the new fetchPostById function
        const post = await fetchPostById(postId);

        if (post) {
            const postTitle = prompt('Enter new title:', post.title);
            const postContent = prompt('Enter new content:', post.content);

            if (postTitle !== null && postContent !== null) {
                const updatedPost = {
                    title: postTitle,
                    content: postContent
                };

                // Send a PUT request to update the post
                await fetchData(`posts?post_id=${postId}`, 'PUT', updatedPost);
                displayPosts('', 1); // Refresh the post list after successful edit
            }
        } else {
            console.error(`Post with ID ${postId} not found.`);
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred while editing the post.');
    }
}

// Function to fetch a single post by its ID
async function fetchPostById(postId) {
    try {
        // Fetch the post with the specified ID
        const response = await fetchData(`posts?post_id=${postId}`);
        return response.data;
    } catch (error) {
        console.error(error);
        return null;
    }
}



// Delete a post
async function deletePost(postId) {
    // Confirm the deletion with the user
    const confirmed = confirm('Are you sure you want to delete this post?');
    
    if (confirmed) {
        try {
            // Send a DELETE request to delete the post
            const response = await fetchData(`posts/?post_id=${postId}`, 'DELETE');
            
            if (response.status === 200) {                
                alert('Post deleted successfully.');
                removePostFromUI(postId); // Remove the deleted post from the UI
            } else if (response.status === 403) {
                alert('You are not authorized to delete this post.');
            } else {
                alert('Failed to delete post.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred while deleting the post.');
        }
    }
}

// Helper function to remove a post element from the UI
function removePostFromUI(postId) {
    // Find the post element by its unique ID
    const postElement = document.getElementById(`post_${postId}`);
    
    // Check if the post element exists
    if (postElement) {
        postElement.remove(); // Remove the post element from the UI
    }
}


// Function to display posts on a specific page or based on search results
async function displayPosts(responseData, pageNumber = 1) {
    const startIndex = (pageNumber - 1) * POSTS_PER_PAGE;
    try {
        let response = "";
        if (responseData === "") { 
            response = await fetchData(`posts?page=${pageNumber}`);
        } else { 
            response = responseData;
        } 
        const posts = response.data.posts;

        // Check if the retrieved data is an array
        if (!Array.isArray(posts)) {
            console.error('Invalid response: posts is not an array', response);
            return;
        }

        const postList = document.getElementById('post-list');
        postList.innerHTML = '';

        // Loop through each post and create a list item element
        posts.forEach((post) => {
            const listItem = document.createElement('li');
            listItem.setAttribute('id', `post_${post.post_id}`);
            listItem.classList.add('list-group-item');
            listItem.innerHTML = `
                <div>
                    <h5 class="mb-1">${post.title}</h5>
                    <p class="mb-1">${post.content}</p>
                    <small>Created at: ${post.created_at}</small>
                </div>
                <div class="text-right">
                    <button class="btn btn-sm btn-primary" onclick="editPost(${post.post_id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deletePost(${post.post_id})">Delete</button>
                </div>
            `;
            postList.appendChild(listItem);
        });

        // Update pagination
        const pagination = document.getElementById('pagination');
        const totalPages = Math.ceil(response.data.total_count / POSTS_PER_PAGE);
        let paginationHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += `<li class="page-item${pageNumber === i ? ' active' : ''}">
                <a class="page-link" href="#" onclick="displayPosts('',${i})">${i}</a>
            </li>`;
        }
        pagination.innerHTML = paginationHTML;
    } catch (error) {
        console.error('An error occurred while fetching and displaying posts', error);
    }
}


// Initial setup: display first page of posts
displayPosts("",1);




