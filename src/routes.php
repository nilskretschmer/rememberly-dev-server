<?php

use Rememberly\Persistence\DatabaseOperator;
use Rememberly\Authentication\TokenManager;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

// Create new Todo for Todolist associated with user
$app->post('/api/todo/new', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    $userID = $token['user_id'];
    $todolist_permissions = $token['todolistPermissions'];
    $input = $request->getParsedBody();
    $list_id = $input['list_id'];
    $expires_on = $input['expires_on'];
    $todo_text = $input['todo_text'];
    $databaseOperator = new DatabaseOperator($this->db);
    $responseObject;
    if (in_array($list_id, $todolist_permissions)) {
      if (isset($expires_on)) {
        $this->logger->info("Insert todo with expiration");
        $responseObject = $databaseOperator->insertTodoWithExpiration($list_id,
         $expires_on, $todo_text);
      } else {
        $this->logger->info("Insert todo");
        $responseObject = $databaseOperator->insertTodo($list_id, $todo_text);
      }
    } else {
      $responseObject->message = "Not authorized!";
      return $this->response->withJson($responseObject);
    }
    return $this->response->withJson($responseObject);
});
// update todo (name, check status,..)
$app->put('/api/todo/update', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $userID = $token['user_id'];
  $todolist_permissions = $token['todolistPermissions'];
  $input = $request->getParsedBody();
  $list_id = $input['list_id'];
  $expires_on = $input['expires_on'];
  $todo_text = $input['todo_text'];
  $todo_id = $input['todo_id'];
  $is_checked = intval($input['is_checked']);
  $this->logger->info("Update Todo with Check Status: " . $is_checked);
  $databaseOperator = new DatabaseOperator($this->db);
  $responseObject;
  if (in_array($list_id, $todolist_permissions)) {
    if (isset($expires_on)) {
      $this->logger->info("Update todo with expiration");
      $responseObject = $databaseOperator->updateTodoWithExpiration($expires_on, $todo_text, $todo_id, $is_checked, $list_id);
    } else {
      $this->logger->info("Update todo without expiration");
      $responseObject = $databaseOperator->updateTodo($todo_id, $is_checked, $todo_text, $list_id);
    }
  } else {
    $responseObject->message = "Not authorized!";
    return $this->response->withJson($responseObject);
  }
  return $this->response->withJson($responseObject);
});
// Get all todos of a list with list id
$app->get('/api/todos/[{list_id}]', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $userID = $token['user_id'];
  $permissions = $token['todolistPermissions'];
  $list_id = $args['list_id'];
  $responseObject;
  if (in_array($list_id, $permissions)) {
    $databaseOperator = new DatabaseOperator($this->db);
    $responseObject = $databaseOperator->getTodos($list_id);
  } else {
    $responseObject->message = "No permissions or list not found";
  }
  return $this->response->withJson($responseObject);
});
// get a new token with an old token (which is still in time)
$app->post('/api/tokenrefresh', function (Request $request, Response $response, array $args) {
   $token = $request->getAttribute("decoded_token_data");
   $userID = $token['user_id'];
   $username = $token['username'];
   // Maybe the permissions have changed
   $databaseOperator = new DatabaseOperator($this->db);
   $todolist_permissions = $databaseOperator->getUserTodolistPermissions($userID);
   $noticesPermissions = $databaseOperator->getUserNoticesPermissions($userID);
   $androidAppID = $token['androidAppID'];
   $tokenManager = new TokenManager($this->get('settings'));
   $token = $tokenManager->createUserToken($userID, $username, $todolist_permissions, $noticesPermissions, $androidAppID);
   return $this->response->withJson(['token' => $token]);
});
// Endpoint should return Statuscode 401 if token is no more valid
$app->post('/api/tokenlogin', function (Request $request, Response $response, array $args) {
   return $this->response->withJson(['message' => "Login successful"]);
});
// update a notice's name
$app->put('/api/notice/update', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $input = $request->getParsedBody();
  $noticeID = $input['noticeID'];
  $noticeName = $input['noticeName'];
  $permissions = $token['noticesPermissions'];
  if (in_array($noticeID, $permissions)) {
    $databaseOperator = new DatabaseOperator($this->db);
    $databaseOperator->updateNotice($noticeID, $noticeName);
  } else {
    // Deletion forbidden (not owner)
    return $this->response->withStatus(403);
  }
  $returnMessage->message = "Notice updated";
  return $this->response->withJson($returnMessage);
});
// update todolist name
$app->put('/api/todolist/update', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $input = $request->getParsedBody();
  $list_id = $input['list_id'];
  $list_name = $input['list_name'];
  $permissions = $token['todolistPermissions'];
  if (in_array($list_id, $permissions)) {
    $databaseOperator = new DatabaseOperator($this->db);
    $databaseOperator->updateTodolist($list_id, $list_name);
  } else {
    // Deletion forbidden (not owner)
    return $this->response->withStatus(403);
  }
  $returnMessage->message = "Todolist updated";
  return $this->response->withJson($returnMessage);
});
// Create new todolist with user id from token
$app->post('/api/todolist/new', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    // TODO: Error Handling
    $userID = $token['user_id'];
    $input = $request->getParsedBody();
    // set permissions in DB and send new token
    $databaseOperator = new DatabaseOperator($this->db);
    $responseObject = $databaseOperator->createTodolist($input['list_name'], $userID);
    return $this->response->withJson($responseObject);
});
// create a new notice
$app->post('/api/notice/new', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    // TODO: Error Handling
    $userID = $token['user_id'];
    $input = $request->getParsedBody();
    // set permissions in DB and send new token
    $databaseOperator = new DatabaseOperator($this->db);
    $responseObject = $databaseOperator->createNotice($input['noticeName'], $userID);
    return $this->response->withJson($responseObject);
});
// delete todolist with list_id
$app->delete('/api/todolist/delete/[{list_id}]', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $list_id = $args['list_id'];
  $permissions = $token['todolistPermissions'];
  $databaseOperator = new DatabaseOperator($this->db);
  if (in_array($list_id, $permissions)) {
    $databaseOperator->deleteTodolist($list_id);
  } else {
    // Deletion forbidden (not owner)
    return $this->response->withStatus(403);
  }
  $returnMessage->message = "Todolist deleted";
  return $this->response->withJson($returnMessage);
});
// delete notice with noticeID
$app->delete('/api/notice/delete/[{noticeID}]', function (Request $request, Response $response, array $args) {
  $token = $request->getAttribute("decoded_token_data");
  $noticeID = $args['noticeID'];
  $permissions = $token['noticesPermissions'];
  $databaseOperator = new DatabaseOperator($this->db);
  if (in_array($noticeID, $permissions)) {
    $databaseOperator->deleteNotice($noticeID);
  } else {
    // Deletion forbidden (not owner)
    return $this->response->withStatus(403);
  }
  $returnMessage->message = "Todolist deleted";
  return $this->response->withJson($returnMessage);
});
// share todolist with user (username provided in html body)
$app->post('/api/todolist/share', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    $parsedBody = $request->getParsedBody();
    $list_id = $parsedBody['list_id'];
    $username = $parsedBody['username'];
    $todolist_permissions = $token['todolistPermissions'];
    $responseObject;
    if (in_array($list_id, $todolist_permissions)) {
      // set permissions to new user
      try {
      $databaseOperator = new DatabaseOperator($this->db);
      $userID = $databaseOperator->getUserID($username);
      $databaseOperator->setTodolistPermissions($userID, $list_id);
      $databaseOperator->setTodolistShared($list_id);
      $responseObject->message = "Todolist shared with " . $username;
    } catch (PDOException $pdoe) {
      return $this->response->withStatus(404);
    }
    } else {
      $responseObject->message = "Failed to share Todolist";
    }
    return $this->response->withJson($responseObject);
});
// share notice with username (username in html body)
$app->post('/api/notice/share', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    $parsedBody = $request->getParsedBody();
    $noticeID = $parsedBody['noticeID'];
    $username = $parsedBody['username'];
    $noticesPermissions = $token['noticesPermissions'];
    $responseObject;
    if (in_array($noticeID, $noticesPermissions)) {
      // set permissions to new user
      try {
      $databaseOperator = new DatabaseOperator($this->db);
      $userID = $databaseOperator->getUserID($username);
      $databaseOperator->setNoticePermissions($userID, $noticeID);
      $databaseOperator->setNoticeShared($noticeID);
      $responseObject->message = "Notice shared with " . $username;
    } catch (PDOException $pdoe) {
      return $this->response->withStatus(404);
    }
    } else {
      $responseObject->message = "Failed to share Todolist";
    }
    return $this->response->withJson($responseObject);
});
// Get TodoList of User
$app->get('/api/todolist/[{list_id}]', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
    $userID = $token['user_id'];
    $permissions = $token['todolistPermissions'];
    $in  = str_repeat('?,', count($permissions) - 1) . '?';
    $this->logger->info("User ID: " . $userID . " is trying to access list with ID: " . $args['list_id']
  . " with permissions to lists: " . print_r($permissions, true));
    $sql = "SELECT * FROM todolists WHERE list_id=? AND list_id IN ($in)";
    $sth = $this->db->prepare($sql);
    $params = array_merge([$args['list_id']], $permissions);
    $sth->execute($params);
    $todolist = $sth->fetchObject();
    // false if none found or no permission
    if ($todolist == false) {
      // error
      $error->message = "No permissions or list not found!";
      return $this->response->withJson($error);
    }
    return $this->response->withJson($todolist);
});
// Get all Todolists associated with user id
$app->get('/api/todolists', function (Request $request, Response $response, array $args) {
    $token = $request->getAttribute("decoded_token_data");
        $userID = $token['user_id'];
        $todolist_permissions = $token['todolistPermissions'];
        $in  = str_repeat('?,', count($todolist_permissions) - 1) . '?';
        $this->logger->info("User has permission to access lists: " . print_r($todolist_permissions, true));
        $sql = "SELECT * FROM todolists WHERE list_id IN ($in)";
        $sth = $this->db->prepare($sql);
        try {
        $sth->execute($todolist_permissions);
        $todolist = $sth->fetchAll(); // false if none found or no permission
        return $this->response->withJson($todolist);
      } catch (PDOException $pdoe) {
        // no permissions found
        return $this->response->withStatus(404);
      }
    });
    // get all notices of user
    $app->get('/api/notices', function (Request $request, Response $response, array $args) {
        $token = $request->getAttribute("decoded_token_data");
            $userID = $token['user_id'];
            $noticesPermissions = $token['noticesPermissions'];
            $in  = str_repeat('?,', count($noticesPermissions) - 1) . '?';
            $this->logger->info("User has permission to access notices: " . print_r($noticesPermissions, true));
            $sql = "SELECT * FROM notices WHERE noticeID IN ($in)";
            $sth = $this->db->prepare($sql);
            try {
            $sth->execute($noticesPermissions);
            $notices = $sth->fetchAll(); // false if none found or no permission
            return $this->response->withJson($notices);
          } catch (PDOException $pdoe) {
            // no permissions found
            return $this->response->withStatus(404);
          }
        });
// render the index page (welcome page)
$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->renderer->render($response, 'index.phtml', $args);
});
// Testing purposes not for production
$app->get('/api/tokendecode', function (Request $request, Response $response, array $args) {
    // Sample log message
    $token = $request->getAttribute("decoded_token_data");
    return $this->response->withJson($token);
});
// create user
$app->post('/user/create', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    if (isset($parsedBody['user']) && isset($parsedBody['password'])) {
      $user = $parsedBody['user'];
      $password = $parsedBody['password'];
      $databaseOperator = new DatabaseOperator($this->db);
      // return message
      $jsonResponse = $databaseOperator->createUser($user, $password);
      $statusCode = $jsonResponse["status"];
      return $this->response->withJson($jsonResponse, $statusCode);
    } else {
      $jsonResponse = array('message' => "Username/Password not found.", 'status' => 404);
      $statusCode = $jsonResponse["status"];
      return $this->response->withJson($jsonResponse, $statusCode);
    }
});
// route for logging in. This is just http basic authentication
$app->get('/login', function (Request $request, Response $response, array $args) {
    $username = $request->getServerParam('PHP_AUTH_USER');
    if (isset($username)) {
        $this->logger->info("Authenticated user: " . $username);
        $databaseOperator = new DatabaseOperator($this->db);
        $tokenManager = new TokenManager($this->get('settings'));
        $userID = $databaseOperator->getUserID($username);
        $todolist_permissions = $databaseOperator->getUserTodolistPermissions($userID);
        $notices_permissions = $databaseOperator->getUserNoticesPermissions($userID);
        $androidAppID = $databaseOperator->getAndroidAppID($userID);
        $token = $tokenManager->createUserToken($userID, $username, $todolist_permissions, $notices_permissions, $androidAppID);
        return $this->response->withJson(['token' => $token]);
    }
});
