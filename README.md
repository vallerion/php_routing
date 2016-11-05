## Usage

Create an index.php file with the following contents:

```php
require __DIR__ . '/../Routing/Router.php';

use Routing\Router;


$route = new Router();

$route->get('/', function(){
    echo "Hello!";
});

$route->get('user', function(){
    echo "this is user!";
});

$route->get('user/{username}', function($username){
    echo "Hello, $username!";
});

$route->run();
```