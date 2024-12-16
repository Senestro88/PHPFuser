<?php

namespace PHPFuser;

use \PHPFuser\Utils;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @author Senestro
 */
class Request {
    // PRIVATE VARIABLE
    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    public static function get($url, $params = array(), $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('GET', $exploded[0], array('query' => $params, 'headers' => $headers));
        return $response->getContent(false);
    }

    public static function post($url, $params = array(), $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('POST', $exploded[0], array('body' => $params, 'headers' => $headers));
        return $response->getContent(false);
    }

    public static function upload($url, $params = array(), $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('POST', $exploded[0], array('multipart' => Request::setMultipart($params), 'headers' => $headers));
        return $response->getContent(false);
    }

    public static function head($url, $headers = array()): array {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('HEAD', $exploded[0], array('headers' => $headers));
        return $response->getHeaders();
    }

    // PRIVATE METHODS
    private static function setMultipart($params = array()): array {
        $multipart = array();
        foreach ($params as $key => $value) {
            if (File::isFile($value)) {
                // Use fopen() to send the file as a stream
                $multipart[] = array('name' => $key, 'contents' => fopen($value, 'r'), 'filename' => basename($value));
            } else {
                // If it's not a file, just send the data as a normal key-value pair
                $multipart[] = array('name' => $key, 'contents' => $value);
            }
        }
        return $multipart;
    }
}
