# Rememberly Server REST API

This REST API privdes endpoints to create, modify, retrieve, delete and to share notes and todolists. The project includes classes which require a MySQL Database to work with. The API is accessed with Basic Authentication (to login) and with JWT's to actual use the API. All data for todolists/notes is stored in the MySQL database.

## Warnings

At the moment there are no restrictions for clients to create a user on their own so be careful in production use. This software is not 100% tested and should be used carefully in production.

## Installation

1. Clone the repository
2. use the rememberlyserver_db_setup.sql to import the database table structure into your own mysql database
3. create a .htaccess inside the /public folder and add the following contents:
```
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Some hosts may require you to use the `RewriteBase` directive.
  # Determine the RewriteBase automatically and set it as environment variable.
  # If you are using Apache aliases to do mass virtual hosting or installed the
  # project in a subdirectory, the base path will be prepended to allow proper
  # resolution of the index.php file and to redirect to the correct URI. It will
  # work in environments without path prefix as well, providing a safe, one-size
  # fits all solution. But as you do not need it in this case, you can comment
  # the following 2 lines to eliminate the overhead.
  RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
  RewriteRule ^(.*) - [E=BASE:%1]

  # If the above doesn't work you might need to set the `RewriteBase` directive manually, it should be the
  # absolute physical path to the directory that contains this htaccess file.
  # RewriteBase /

  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ index.php [QSA,L]
  RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  SetEnv REM_TOKEN_ENV your password for token generation on the server
  SetEnv REM_DB_HOSTNAME the hostname/ip-address of you mysql database
  SetEnv REM_DB_DBNAME your database name
  SetEnv REM_DB_USER your database username
  SetEnv REM_DB_PASS your database password
</IfModule>
```
4. Install all other required dependencies using composer:
```composer install```
5. Point your webserver/domain (only tested with apache/nginx yet) to the /public directory in the root directory of the application.
6. Open up your domain and you should see a welcome page.

## Usage
The API offers basic endpoints for user services and to access stored data (notes/todolists). Take a look inside the /src/routes.php file for a complete overview of possible endpoints. Most of the endpoints require data from the client. This can be JSON in the HTML body or URL query parameters.
Some examples:
- Login route (GET: /login):
This endpoint requires login via username/password (HTTP Basic Authentication) and returns an API token for the specified user to access the API with the users permissions.
- Creating users (POST: /user/create): Requires 2 values. "user" and "password". Both Values have to be valid JSON Strings. Example:
```
{
    "user" : "testuser",
    "password" : "password"
} 
```
This endpoint also returns statusmessage + statuscode as JSON and normal HTTP statuscodes to handle errors.
#
### All following endpoints need an API token to be set via HTTP header:
- **Header name: Authorization**
- **Header Value: Bearer** `your token here`
#

- Creating new todolists (POST: /api/todolist/new): Takes a listname as a value and creates a new database entry. Example:
```
{
    "list_name" : "testlist"
}
```
- Adding todo's (POST: /api/todo/new): Takes a todolist-ID (integer), expiration (datetime) and a text (string):
```
{
    "list_id" : 1,
    "expires_on" : "2019-01-13 18:37:42",
    "todo_text" : "things I need to get done"
}
```
**The user needs permissions to change entries or to add todo's to a todolist. If a user creates a todolist he is the only one who can add todo's to that list. If he wants to share it you need the following:**
- Sharing todolists (POST: /api/todolist/share): Shares a todolist with another user. You need to have permissions on the list that you want to share. In the next example the requesting user owns todolist with ID 1 and wants to share it with user 'freddy':
```
{
    "list_id" : "1",
    "username" : "freddy"
}
```
Freddy now gains access permissions for that list.
- Get todo's of a specific todolist (GET: /api/todos/{list_id}: This endpoint needs a query parameter. It requires the todolist ID of the list you want to get the todo's of. Example: /api/todos/1.
