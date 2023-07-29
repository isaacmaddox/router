<?php
class Router
{
    public array $routes = [];
    private ?string $error = null;

    /**
     * Define a path to include if no route is found.
     * @param string $path Path to the error file.
     * @return bool
     */
    public function setErrorPage(string $path): bool
    {
        if (!is_file($path) && !is_dir($path))
            return false;
        $this->error = $path;
        return true;
    }

    /**
     * Create a new routing path.
     *
     * Create a new path to route from a request URI and convert values into variables.
     *
     * @param string $uri The request URI that users will type.
     * @param string $path The path which the request will include. Path must be a valid file or directory, and no required parameters should be placed after optional ones.
     * @param ?string $method Define a specific method with which the route can be called. If the method does not match the request method, the file will not be loaded.
     * @return bool
     **/
    public function newRoute(string $uri, string $path, ?string $method = "any"): bool
    {
        if ((!is_file($path) && !is_dir($path)) || preg_match('/{.*\[/', $uri))
            return false;

        preg_match_all('/[\[{]([a-z]+)(?:\:int|\:a-z)?[\]}]/', $uri, $names);
        array_shift($names);
        $names = $names[0];

        $uriReg = preg_replace('/\/\[[a-z]+(\:int|\:a-z)?\]/', '(?:\/($_$1))', $uri);
        $uriReg = preg_replace('/\/{[a-z]+(\:int|\:a-z)?}/', '(?:\/($_$1))?', $uriReg);
        $uriReg = preg_replace('/\$_(?!\:int|\:a-z)/', '[^\/\?#\s]+', $uriReg);
        $uriReg = preg_replace('/\$_\:int/', '[0-9]+', $uriReg);
        $uriReg = preg_replace('/\$_\:a-z/', '[a-z]+', $uriReg);
        $uriReg = preg_replace('/(?<!\\\)\//', '\/', $uriReg);
        $uriReg = "/$uriReg/i";

        $newRoute = [
            "method" => $method,
            "uri" => $uri,
            "reg" => $uriReg,
            "names" => $names,
            "path" => $path,
        ];

        array_push($this->routes, $newRoute);

        return true;
    }

    /**
     * Find the route, if any, which matches the URI and include that file with the variables from the URI.
     * @return bool
     */
    public function route(): bool
    {
        $uri = $_SERVER["REQUEST_URI"];

        foreach ($this->routes as $route) {
            if (
                !preg_match($route["reg"], $uri, $matches)
                || ($route["method"] !== "any" && $route["method"] !== strtolower($_SERVER["REQUEST_METHOD"]))
            )
                continue;

            array_shift($matches);
            $arr = Router::match_values($route["names"], $matches);

            foreach ($arr as $varName => $varVal) {
                ${$varName} = $varVal;
            }
            include_once($route["path"]);
            return true;
        }

        if ($this->error)
            include_once($this->error);
        return false;
    }

    private static function match_values(array $names, array $values): array|bool
    {
        if (count($values) > count($names))
            return false;
        $values = array_pad($values, count($names), null);
        return array_combine($names, $values);
    }
}
?>
