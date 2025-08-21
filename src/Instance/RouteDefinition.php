<?php

namespace PHPFuser\Instance;

use PHPFuser\Route;

/**
 * RouteDefinition
 *
 * Represents a single route instance during its definition phase.
 * 
 * This class exists primarily to support "fluent" route APIs,
 * such as assigning names to routes immediately after declaring them.
 *
 * Example usage:
 *   Route::get('admin.home', fn() => '...')->name('admin.home');
 *
 * Internally:
 *   - The route is created with a normalized URI (e.g. "/admin/home")
 *   - A RouteDefinition object is returned
 *   - Calling ->name('admin.home') will register that name with the URI
 */
class RouteDefinition {
    /**
     * The resolved/normalized URI for this route.
     * Example: "/admin/home"
     */
    protected string $uri;

    /**
     * Create a new RouteDefinition instance.
     *
     * @param string $uri The normalized URI for the route
     */
    public function __construct(string $uri) {
        $this->uri = $uri;
    }

    /**
     * Assign a name to the route (dot-notation supported).
     *
     * This allows the route to be referenced later by its symbolic name,
     * rather than hardcoding the URI. Dot-notation is recommended for
     * organization, e.g. "admin.dashboard" or "users.show".
     *
     * Example:
     *   Route::get('users.show', fn($id) => ...)->name('users.show');
     *
     * Later you can generate its URL:
     *   Route::url('users.show', ['id' => 42]); // → "/users/42"
     *
     * @param string $name The symbolic name of the route
     */
    public function name(string $name): void {
        Route::registerName($name, $this->uri);
    }
}
