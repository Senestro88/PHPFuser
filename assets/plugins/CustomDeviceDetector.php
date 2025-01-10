<?php

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

/**
 *
 */

use PHPFuser\Utils;

if (!class_exists("\CustomDeviceDetector")) {
    class CustomDeviceDetector {
        private string $ip = "";
        private DeviceDetector $deviceDetector;

        public function __construct() {
            // Set version truncation to get detailed versions
            AbstractDeviceParser::setVersionTruncation(AbstractDeviceParser::VERSION_TRUNCATION_NONE);
            $this->ip = Utils::getIPAddress();
            $userAgent = getenv("HTTP_USER_AGENT");
            $this->deviceDetector = new DeviceDetector($userAgent);
            $this->deviceDetector->parse();
        }

        public function getBrowser() {
            // Get browser name
            $clientInfo = $this->deviceDetector->getClient(); // Contains browser details
            return $clientInfo['name'] ?? 'Unknown';
        }

        public function getDevice() {
            // Get device name
            return $this->deviceDetector->getDeviceName() ?? 'Unknown';
        }

        public function getDeviceOsName() {
            // Get operating system name
            $osInfo = $this->deviceDetector->getOs(); // Contains OS details
            return $osInfo['name'] ?? 'Unknown';
        }

        public function getDeviceBrand() {
            // Get device brand
            return $this->deviceDetector->getBrandName() ?? 'Unknown';
        }

        public function getIPInfo() {
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

        public function isMobile() {
            return $this->deviceDetector->isMobile();
        }

        public function isTablet() {
            return $this->deviceDetector->isTablet();
        }

        public function isSmartphone() {
            return $this->deviceDetector->isSmartphone();
        }

        public function isDesktop() {
            return $this->deviceDetector->isDesktop();
        }
    }
}
