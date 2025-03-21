<?php

namespace PHPFuser\Instance;

use PHPFuser\Utils;

/**
 * Handles Google reCAPTCHA verification.
 * 
 * This class wraps around the `\ReCaptcha\ReCaptcha` library to validate 
 * reCAPTCHA responses and retrieve error codes if validation fails.
 * 
 * @author Senestro
 */
class Recaptcha {
    /**
     * The user's reCAPTCHA response token.
     * 
     * This token is provided by the client and is required for verification.
     * It may be null if not set during initialization.
     * 
     * @var string|null
     */
    private ?string $response = null;

    /**
     * The user's remote IP address.
     * 
     * This IP address is used for additional security checks during reCAPTCHA verification.
     * It may be null if not set during initialization.
     * 
     * @var string|null
     */
    private ?string $remoteIP = null;


    /**
     * The reCAPTCHA instance for verification.
     * 
     * @var \ReCaptcha\ReCaptcha
     */
    private \ReCaptcha\ReCaptcha $recaptcha;

    /**
     * The reCAPTCHA verification response.
     * 
     * This is null until `validate()` is called.
     * 
     * @var \ReCaptcha\Response|null
     */
    private ?\ReCaptcha\Response $rresponse = null;

    /**
     * Constructs a new Recaptcha instance.
     * 
     * @param string|null $response  The reCAPTCHA response token from the user.
     * @param string $remoteIP       The user's IP address.
     */
    public function __construct(?string $response, ?string $remoteIP) {
        $this->response = $response;
        $this->remoteIP = $remoteIP;
        $this->recaptcha = new \ReCaptcha\ReCaptcha($this->response);
    }

    /**
     * Sets the expected hostname for the reCAPTCHA verification.
     * 
     * @param string $hostname The expected hostname.
     */
    public function setExpectedHostname(string $hostname): void {
        $this->recaptcha->setExpectedHostname($hostname);
    }

    /**
     * Validates the reCAPTCHA response.
     * 
     * Calls the reCAPTCHA verification API and checks if the response is valid.
     * 
     * @return bool True if the response is valid, false otherwise.
     */
    public function validate(): bool {
        $this->rresponse = $this->recaptcha->setScoreThreshold(1)->verify($this->response, $this->remoteIP);
        return $this->rresponse->isSuccess();
    }

    /**
     * Retrieves the reCAPTCHA verification response.
     * 
     * @return \ReCaptcha\Response|array The response object if available, otherwise an empty array.
     */
    public function getResponse(): \ReCaptcha\Response | array {
        return Utils::isNonNull($this->rresponse) ? $this->rresponse : [];
    }

    /**
     * Retrieves any error codes from the last reCAPTCHA verification attempt.
     * 
     * @return array An array of error codes, or an empty array if none exist.
     */
    public function getErrorCodes(): array {
        return Utils::isNonNull($this->rresponse) ? $this->rresponse->getErrorCodes() : [];
    }
}
