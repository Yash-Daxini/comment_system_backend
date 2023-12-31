<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require '../../vendor/autoload.php';

require '../../include/DBOps/DBConnection.php';

$conn = new DBConnection();
$db = $conn->mConnect();

$app = AppFactory::create();

$app->add(new Tuupola\Middleware\CorsMiddleware([
    'origin' => ['*'],  // Adjust to your React app's port
    'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'headers.allow' => ['Authorization', 'If-Match', 'Content-Type'],
    'headers.expose' => ['Authorization', 'Etag'],
]));

$app->get('/Routes/comment', function (Request $request, Response $response, array $args) use ($db) {
    $query = "SELECT * FROM comment inner join user where comment.userId = user.userId";
    $result = $db->query($query);

    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $response->getBody()->write(json_encode($data));
        return $response;
    } else {
        $response->getBody()->write(json_encode(["error" => "Failed to fetch data"]));
        return $response;
    }
});

$app->post('/Routes/comment', function (Request $request, Response $response, array $args) use ($db) {
    $body = $request->getBody();
    $data = json_decode($body, true);

    $comment_Description = $data['comment_Description'];
    $userId = $data['userId'];
    $upvotes = $data['upvotes'];
    $downvotes = $data['downvotes'];
    $postId = $data['postId'];
    $parentComment_Id = $data['parentComment_Id'];
    $creation_Date = $data['creation_Date'];
    $modification_Date = $data['modification_Date'];

    $query = "INSERT INTO comment (comment_Description,userId,upvotes,downvotes,postId,parentComment_Id,creation_Date,modification_Date) VALUES (?,?,?,?,?,?,?,?)";
    $statement = $db->prepare($query);

    if ($statement) {
        $statement->bind_param('siiiiiss', $comment_Description, $userId, $upvotes, $downvotes, $postId, $parentComment_Id, $creation_Date, $modification_Date);
        $result = $statement->execute();

        if ($result) {
            $response->getBody()->write(json_encode(["message" => "Data inserted successfully", "data" => $data]));
            return $response;
        } else {
            $response->getBody()->write(json_encode(["error" => "Failed to insert data"]));
            return $response;
        }
    } else {
        $response->getBody()->write(json_encode(["error" => "Failed to prepare statement"]));
        return $response;
    }
});
$app->get('/Routes/comment/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $id = $args['id'];
    $query = "SELECT * FROM comment inner join user where comment.userId = user.userId && comment.comment_Id = $id";
    $result = $db->query($query);

    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $response->getBody()->write(json_encode($data));
        return $response;
    } else {
        $response->getBody()->write(json_encode(["error" => "Failed to fetch data"]));
        return $response;
    }
});
$app->put('/Routes/comment/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $id = $args['id'];
    $body = $request->getBody();
    $data = json_decode($body, true);

    $comment_Description = $data['comment_Description'];
    $userId = $data['userId'];
    $upvotes = $data['upvotes'];
    $downvotes = $data['downvotes'];
    $postId = $data['postId'];
    $parentComment_Id = $data['parentComment_Id'];
    $creation_Date = $data['creation_Date'];
    $modification_Date = $data['modification_Date'];

    $query = "Update comment set comment_Description = ? ,userId = ?, upvotes = ? , downvotes = ? , postId = ? , parentComment_Id = ? , creation_Date = ?,modification_Date = ? WHERE comment_Id = ?";
    $statement = $db->prepare($query);

    if ($statement) {
        $statement->bind_param('siiiiissi', $comment_Description, $userId, $upvotes, $downvotes, $postId, $parentComment_Id, $creation_Date, $modification_Date, $id);
        $result = $statement->execute();
        if ($result) {
            $response->getBody()->write(json_encode(["message" => "Data updated successfully", "data" => $data]));
            return $response;
        } else {
            $response->getBody()->write(json_encode(["error" => "Failed to update data"]));
            return $response;
        }
    } else {
        $response->getBody()->write(json_encode(["error" => "Failed to prepare statement"]));
        return $response;
    }
});
$app->delete('/Routes/comment/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $id = $args['id'];
    $query = "delete from comment WHERE comment_Id = ?";
    $statement = $db->prepare($query);
    if ($statement) {
        $statement->bind_param('i', $id);
        $result = $statement->execute();

        if ($result) {
            $response->getBody()->write(json_encode(["message" => "Data deleted successfully"]));
            return $response;
        } else {
            $response->getBody()->write(json_encode(["error" => "Failed to delete data"]));
            return $response;
        }
    } else {
        $response->getBody()->write(json_encode(["error" => "Failed to prepare statement"]));
        return $response;
    }
});

$app->run();