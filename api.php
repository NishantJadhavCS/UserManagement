<?php
include 'db.php';

// Function to fetch all users
function getAllUsers($con)
{
    $query = "SELECT * FROM users";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Function to fetch a single user by ID
function getUserById($con, $id)
{
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Function to add a new user
function addUser($con, $username, $email)
{
    $query = "INSERT INTO users (username, email, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $username, $email);
    if ($stmt->execute()) {
        return $con->insert_id; 
    } else {
        return false;
    }
}

function updateUser($con, $id, $username, $email)
{
    $query = "UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $username, $email, $id);
    return $stmt->execute();
}

function deleteUser($con, $id)
{
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

header('Content-Type: application/json');

$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method === 'GET') {
    if (isset($_GET['id'])) {
        $userId = intval($_GET['id']);
        $user = getUserById($con, $userId);

        if ($user) {
            echo json_encode($user,JSON_PRETTY_PRINT);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
    } else {
        $users = getAllUsers($con);
        echo json_encode($users, JSON_PRETTY_PRINT);
    }
} elseif ($request_method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['username']) && isset($input['email'])) {
        $username = $input['username'];
        $email = $input['email'];

        $userId = addUser($con, $username, $email);
        if ($userId) {
            echo json_encode(["id" => $userId, "username" => $username, "email" => $email]);
        } else {
            echo json_encode(["error" => "Failed to add user"]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
} elseif ($request_method === 'PATCH') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['id']) && isset($input['username']) && isset($input['email'])) {
        $id = intval($input['id']);
        $username = $input['username'];
        $email = $input['email'];
        if (updateUser($con, $id, $username, $email)) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update user"]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
} elseif ($request_method === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['id'])) {
        $id = intval($input['id']);
        if (deleteUser($con, $id)) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            echo json_encode(["error" => "Failed to delete user"]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
} else {
    echo json_encode(["error" => "Unsupported request method"]);
}

mysqli_close($con); 
?>