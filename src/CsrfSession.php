<?php

namespace PHPFuser;

use \PHPFuser\Utils;
use \PHPFuser\Aes;
use \PHPFuser\Exception\Session;

/**
 * @author Senestro
 */
class CsrfSession {
    // Private constants
    private static $name = "X-SESS-CSRF-TOKEN";
    private static $csrfKey = "0914843909148439";
    private static $errorMessage = "";

    // Prevent the constructor from being initialized
    private function __construct() {
    }

    /**
     * Get the CSRF token name
     * @return string
     */
    public static function getName(): string {
        return self::$name;
    }

    /**
     * Get the last session error message
     * @return string The session last error message
     */
    public static function getErrorMessage(): string {
        return self::$errorMessage;
    }

    /**
     * Set the CSRF token in the session
     * @param mixed $expires The time to mark the token as expired  (In minutes)
     */
    public static function setToken(int $expires = 1440): void {
        if (self::validSessionID()) {
            $generatedToken = self::generateToken($expires);
            if (!empty($generatedToken)) {
                // Set the token in the session
                $_SESSION[self::$name] = bin2hex($generatedToken);
                if (!isset($_SESSION[self::$name])) {
                    self::$errorMessage = "Failed to set the token session.";
                }
            } else {
                self::$errorMessage = "Failed to generated session token.";
            }
        } else {
            self::$errorMessage = "Failed to set session token, session is not started.";
        }
    }

    /**
     * Get the CSRF token from the session
     * @return string|null
     */
    public static function getToken(): ?string {
        return self::validSessionID() && isset($_SESSION) && isset($_SESSION[self::$name]) && is_string($_SESSION[self::$name]) && Utils::isNotEmptyString($_SESSION[self::$name]) ? (string) $_SESSION[self::$name] : null;
    }

    /**
     * Validate the CSRF token
     * @return bool
     */
    public static function validateToken(): bool {
        if (self::validSessionID()) {
            $sessionToken = self::getToken();
            if (!self::isNull($sessionToken)) {
                $isValid = Csrf::validateToken(self::$csrfKey, hex2bin($sessionToken));
                if ($isValid) {
                    self::unsetToken();
                } else {
                    self::$errorMessage = "Failed to validated session token, session token is not valid or has expired.";
                }
                return $isValid;
            } else {
                self::$errorMessage = "Failed to validated session token, unable to get the session token.";
            }
        } else {
            self::$errorMessage = "Failed to validate session token, session is not started.";
        }
        return false;
    }

    /**
     * Validate a generated CSRF token
     * @param string $generatedToken
     * @return bool
     */
    public static function isValidToken(string $generatedToken): bool {
        if (!empty($generatedToken)) {
            $isValid = Csrf::validateToken(self::$csrfKey, hex2bin($generatedToken));
            if ($isValid) {
                self::unsetToken();
            } else {
                self::$errorMessage = "Failed to validated token, token is not valid or has expired.";
            }
            return $isValid;
        } else {
            self::$errorMessage = "Failed to validate token, the token provided is empty.";
        }
        return false;
    }

    /**
     * Echo the CSRF token in a form
     */
    public static function echoTokenInForm(): void {
        if (self::validSessionID()) {
            $sessionToken = self::getToken();
            if (!self::isNull($sessionToken)) {
                echo "<input type='hidden' name='" . self::$name . "' id='" . self::$name . "' value='" . $sessionToken . " />";
            } else {
                self::$errorMessage = "Failed to echo token in html form, unable to get the session token.";
            }
        } else {
            self::$errorMessage = "Failed to echo token in html form, the token provided is empty.";
        }
    }

    /**
     * Echo the CSRF token in the HTML head
     */
    public static function echoTokenInHtmlHead(): void {
        if (self::validSessionID()) {
            $sessionToken = self::getToken();
            if (!self::isNull($sessionToken)) {
                echo "<meta name='" . self::$name . "' content='" . $sessionToken . " />";
            } else {
                self::$errorMessage = "Failed to echo token in html head, unable to get the session token.";
            }
        } else {
            self::$errorMessage = "Failed to echo token in html head, the token provided is empty.";
        }
    }

    /**
     * Validate the CSRF token from a POST request
     * @return bool
     */
    public static function validateTokenFromPost(): bool {
        if (getenv("REQUEST_METHOD") === "POST" && isset($_POST[self::$name]) && !empty($_POST[self::$name])) {
            $isValid = Csrf::validateToken(self::$csrfKey, hex2bin((string) $_POST[self::$name]));
            if ($isValid) {
                self::unsetToken();
            } else {
                self::$errorMessage = "Failed to validated token from post rquest, token is not valid or has expired.";
            }
            return $isValid;
        } else {
            self::$errorMessage = "Failed to validated token from post request, the token provided is empty or not a POST request.";
        }
        return false;
    }

    /**
     * Validate the CSRF token from headers
     * @return bool
     */
    public static function validateTokenFromHeaders(): bool {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers[self::$name]) && !empty($headers[self::$name])) {
                $isValid = Csrf::validateToken(self::$csrfKey, hex2bin((string) $headers[self::$name]));
                if ($isValid) {
                    self::unsetToken();
                } else {
                    self::$errorMessage = "Failed to validated token from request header, token is not valid or has expired.";
                }
                return $isValid;
            }
        } else {
            self::$errorMessage = "Failed to validated token from request header, the token provided is empty or not in header request.";
        }
        return false;
    }

    // PRIVATE METHODS

    /**
     * Generate a CSRF token
     * @param mixed $expires The time to mark the token as expired (In minutes)
     * @return string
     */
    private static function generateToken(int $expires = 1440): string {
        return Csrf::generateToken(self::$csrfKey, $expires);
    }

    /**
     * Check if the session ID is valid
     * @return bool
     */
    private static function validSessionID(): bool {
        $sessionId = session_id();
        return is_string($sessionId) && Utils::isNotEmptyString($sessionId);
    }

    /**
     * Unset the CSRF token
     */
    private static function unsetToken(): void {
        if (self::validSessionID()) {
            if (isset($_SESSION[self::$name])) {
                unset($_SESSION[self::$name]);
            }
        }
    }

    /**
     * Check if a value is null
     * @param mixed $arg
     * @return bool
     */
    private static function isNull(mixed $arg): bool {
        return $arg === null;
    }
}
