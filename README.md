## Usage

Create an index.php file with the following contents:

```php
require __DIR__ . '/../Routing/Router.php';

use Routing\Router;


$route = new Router();

$route->get('/', function(){
    echo "Hello!";
});

$route->get('user/{username}', function($username){
    echo "Hello, $username!";
});

$route->post('user', function(){
    echo "there must be create user";
});

$route->put('user/{id}', function(int $id){
    echo "there must be update user #$id";
});

$route->delete('user/{id}', function(int $id){
    echo "there must be delete user #$id";
});

// work with all methods
$route->any('somepage/{title}/{id}', function($title, $id) {
    echo "title field: $title, id: #$id";
});

$route->run();
```