<?php
namespace App\Core;

class Router
{
    protected static array $routes = [];

    public function get(string $uri, string $controllerAction): void
    {
        self::addRoute('GET', $uri, $controllerAction);
    }
    
    // ... post() メソッドなど ...

    protected static function addRoute(string $method, string $uri, string $controllerAction): void
    {
        // Controller@method を [ControllerName, methodName] に分割
        list($controller, $action) = explode('@', $controllerAction);
        self::$routes[$method][$uri] = [$controller, $action]; 
    }

    /**
     * URIから対応するコントローラー、メソッド、および引数を検索する
     * @param string $uri 現在のリクエストURI
     * @return array|null [Controller名, Method名, [引数値]] または null
     */
    public static function findRoute(string $uri): ?array
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $path = strtok($uri, '?');
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/'; 
        }

        if (!isset(self::$routes[$method])) {
            return null;
        }

        $definedRoutes = self::$routes[$method];

        foreach ($definedRoutes as $routeUri => $routeInfo) {
            
            // 1. 完全一致チェック
            if ($routeUri === $path) {
                
                $args = [];
                if ($path === '/') {
                    // トップページ ('/') のデフォルトサイトIDを 'okashi' に設定
                    $args = ['okashi']; 
                }
                
                return [$routeInfo[0], $routeInfo[1], $args];
            }
            
            // 2. 可変セグメントチェック
            if (strpos($routeUri, '{') !== false) {
                
                $routeSegments = explode('/', trim($routeUri, '/'));
                $pathSegments = explode('/', trim($path, '/'));
                
                if (count($routeSegments) !== count($pathSegments)) {
                    continue;
                }

                $args = [];
                $match = true;

                for ($i = 0; $i < count($routeSegments); $i++) {
                    $routeSegment = $routeSegments[$i];
                    $pathSegment = $pathSegments[$i];

                    // セグメントがプレースホルダーの場合
                    if (preg_match('/^\{([a-zA-Z0-9]+)\}$/', $routeSegment, $matches)) {
                        $args[] = $pathSegment; 
                    } 
                    // セグメントが固定文字列で、一致しない場合
                    elseif ($routeSegment !== $pathSegment) {
                        $match = false;
                        break;
                    }
                }

                if ($match) {
                    return [$routeInfo[0], $routeInfo[1], $args];
                }
            }
        }
        
        return null;
    }
}