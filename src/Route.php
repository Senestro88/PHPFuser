<?php

namespace PHPFuser;

use PHPFuser\Instance\RouteResourceRegistrar;
use PHPFuser\Instance\RouteDefinition;
use \Bramus\Router\Router;
use InvalidArgumentException;

/**
 * Class Route
 *
 * A Laravel-like routing facade built on top of bramus/router.
 * ------------------------------------------------------------------
 * Provides static methods for defining routes in a clean syntax:
 *   - Route::get('/path', callable|[Controller::class, 'method']);
 *   - Route::post('/path', ...);
 *   - Route::put('/path', ...);
 *   - Route::delete('/path', ...);
 *
 * Also supports:
 *   - Route::group('/prefix', fn() => { ... });   // route groups
 *   - Route::middleware(fn() => { ... }, fn() => { ... });  // scoped middleware
 *
 * Controller-style routes:
 *   Route::get('/users', [UserController::class, 'index']);
 *   Route::get('/users/{id}', [UserController::class, 'show']);
 *
 * Must be finalized with:
 *   Route::run();
 *
 * Example:
 * ------------------------------------------------------------------
 * use PHPFuser\Instance\Route;
 * use App\Controllers\UserController;
 *
 * Route::get('/', fn() => echo "Home");
 *
 * Route::group('/api', function() {
 *     Route::get('/users', [UserController::class, 'index']);
 *     Route::get('/users/{id}', [UserController::class, 'show']);
 * });
 *
 * Route::middleware(function() {
 *     if (!isset($_GET['token'])) {
 *         header('HTTP/1.1 403 Forbidden');
 *         exit('Unauthorized');
 *     }
 * }, function() {
 *     Route::get('/profile', fn() => echo "Protected Profile");
 * });
 *
 * Route::run();
 */
class Route {
    /**
     * The underlying router instance.
     * This is shared across all static calls.
     *
     * @var Router|null
     */
    protected static ?Router $router = null;

    /**
     * Store named routes.
     * e.g. ['admin.home' => '/admin/home']
     */
    protected static array $namedRoutes = [];

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Lazily initialize and return the router instance.
     * Ensures only one Router object exists at runtime.
     *
     * @return Router
     */
    protected static function getRouter(): Router {
        if (!self::$router) {
            self::$router = new Router();
        }
        return self::$router;
    }

    /**
     * Normalize URI by replacing dot-notation with slashes.
     *
     * Example:
     *   "admin.home" → "/admin/home"
     *   "user.profile.edit" → "/user/profile/edit"
     *
     * @param string $uri
     * @return string
     */
    protected static function normalize(string $uri): string {
        // Replace dots with slashes
        $uri = str_replace('.', '/', $uri);
        // Ensure it starts with a leading slash
        return '/' . ltrim($uri, '/');
    }

    /**
     * Resolve an action or middleware into a callable.
     *
     * This utility converts different action formats into a standard callable
     * that can later be executed by the router. It supports:
     *
     *   - Direct callables (Closures, anonymous functions, global functions)
     *   - Controller-style arrays: [ControllerClass::class, 'method']
     *
     * Why this is needed:
     *   Routes and middlewares may be declared in different formats, but at
     *   runtime, the router always needs a plain callable. This method ensures
     *   everything is normalized.
     *
     * @param callable|array $am         The action or middleware definition
     * @param bool           $middleware Flag to improve error messages:
     *                                   true → "Middleware", false → "Controller"
     *
     * @return callable A fully resolved, ready-to-execute callable
     *
     * @throws InvalidArgumentException If the action is not resolvable
     */
    protected static function amHandler(callable|array $am, bool $middleware = false): callable {
        //  If it's already a Closure/callable, return it directly.
        if (is_callable($am)) {
            return $am;
        }
        // Label for error messages (helps differentiate context)
        $which = $middleware ? "Middleware" : "Controller";
        // Handle array syntax: [ClassName::class, 'method']
        if (is_array($am) && count($am) === 2) {
            [$class, $method] = $am;
            // Ensure the class actually exists
            if (!class_exists($class)) {
                throw new InvalidArgumentException("$which class '{$class}' not found.");
            }
            // Ensure the method exists in that class
            if (!method_exists($class, $method)) {
                throw new InvalidArgumentException("$which method '{$method}' not found in {$class}.");
            }
            // Return a closure that:
            // 1. Instantiates the controller/middleware class
            // 2. Calls the method with any parameters passed at runtime
            return fn(...$params) => (new $class())->{$method}(...$params);
        }
        // If none of the above matched, throw a descriptive exception
        throw new InvalidArgumentException("Action must be a Closure or [{$which}::class, method]");
    }


    /**
     * Register a GET route.
     *
     * @param string              $uri    URI pattern (e.g. "/users/{id}" or "users.id.{id}" where dots are translated to /)
     * @param callable|array      $controller Closure or [Controller::class, 'method']
     */
    public static function get(string $uri, callable|array $controller): RouteDefinition {
        $uri = self::normalize($uri);
        self::getRouter()->get($uri, self::amHandler($controller));
        return new RouteDefinition($uri);
    }

    /**
     * Register a POST route.
     *
     * @param string              $uri    URI pattern (e.g. "/users/{id}" or "users.id.{id}" where dots are translated to /)
     * @param callable|array      $controller Closure or [Controller::class, 'method']
     */
    public static function post(string $uri, callable|array $controller): RouteDefinition {
        $uri = self::normalize($uri);
        self::getRouter()->post($uri, self::amHandler($controller));
        return new RouteDefinition($uri);
    }

    /**
     * Register a PUT route.
     *
     * @param string              $uri    URI pattern (e.g. "/users/{id}" or "users.id.{id}" where dots are translated to /)
     * @param callable|array      $controller Closure or [Controller::class, 'method']
     */
    public static function put(string $uri, callable|array $controller): RouteDefinition {
        $uri = self::normalize($uri);
        self::getRouter()->put($uri, self::amHandler($controller));
        return new RouteDefinition($uri);
    }

    /**
     * Register a DELETE route.
     *
     * @param string              $uri    URI pattern (e.g. "/users/{id}" or "users.id.{id}" where dots are translated to /)
     * @param callable|array      $controller Closure or [Controller::class, 'method']
     */
    public static function delete(string $uri, callable|array $controller): RouteDefinition {
        $uri = self::normalize($uri);
        self::getRouter()->delete($uri, self::amHandler($controller));
        return new RouteDefinition($uri);
    }

    /**
     * Group multiple routes under a common URI prefix.
     *
     * Example:
     * Route::group('/admin', function() {
     *     Route::get('/dashboard', ...);
     *     Route::get('/settings', ...);
     * });
     *
     * Creates routes:
     *   /admin/dashboard
     *   /admin/settings
     *
     * @param string              $prefix    URI pattern (e.g. "/users/friends" or "users.friends" where dots are translated to /)
     * @param callable      $callback Closure
     */
    public static function group(string $prefix, callable $callback): void {
        self::getRouter()->mount(self::normalize($prefix), function () use ($callback) {
            $callback();
        });
    }

    /**
     * Define middleware logic to wrap around a set of routes.
     *
     * Example:
     * Route::middleware(function() {
     *     if (!isset($_GET['auth'])) exit("Unauthorized");
     * }, function() {
     *     Route::get('/protected', ...);
     * });
     *
     * The first callable is executed before any routes in the callback.
     *
     * @param callable|array $middleware Closure or [Controller::class, 'method'] run before routes in the group
     * @param callable $callback   Routes inside this middleware scope
     */
    public static function middleware(callable|array $middleware, callable $callback): void {
        // Attach middleware for all verbs within this group
        self::getRouter()->before('GET|POST|PUT|DELETE|OPTIONS', '/.*', self::amHandler($middleware));
        // Then register routes
        $callback();
    }

    /**
     * Register a RESTful resource controller.
     *
     * This method creates a new instance of `ResourceRegistrar`,
     * which auto-generates conventional CRUD routes for a given resource
     * (similar to Laravel's `Route::resource`).
     *
     * By default, the following 7 routes are registered:
     *
     *   GET    /resource             → index()    // List all items
     *   GET    /resource/create      → create()   // Show form to create an item
     *   POST   /resource             → store()    // Save new item
     *   GET    /resource/{id}        → show($id)  // Display a single item
     *   GET    /resource/{id}/edit   → edit($id)  // Show form to edit an item
     *   PUT    /resource/{id}        → update($id)// Update an existing item
     *   DELETE /resource/{id}        → destroy($id)// Delete an item
     *
     * You can refine which routes are generated:
     *
     *   Route::resource('users', UserController::class);
     *       → Registers all 7 resource routes
     *
     *   Route::resource('posts', PostController::class)->only(['index', 'show']);
     *       → Registers only index() and show() routes
     *
     *   Route::resource('comments', CommentController::class)->except(['destroy']);
     *       → Registers all except destroy()
     *
     * By default, the `RouteResourceRegistrar` auto-registers routes when destroyed,
     * so you don’t need to manually call `->register()`.  
     * However, you may explicitly call it if you prefer:
     *
     *   Route::resource('products', ProductController::class)
     *       ->only(['index', 'show'])
     *       ->register(); // Explicit registration
     *
     * @param string $name   The resource name (plural form, e.g. "users", "posts")
     *                       Used to prefix route URIs: "/users", "/posts"
     * @param string $class  The controller class name that defines CRUD methods
     *
     * @return RouteResourceRegistrar  A fluent builder for refining registered routes
     */
    public static function resource(string $name, string $class): RouteResourceRegistrar {
        // Normalize "admin.users" → "admin/users"
        $name = str_replace('.', '/', $name);
        return new RouteResourceRegistrar($name, $class);
    }

    /**
     * Register a named route.
     *
     * This method stores a "name → URI" mapping inside the internal
     * `$namedRoutes` array. Named routes let you refer to routes
     * symbolically (by name) instead of hardcoding their URI.
     *
     * For example:
     *   Route::get('admin.home', fn() => '...')->name('admin.home');
     *
     * Internally, this will call:
     *   Route::registerName('admin.home', '/admin/home');
     *
     * Which means later you can generate the URL by doing:
     *   Route::url('admin.home');  // → "/admin/home"
     *
     * @param string $name  The unique name for this route (dot-notation recommended, e.g. "users.show")
     * @param string $uri   The resolved URI path for this route (e.g. "/users/{id}")
     */
    public static function registerName(string $name, string $uri): void {
        self::$namedRoutes[$name] = $uri;
    }

    /**
     * Generate a URL from a named route.
     *
     * Looks up the URI associated with the given route name,
     * then substitutes any placeholder parameters.
     *
     * Placeholders in the route are wrapped in curly braces `{}`:
     *   e.g. "/users/{id}/posts/{postId}"
     *
     * When calling this method, you can pass an array of values
     * to replace those placeholders:
     *
     * Example:
     *   Route::url('users.show', ['id' => 42]);
     *     → "/users/42"
     *
     *   Route::url('posts.show', ['id' => 42, 'postId' => 7]);
     *     → "/users/42/posts/7"
     *
     * @param string $name   The route name to generate a URL for
     * @param array  $params Key-value pairs used to replace URI placeholders
     *
     * @return string The fully resolved URL path
     *
     * @throws \Exception If the route name does not exist
     */
    public static function url(string $name, array $params = []): string {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route name '{$name}' not defined.");
        }
        $url = self::$namedRoutes[$name];
        // Replace placeholders (e.g. {id}) with actual parameter values
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        return $url;
    }


    /**
     * Run the router to process the current request.
     *
     * Must be called at the end of your routes definition file.
     */
    public static function run(): void {
        self::getRouter()->run();
    }

    /**
     * Retrieve the contents of the route-specific `.htaccess` file.
     *
     * This method is responsible for locating and reading the `.htaccess`
     * configuration that applies specifically to the routing system.
     *
     * @return string The full contents of the `route/htaccess` file
     */
    public static function getRouteHtaccessData(): string {
        // Normalize and ensure correct directory separators
        $routeDirname = Path::insert_dir_separator(Path::arrange_dir_separators(PHPFUSER['DIRECTORIES']['DATA'] . DIRECTORY_SEPARATOR . "route"));
        // Return the contents of "route/htaccess"
        return File::getFileContent($routeDirname . "htaccess");
    }
}
