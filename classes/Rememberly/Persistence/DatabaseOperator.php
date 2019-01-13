<?php

namespace Rememberly\Persistence;

  class DatabaseOperator
  {
      private $dbconnection;
      public function __construct($dbconnection)
      {
          $this->dbconnection = $dbconnection;
      }

      public function getUserTodolistPermissions($user_id)
      {
          $query = "SELECT list_id FROM todolistPermissions WHERE user_id='$user_id'";
          $sth = $this->dbconnection->prepare($query);
          $sth->execute();
          $resultPermissions = "";
          for ($i = 0; $i < $sth->rowCount(); $i++) {
            if ($i == $sth->rowCount() - 1) {
              $result = $sth->fetchColumn();
              $resultPermissions .= $result;
            } else {
              $result = $sth->fetchColumn();
              $resultPermissions .= $result . ",";
            }
          }
          return explode(',', $resultPermissions);
      }
      public function getUserNoticesPermissions($user_id)
      {
          $query = "SELECT noticeID FROM noticesPermissions WHERE userID='$user_id'";
          $sth = $this->dbconnection->prepare($query);
          $sth->execute();
          $resultPermissions = "";
          for ($i = 0; $i < $sth->rowCount(); $i++) {
            if ($i == $sth->rowCount() - 1) {
              $result = $sth->fetchColumn();
              $resultPermissions .= $result;
            } else {
              $result = $sth->fetchColumn();
              $resultPermissions .= $result . ",";
            }
          }
          return explode(',', $resultPermissions);
      }
      public function getUserID($username)
      {
          $sqluser = "SELECT user_id FROM users WHERE username='$username'";
          $sth = $this->dbconnection->prepare($sqluser);
          $sth->execute();
          $res = $sth->fetch(\PDO::FETCH_ASSOC);
          return $res['user_id'];
      }
      public function getTodos($list_id) {
        $sql = "SELECT list_id, created_at, expires_on, todo_text, todo_id, is_checked
        FROM todos WHERE list_id=:list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        return ($sth->fetchAll());
      }
      public function getAndroidAppID($user_id) {
        $sql = "SELECT androidAppID FROM users WHERE user_id = :user_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("user_id", $user_id);
        $sth->execute();
        $res = $sth->fetch(\PDO::FETCH_ASSOC);
        return $res['androidAppID'];
      }
      public function createUser($user, $password)
      {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $jsonResponse = array('message' => "Unknown error.", 'status' => 500);
              try {
                  $sth = $this->dbconnection->prepare("INSERT INTO users (username, passwordhash) VALUES ('{$user}', '{$hash}')");
                  $sth->execute();
                  $jsonResponse = array('message' => "User " . $user . " successfully created.", 'status' => 201);
              } catch (\PDOException $e) {
                  if ($e->getCode() == 23000) {
                      $jsonResponse = array('message' => "Username already registered.", 'status' => 403);
                  } else {
                      $jsonResponse = array('message' => "Unknown error.", 'status' => 400);
                  }
              }
          return $jsonResponse;
      }
      public function createTodolist($list_name, $user_id) {
        $sql = "INSERT INTO todolists (list_name, owner) VALUES (:list_name, :user_id)";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_name", $list_name);
        $sth->bindParam("user_id", $user_id);
        $sth->execute();
        //  set list_id to the auto increment value from DB
        $list_id = $this->dbconnection->lastInsertId();
        $input['list_id'] = $list_id;
        $this->setTodolistPermissions($user_id, $list_id);
        $sql = "SELECT * FROM todolists WHERE list_id = :list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        $responseObject = $sth->fetch();
        $sth = null;
        return $responseObject;
      }
      public function createNotice($noticeName, $userID) {
        $sql = "INSERT INTO notices (noticeName, owner) VALUES (:noticeName, :userID)";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeName", $noticeName);
        $sth->bindParam("userID", $userID);
        $sth->execute();
        //  set list_id to the auto increment value from DB
        $noticeID = $this->dbconnection->lastInsertId();
        $input['noticeID'] = $noticeID;
        $this->setNoticePermissions($userID, $noticeID);
        $sql = "SELECT * FROM notices WHERE noticeID = :noticeID";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeID", $noticeID);
        $sth->execute();
        $responseObject = $sth->fetch();
        $sth = null;
        return $responseObject;
      }
      // Delete Todolist with list_id and delete the Todolistpermissions and the todos related to the todolist

      public function deleteTodolist($list_id) {
        $sql = "DELETE FROM todolists WHERE list_id=:list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        $this->deleteTodolistPermissions($list_id);
        $this->deleteTodos($list_id);
        $sth = null;
      }
      public function deleteNotice($noticeID) {
        $sql = "DELETE FROM notices WHERE noticeID=:noticeID";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeID", $noticeID);
        $sth->execute();
        $this->deleteNoticePermissions($noticeID);
        // TODO: delete content of notice
        //$this->deleteContent($noticeID);
        $sth = null;
      }
      public function updateTodolist($list_id, $list_name) {
        $sql = "UPDATE todolists SET list_name = :list_name WHERE list_id = :list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_name", $list_name);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        $sth = null;
      }
      public function updateNotice($noticeID, $noticeName) {
        $sql = "UPDATE notices SET noticeName = :noticeName WHERE noticeID = :noticeID";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeName", $noticeName);
        $sth->bindParam("noticeID", $noticeID);
        $sth->execute();
        $sth = null;
      }
      public function deleteTodolistPermissions($list_id) {
        $sql = "DELETE FROM todolistPermissions WHERE list_id=:list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        $sth = null;
      }
      public function deleteNoticePermissions($noticeID) {
        $sql = "DELETE FROM noticesPermissions WHERE noticeID=:noticeID";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeID", $noticeID);
        $sth->execute();
        $sth = null;
      }
      public function deleteTodos($list_id) {
        $sql = "DELETE FROM todos WHERE list_id=:list_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
        $sth = null;
      }
      public function setTodolistPermissions($user_id, $list_id) {
        $sql = "INSERT INTO todolistPermissions (list_id, user_id) VALUES (:list_id, :user_id)";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("list_id", $list_id);
        $sth->bindParam("user_id", $user_id);
        $sth->execute();
        $sth = null;
      }
      public function setNoticePermissions($userID, $noticeID) {
        $sql = "INSERT INTO noticesPermissions (noticeID, userID) VALUES (:noticeID, :userID)";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("noticeID", $noticeID);
        $sth->bindParam("userID", $userID);
        $sth->execute();
        $sth = null;
      }
      public function setNoticeShared($noticeID) {
        $sql = "UPDATE notices SET isShared = :isShared WHERE noticeID = :noticeID";
        try {
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("isShared", $a = 1);
        $sth->bindParam("noticeID", $noticeID);
        $sth->execute();
      } catch (\PDOException $e) {
              $responseObject->message = $e->getMessage();
              return $responseObject;
      }
      $responseObject->message = "Notice successfully shared";
      return $responseObject;
      }
      public function setTodolistShared($list_id) {
        $sql = "UPDATE todolists SET isShared = :isShared WHERE list_id = :list_id";
        try {
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindParam("isShared", $a = 1);
        $sth->bindParam("list_id", $list_id);
        $sth->execute();
      } catch (\PDOException $e) {
              $responseObject->message = $e->getMessage();
              return $responseObject;
      }
      $responseObject->message = "Todolist successfully shared";
      return $responseObject;
      }
      public function insertTodoWithExpiration($list_id, $expires_on, $todo_text) {
        $responseObject;
      try {
          $sql = "INSERT INTO todos (list_id, expires_on, todo_text, is_checked)
          VALUES (:list_id, :expires_on, :todo_text, :is_checked)";
          $sth = $this->dbconnection->prepare($sql);
          $sth->bindParam("list_id", $list_id);
          $sth->bindValue("expires_on", $expires_on);
          $sth->bindParam("todo_text", $todo_text);
          $sth->bindParam("is_checked", $a = 0);
          $sth->execute();
      } catch (\PDOException $e) {
              $responseObject->message = "An error occured";
              return $responseObject;
      }
      $responseObject->message = "Todo successfully inserted";
      $responseObject->todo_id = $this->dbconnection->lastInsertId();
      return $responseObject;
    }
    public function updateTodoWithExpiration($expires_on, $todo_text, $todo_id, $is_checked, $list_id) {
      $responseObject;
    try {
        $sql = "UPDATE todos SET expires_on = :expires_on, is_checked = :is_checked, todo_text = :todo_text
        WHERE todo_id = :todo_id";
        $sth = $this->dbconnection->prepare($sql);
        $sth->bindValue("expires_on", $expires_on);
        $sth->bindParam("is_checked", $is_checked);
        $sth->bindParam("todo_text", $todo_text);
        $sth->bindParam("todo_id", $todo_id);
        $sth->execute();
        $this->removeOldTodos($list_id);
    } catch (\PDOException $e) {
            $responseObject->message = $e->getMessage();
            return $responseObject;
    }
    $responseObject->message = "Todo successfully updated";
    return $responseObject;
  }
  public function updateTodo($todo_id, $is_checked, $todo_text, $list_id) {
    $responseObject;
  try {
      $sql = "UPDATE todos SET is_checked = :is_checked, todo_text = :todo_text
      WHERE todo_id = :todo_id";
      $sth = $this->dbconnection->prepare($sql);
      $sth->bindParam("is_checked", $is_checked);
      $sth->bindParam("todo_text", $todo_text);
      $sth->bindParam("todo_id", $todo_id);
      $sth->execute();
      $this->removeOldTodos($list_id);
  } catch (\PDOException $e) {
          $responseObject->message = $e->getMessage();
          return $responseObject;
  }
  $responseObject->message = "Todo successfully updated";
  return $responseObject;
}
    public function insertTodo($list_id, $todo_text) {
      $responseObject;
      try {
          $sql = "INSERT INTO todos (list_id, todo_text, is_checked)
          VALUES (:list_id, :todo_text, :is_checked)";
          $sth = $this->dbconnection->prepare($sql);
          $sth->bindParam("list_id", $list_id);
          $sth->bindParam("todo_text", $todo_text);
          $sth->bindParam("is_checked", $a = 0);
          $sth->execute();
      } catch (\PDOException $e) {
        $responseObject->message = "An error occured";
        return $responseObject;
      }
      $newTodoID = $this->dbconnection->lastInsertId();
      $sql ="SELECT * FROM todos WHERE todo_id=:newTodoID";
      $sth = $this->dbconnection->prepare($sql);
      $sth->bindParam("newTodoID", $newTodoID);
      $sth->execute();
      $responseObject = $sth->fetch();
      $sth = null;
      return $responseObject;
    }
    private function removeOldTodos($list_id) {
      try {
        $sql = "DELETE FROM todos WHERE todo_id IN
        (SELECT todo_id FROM
          (SELECT todo_id FROM todos
            WHERE list_id = :list_id
            AND is_checked = :is_checked
            order by created_at DESC LIMIT 15, 50
          ) a
        )";
      $sth = $this->dbconnection->prepare($sql);
      $sth->bindParam("list_id", $list_id);
      $sth->bindParam("is_checked", $a = 1);
      $sth->execute();
      } catch (\PDOException $e) {
        $responseObject->message = "An Error occured";
      }
    }
  }
