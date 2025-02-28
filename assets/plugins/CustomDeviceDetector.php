<?php

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

/**
 *
 */

use PHPFuser\Utils;

class CustomDeviceDetector {
    /**
     * @var string The ip address
     */
    private string $ip = "";

    /**
     * @var DeviceDetector The device detector
     */
    private DeviceDetector $deviceDetector;

    public function __construct() {
        // Set version truncation to get detailed versions
        AbstractDeviceParser::setVersionTruncation(AbstractDeviceParser::VERSION_TRUNCATION_NONE);
        $this->ip = Utils::getIPAddress();
        $userAgent = getenv("HTTP_USER_AGENT");
        $this->deviceDetector = new DeviceDetector($userAgent);
        $this->deviceDetector->parse();
    }

    /**
     * Get the browser name
     */
    public function getBrowser(): string {
        // Get browser name
        $clientInfo = $this->deviceDetector->getClient(); // Contains browser details
        return $clientInfo['name'] ?? 'Unknown';
    }

    /**
     * Get he device name
     */
    public function getDevice(): string {
        // Get device name
        return $this->deviceDetector->getDeviceName() ?? 'Unknown';
    }

    /**
     * Get the device operating system name
     */
    public function getDeviceOsName(): string {
        // Get operating system name
        $osInfo = $this->deviceDetector->getOs(); // Contains OS details
        return $osInfo['name'] ?? 'Unknown';
    }

    /**
     * Get the device brand
     */
    public function getDeviceBrand(): string {
        // Get device brand
        return $this->deviceDetector->getBrandName() ?? 'Unknown';
    }

    /**
     * Get the ip address information
     */
    public function getIPInfo(): array {
        // Get IP address info
        $info = array();
        try {
            $info = (array) json_decode(@file_get_contents('https://ipinfo.io/json?token=d4e2c91d08f44e'), JSON_OBJECT_AS_ARRAY);
        } catch (\Throwable $e) {
            try {
                $data = (array) json_decode(@file_get_contents('https://ipapi.co/' . $this->ip . '/json/'), JSON_OBJECT_AS_ARRAY);
                if (isset($data['error']) && $data['error'] !== true) {
                    $info = $data;
                }
            } catch (\Throwable $e) {
            }
        }
        return $info;
    }

    /**
     * Determine if the device is mobile
     */
    public function isMobile() {
        return $this->deviceDetector->isMobile();
    }

    /**
     * Determine if the device is tablet
     */
    public function isTablet() {
        return $this->deviceDetector->isTablet();
    }

    /**
     * Determine if the device is smartphone
     */
    public function isSmartphone() {
        return $this->deviceDetector->isSmartphone();
    }

    /**
     * Determine if the device is desktop
     */
    public function isDesktop() {
        return $this->deviceDetector->isDesktop();
    }
}
