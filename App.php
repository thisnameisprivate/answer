<?php

class App {
    protected $routes = [];
    protected $responseStatus = '200 OK';
    protected $responseContentType = 'text/html';
    protected $responseBody = 'Hello World';
    public function addRoute ($path, $callback) {
        $this->routes[$path] = $callback->bindTo($this, __CLASS__);
    }
    public function disptach ($path) {
        foreach ($this->routes as $routePath => $callback) {
            if ($routePath === $path) {
                $callback();
            }
        }
        header('HTTP/1.1 ' . $this->responseStatus);
        header('Content-Type: ' . $this->responseContentType);
        header('Content-Length: ' . mb_string($this->responseBody));
        echo $this->responseBody;
    }
}
$app = new App();
$app->addRoute('/user', function () {
    $this->responseContentType = 'application/json;charset=utf8';
    $this->responseBody = 'Hello, World.';
});
$app->disptach('/user');
$app->addRoute('/user', function () {

});