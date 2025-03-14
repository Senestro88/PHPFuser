<?php

namespace PHPFuser\Instance;

use \PHPFuser\Utils;

/**
 * @author Senestro
 */
class Session {
    // PRIVATE VARIABLES

    /**
     * @var string Same-site policy for the session cookie
     */
    private string $sameSite = "Lax";

    /**
     * @var bool HTTP-only flag for the session cookie
     */
    private bool $httpOnly = true;

    /**
     * @var string Path for the session cookie
     */
    private string $path = "/";

    /**
     * @var string Domain for the session cookie
     */
    private string $domain;

    // PUBLIC VARIABLES

    /**
     * @var int Maximum days for the session to expire and reset
     */
    public int $maxDays = 7;

    // PUBLIC METHODS

    /**
     * Construct new Session instance
     * @param int $maxDays The maximum days for the session to expire and reset
     */
    public function __construct(int $maxDays = 7) {
        $this->maxDays = $maxDays;
        $this->domain = getenv('HTTP_HOST'); // Set domain from environment variable
    }

    /**
     * Get session domain
     * @return string
     */
    public function getDomain(): string {
        return $this->domain;
    }

    /**
     * Set session domain
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void {
        $this->domain = $domain;
    }

    /**
     * Set session path
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void {
        $this->path = $path;
    }

    /**
     * Get session path
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Determine if session is in secured context
     * @return bool
     */
    public function inSecuredContext(): bool {
        return (getenv('HTTPS') == 'on' || getenv('HTTPS') == '1' || getenv('SERVER_PORT') == '443');
    }

    /**
     * Set HTTP-only flag
     * @param bool $httpOnly
     * @return void
     */
    public function setHttpOnly(bool $httpOnly): void {
        $this->httpOnly = $httpOnly;
    }

    /**
     * Get HTTP-only flag
     * @return bool
     */
    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }

    /**
     * Set same-site policy
     * @param string $sameSite
     * @return void
     */
    public function setSameSite(string $sameSite): void {
        $this->sameSite = $sameSite;
    }

    /**
     * Get same-site policy
     * @return string
     */
    public function getSameSite(): string {
        return $this->sameSite;
    }

    /**
     * Start a session
     * @return bool
     */
    public function startSession(): bool {
        $status = @session_status();
        switch ($status) {
            case PHP_SESSION_DISABLED:
                throw new \Exception("Sessions are disabled");
                break;
            case PHP_SESSION_NONE:
                if ($this->setParameters()) {
                    return $this->start();
                }
                break;
            case PHP_SESSION_ACTIVE:
                if (!$this->isValid() && $this->stopSession() && $this->setParameters()) {
                    return $this->start();
                }
                return $this->start();
        }
        return $this->start();
    }

    /**
     * Stop a session
     * @return bool
     */
    public function stopSession(): bool {
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }
        }
        return session_destroy();
    }

    /**
     * Creates a new session variable
     *
     * @param string $name  The name of the session variable
     * @param string $value The value of the session variable
     * @return bool Whether the session variable was successfully created
     */
    public function createSession(string $name, string $value): bool {
        // Check if the session is already started
        if (isset($_SESSION)) {
            // Set the session variable
            $_SESSION[$name] = $value;
        }
        // Return true if the session variable was successfully created
        return isset($_SESSION) && isset($_SESSION[$name]);
    }

    /**
     * Deletes a session variable
     *
     * @param string $name The name of the session variable to delete
     * @return bool Whether the session variable was successfully deleted
     */
    public function deleteSession(string $name): bool {
        // Check if the session is already started and the variable exists
        if (isset($_SESSION) && isset($_SESSION[$name])) {
            // Unset the session variable
            unset($_SESSION[$name]);
        }
        // Return true if the session variable was successfully deleted
        return !isset($_SESSION) || !isset($_SESSION[$name]);
    }

    /**
     * Creates a new cookie
     *
     * @param string $name  The name of the cookie
     * @param string $value The value of the cookie
     * @param int $days The number of days until the cookie expires
     * @return bool Whether the cookie was successfully created
     */
    public function createCookie(string $name, string $value, int $days): bool {
        // Calculate the expiration time
        $expires = strtotime('+' . $days . ' days');
        // Set the cookie with the calculated expiration time
        return @setcookie($name, $value, array(
            'expires' => $expires,
            'path' => $this->getPath(),
            'domain' => $this->getDomain(),
            'secure' => $this->inSecuredContext(),
            'httponly' => $this->isHttpOnly(),
            'samesite' => ucfirst($this->getSameSite())
        ));
    }

    /**
     * Deletes a cookie
     *
     * @param string $name The name of the cookie to delete
     * @return bool Whether the cookie was successfully deleted
     */
    public function deleteCookie(string $name): bool {
        // Check if the cookie exists
        if (isset($_COOKIE) && isset($_COOKIE[$name])) {
            // Set the cookie to expire in the past
            $expires = strtotime('2010');
            // Set the cookie with the expired time
            $setcookie = @setcookie($name, "", array(
                'expires' => $expires,
                'path' => $this->getPath(),
                'domain' => $this->getDomain(),
                'secure' => $this->inSecuredContext(),
                'httponly' => $this->isHttpOnly(),
                'samesite' => ucfirst($this->getSameSite())
            ));
            // Check if the cookie was successfully deleted
            if (Utils::isTrue($setcookie)) {
                try {
                    // Unset the cookie
                    unset($_COOKIE[$name]);
                } catch (\Throwable $e) {
                    // Ignore any exceptions
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the value of a session variable
     *
     * @param string $name The name of the session variable
     * @return string The value of the session variable, or an empty string if it doesn't exist
     */
    public function getSessionValue(string $name): string {
        // Check if the session variable exists
        if (isset($_SESSION) && isset($_SESSION[$name])) {
            // Return the value of the session variable
            return (string) $_SESSION[$name];
        }
        // Return an empty string if the session variable doesn't exist
        return "";
    }

    /**
     * Gets the value of a cookie
     *
     * @param string $name The name of the cookie
     * @return string The value of the cookie, or an empty string if it doesn't exist
     */
    public function getCookieValue(string $name): string {
        // Check if the cookie exists
        if (isset($_COOKIE) && isset($_COOKIE[$name])) {
            // Return the value of the cookie
            return (string) $_COOKIE[$name];
        }
        // Return an empty string if the cookie doesn't exist
        return "";
    }

    // PRIVATE METHODS

    /**
     * Check if session is valid
     * @return bool
     */
    private function isValid(): bool {
        $params = session_get_cookie_params();
        return (
            (int) $params['lifetime'] == 0 &&
            (string) $params['path'] == $this->getPath() &&
            (string) $params['domain'] == $this->getDomain() &&
            (bool) $params['secure'] == $this->inSecuredContext() &&
            (bool) $params['httponly'] == $this->isHttpOnly() &&
            (string) $params['samesite'] == $this->getSameSite()
        );
    }

    /**
     * Set session expiration time
     * @return void
     */
    private function setExpirationTime(): void {
        if (isset($_SESSION)) {
            $expirationDays = (int) $this->maxDays;
            $_SESSION['session-expires'] = strtotime('+ ' . $expirationDays . ' ' . ($expirationDays > 1 ? "days" : "day"));
        }
    }

    /**
     * Revalidate if session has expired
     * @return bool
     */
    private function revalidateElapsedTime(): bool {
        if (isset($_SESSION) && isset($_SESSION['session-expires']) && !empty($_SESSION['session-expires'])) {
            $sessionExpires = (int) $_SESSION['session-expires'];
            if ($sessionExpires <= time()) {
                session_gc();
                $this->stopSession();
                $newId = session_create_id(substr(md5(time()), 0, 10));
                if (\is_string($newId) && session_commit() === true && \is_string(session_id($newId))) {
                    if (Utils::isTrue(@session_start())) {
                        $this->setExpirationTime();
                        return true;
                    }
                }
            }
        } else {
            $this->setExpirationTime();
        }
        return false;
    }

    /**
     * Start the session
     * @return bool
     */
    private function start(): bool {
        if (Utils::isTrue(@session_start())) {
            $this->revalidateElapsedTime();
            return true;
        }
        return false;
    }

    /**
     * Set session parameters
     * @return bool
     */
    private function setParameters(): bool {
        $lifetime = 0;
        $path = $this->getPath();
        $domain = $this->getDomain();
        $secure = $this->inSecuredContext();
        $httpOnly = $this->isHttpOnly();
        $sameSite = $this->getSameSite();
        if (PHP_VERSION_ID < 70300) {
            return session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        } else {
            return session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'samesite' => $sameSite,
            ]);
        }
    }
}
