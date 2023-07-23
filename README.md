# Router
A super-simple, intuitive router created using PHP and Regular Expressions. This router is ideal for APIs, where SEO is not a priority, as well as for simple personal projects to clean up your project folder. It is **not** recommended to use this router on a production website for obvious reasons.

## How it Works
Include the `router.php` file, with `include`, `include_once`, `require`, or simply by pasting the `Router` class in your file. The `.htaccess` file redirects all traffic to the specified routing file. This file is where you will create a new Router and add routes.

When the file is requested by the browser, by navigating anywhere in its direct parent folder (with the current `.htaccess` file), the router will create the new Routes specified by the user, set the variables specified in the path, and **include** the requested file. It is important to understand that the Router *includes* the files. Take this simple file for example:
```html
<!DOCTYPE html>
...
<body>
 <header>
  <nav>
   <a href="/" title="Home">Home Page</a>
   <a href="/about" title="About">About Us</a>
  </nav>
 </header>
 <?php
   include "./lib/router.php";
   $router = new Router();
   $router->newRoute("/post/[id]", "./post_by_id.php", "get");
   $router->route();
 ?>
</body>
</html>
```
With this example, the file `./post_by_id.php` must not contain the html, body, and head tags from a typical HTML document, as those were already defined in the template.

## Adding a Route
In the file that `.htaccess` points to, initialize a new instance of the Router object:
```php
$router = new Router();
```
Next, you will create all the routes that you wish to have. A new route is created as follows:
```php
$router->newRoute("/posts/{page}", "./posts_page.php");
```
This code will create a new route with a "variable" that will be captured by the router. In this case, if a user navigates to `https://yoursite.com/posts/2`, the router will create a new variable, `$page` and set it equal to `2`. It will then **include** the file that you specified to maintain the variable that was defined.

## Variables with the Router
### Required Variables
A *required* variable is denoted in the path as `[varName]`. If a required variable is not defined in the request, the router will **not** include the file specified by the route. For instance: the routing path `"/users/[id]"` will NOT work with the URL `http://yoursite.com/users/`. **Required variables CANNOT follow optional variables in the path**

### Optional Variables
An *optional* variable is defined in the path as `{varName}`. If an optional variable is not defined in the request, the router will define the variable as `null` and still include the file specified by the path. For instance: the routing path `"/posts/{page}"` will work with the URL `https://yoursite.com/posts/` OR `https://yoursite.com/posts/3`.

### Variable Types
This router supports rudimentary variable types. As of now, you can define a variable as an `any` type by **not designating a type at all**, an `int` type by following the variable name with `:int`, or an alphabetical type (only supporting the characters A-Z) by following the variable name with `:a-z`. Because this router works with Regular Expressions, these types are matched to the following RegEx patterns:
```
No Type: [^\/\?#\s]+
Int Only: [0-9]+
Alphabetic: [a-z]+
```
Here are a few examples for routes containing variables with types:
```php
$router = new Router();
$router->newPath("/profile/[handle:a-z]/{tab:a-z}", "./profile.php");
$router->newPath("/posts/{id:int}", "./post.php");
$router->route();
```
