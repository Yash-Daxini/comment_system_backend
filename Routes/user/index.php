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
    'origin' => ['http://localhost:3000'],
    'methods' => ['GET', 'POST', 'OPTIONS'],
    'headers.allow' => ['Authorization', 'If-Match', 'Content-Type'],
    'headers.expose' => ['Authorization', 'Etag'],
]));


$app->get('/Routes/user', function (Request $request, Response $response, array $args) use ($db) {
    $query = "SELECT * FROM user";
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

$app->post('/Routes/user', function (Request $request, Response $response, array $args) use ($db) {
    $body = $request->getBody();
    $data = json_decode($body, true);

    $user_Name = $data['user_Name'];
    $user_Email = $data['user_Email'];
    $password = $data['password'];


    $query = "INSERT INTO user (user_Name,user_Email,password) VALUES (?,?,?)";
    $statement = $db->prepare($query);

    if ($statement) {
        $statement->bind_param('sss', $user_Name, $user_Email, $password);
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
$app->put('/Routes/user/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $id = $args['id'];
    $body = $request->getBody();
    $data = json_decode($body, true);

    $user_Name = $data['user_Name'];
    $user_Email = $data['user_Email'];
    $password = $data['password'];

    $query = "Update user set user_Name = ? ,user_Email = ?, password = ?
    WHERE userId = ?";
    $statement = $db->prepare($query);

    if ($statement) {
        $statement->bind_param('sssi', $user_Name, $user_Email, $password, $id);
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
$app->delete('/Routes/user/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $id = $args['id'];
    $query = "delete from user WHERE userId = ?";
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