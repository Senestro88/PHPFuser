<?php

namespace PHPFuser\Instance;

use PHPFuser\Route;

/**
 * Class RouteResourceRegistrar
 *
 * This class is responsible for generating RESTful resource routes,
 * similar to Laravel's `Route::resource()`.
 *
 * ✅ Supports automatic registration of standard 7 CRUD routes:
 *    - index   → GET    /resource
 *    - create  → GET    /resource/create
 *    - store   → POST   /resource
 *    - show    → GET    /resource/{id}
 *    - edit    → GET    /resource/{id}/edit
 *    - update  → PUT    /resource/{id}
 *    - destroy → DELETE /resource/{id}
 *
 * ✅ Supports filtering via:
 *    - only([...])   → Register only specific routes.
 *    - except([...]) → Exclude specific routes.
 *
 * ✅ Auto-registers routes when the object is destroyed, so you don’t
 *    always need to call ->register().
 *
 * Example:
 *    Route::resource('users', UserController::class); // all 7 routes
 *
 *    Route::resource('posts', PostController::class)
 *         ->only(['index','show']); // only 2 routes
 *
 *    Route::resource('comments', CommentController::class)
 *         ->except(['destroy']); // all except delete
 */
class RouteResourceRegistrar {
    /**
     * The base resource name (e.g. "users", "posts").
     *
     * @var string
     */
    protected string $name;

    /**
     * The fully qualified controller class name.
     *
     * @var string
     */
    protected string $controller;

    /**
     * The list of methods to generate routes for.
     *
     * Defaults to all 7 resource actions.
     *
     * @var array<string>
     */
    protected array $methods = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy'
    ];

    /**
     * Tracks if register() has already been called,
     * so routes are not duplicated.
     *
     * @var bool
     */
    protected bool $registered = false;

    /**
     * Create a new resource registrar instance.
     *
     * @param string $name       The base resource URI (e.g. "users").
     * @param string $controller The controller handling requests.
     */
    public function __construct(string $name, string $controller) {
        $this->name = $name;
        $this->controller = $controller;
    }

    /**
     * Restrict the resource routes to only the given actions.
     *
     * Example:
     *   Route::resource('users', UserController::class)->only(['index','show']);
     *
     * @param array<string> $methods
     * @return $this
     */
    public function only(array $methods): self {
        $this->methods = array_intersect($this->methods, $methods);
        return $this;
    }

    /**
     * Exclude the given actions from the resource routes.
     *
     * Example:
     *   Route::resource('users', UserController::class)->except(['destroy']);
     *
     * @param array<string> $methods
     * @return $this
     */
    public function except(array $methods): self {
        $this->methods = array_diff($this->methods, $methods);
        return $this;
    }

    /**
     * Register the resource routes into the Route class.
     *
     * Each CRUD action is checked against the $methods list.
     * If present, the corresponding route is registered.
     *
     * Example generated routes:
     *   GET    /users           → UserController@index
     *   GET    /users/create    → UserController@create
     *   POST   /users           → UserController@store
     *   GET    /users/{id}      → UserController@show
     *   GET    /users/{id}/edit → UserController@edit
     *   PUT    /users/{id}      → UserController@update
     *   DELETE /users/{id}      → UserController@destroy
     *
     * @return void
     */
    public function register(): void {
        if ($this->registered) return; // prevent double-registration
        $this->registered = true;
        // CRUD route mappings
        // GET /resource → list all items
        if (in_array('index', $this->methods)) {
            Route::get("/{$this->name}", [$this->controller, 'index']);
        }
        // GET /resource/create → show form to create new item
        if (in_array('create', $this->methods)) {
            Route::get("/{$this->name}/create", [$this->controller, 'create']);
        }
        // POST /resource → save new item
        if (in_array('store', $this->methods)) {
            Route::post("/{$this->name}", [$this->controller, 'store']);
        }
        // GET /resource/{id} → show a single item
        if (in_array('show', $this->methods)) {
            Route::get("/{$this->name}/{id}", [$this->controller, 'show']);
        }
        // GET /resource/{id}/edit → show edit form for an item
        if (in_array('edit', $this->methods)) {
            Route::get("/{$this->name}/{id}/edit", [$this->controller, 'edit']);
        }
        // PUT /resource/{id} → update an existing item
        if (in_array('update', $this->methods)) {
            Route::put("/{$this->name}/{id}", [$this->controller, 'update']);
        }
        // DELETE /resource/{id} → delete an item
        if (in_array('destroy', $this->methods)) {
            Route::delete("/{$this->name}/{id}", [$this->controller, 'destroy']);
        }
    }

    /**
     * Destructor — automatically registers routes if not already registered.
     *
     * This allows a clean syntax:
     *   Route::resource('users', UserController::class);
     *
     * Without requiring ->register() explicitly.
     */
    public function __destruct() {
        $this->register();
    }
}
