<?php

namespace PHPFuser\Instance;

use PHPFuser\Utils;

/**
 * Handles Google reCAPTCHA verification.
 * 
 * This class provides an abstraction over the `\ReCaptcha\ReCaptcha` library
 * to validate reCAPTCHA responses, enforce security checks, and retrieve 
 * error codes if validation fails.
 * 
 * @author Senestro
 */
class Recaptcha {
    /**
     * @var string The secret key used for verifying reCAPTCHA responses.
     */
    private string $secret;

    /**
     * @var \ReCaptcha\ReCaptcha The reCAPTCHA instance used for verification.
     */
    private \ReCaptcha\ReCaptcha $recaptcha;

    /**
     * @var \ReCaptcha\Response|null Holds the last reCAPTCHA verification response.
     */
    private ?\ReCaptcha\Response $recaptchaResponse = null;

    /**
     * Constructor initializes the reCAPTCHA object with the given secret key.
     * 
     * @param string $secret The secret key provided by Google reCAPTCHA.
     */
    public function __construct(string $secret) {
        $this->secret = $secret;
        $this->recaptcha = new \ReCaptcha\ReCaptcha($this->secret);
    }

    /**
     * Sets the expected hostname for reCAPTCHA validation.
     * 
     * This is useful for security, ensuring the token is being used from
     * the correct domain.
     * 
     * @param string $hostname The expected hostname (e.g., example.com).
     */
    public function setExpectedHostname(string $hostname): void {
        $this->recaptcha->setExpectedHostname($hostname);
    }

    /**
     * Sets the expected action for reCAPTCHA v3 validation.
     * 
     * Google recommends setting an expected action to improve security.
     * 
     * @param string $action The expected action (e.g., "login", "register").
     */
    public function setExpectedAction(string $action): void {
        $this->recaptcha->setExpectedAction($action);
    }

    /**
     * Sets the expected APK package name for mobile app verification.
     * 
     * This is applicable when using reCAPTCHA in Android applications.
     * 
     * @param string $apkPackageName The expected Android package name.
     */
    public function setExpectedApkPackageName(string $apkPackageName): void {
        $this->recaptcha->setExpectedApkPackageName($apkPackageName);
    }

    /**
     * Sets the minimum score threshold for reCAPTCHA v3 validation.
     * 
     * reCAPTCHA v3 assigns a score between 0.0 and 1.0, where higher scores 
     * indicate a higher likelihood of human interaction. This method allows 
     * adjusting the minimum required score for a request to be considered valid.
     * 
     * @param float $scoreThreshold The minimum required score (default is usually 0.5 to 0.7).
     */
    public function setScoreThreshold(float $scoreThreshold): void {
        $this->recaptcha->setScoreThreshold($scoreThreshold);
    }


    /**
     * Verifies the reCAPTCHA response.
     * 
     * This method validates the given reCAPTCHA response against Google's
     * verification API. It also allows setting a custom score threshold
     * for reCAPTCHA v3.
     * 
     * @param string $response The reCAPTCHA response token from the frontend.
     * @param string|null $remoteIP (Optional) The user's IP address for additional security checks.
     * @return bool True if the reCAPTCHA validation is successful, false otherwise.
     */
    public function verify(string $response, ?string $remoteIP = null): bool {
        $this->recaptchaResponse = $this->recaptcha->verify($response, $remoteIP);
        return $this->recaptchaResponse->isSuccess();
    }

    /**
     * Retrieves the last reCAPTCHA response object or an empty array if no verification was done.
     * 
     * @return \ReCaptcha\Response|array The reCAPTCHA response object or an empty array.
     */
    public function getResponse(): \ReCaptcha\Response | array {
        return  \is_null($this->recaptchaResponse) ? [] : $this->recaptchaResponse;
    }

    /**
     * Retrieves error codes from the last reCAPTCHA validation attempt.
     * 
     * If the last validation attempt failed, this method returns an array of
     * error codes provided by Google, explaining the failure reason.
     * 
     * @return array List of error codes (empty array if no errors or no validation performed).
     */
    public function getErrorCodes(): array {
        return  \is_null($this->recaptchaResponse) ? [] : $this->recaptchaResponse->getErrorCodes();
    }
}
