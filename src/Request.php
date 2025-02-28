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

    /**
     * Make a GET request
     * 
     * @param string $url The url to make the GET request
     * @param array $params The request parameters
     * @param array $headers The request headers
     * @return string
     */
    public static function get(string $url, array $params = array(), array $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('GET', $exploded[0], array('query' => $params, 'headers' => $headers));
        return $response->getContent(false);
    }

    /**
     * Make a POST request
     * 
     * @param string $url The url to make the POST request
     * @param array $params The request parameters
     * @param array $headers The request headers
     * @return string
     */
    public static function post(string $url, array $params = array(), array $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('POST', $exploded[0], array('body' => $params, 'headers' => $headers));
        return $response->getContent(false);
    }

    /**
     * Make a POST request with multipart support
     * 
     * @param string $url The url to make the POST request
     * @param array $params The request parameters
     * @param array $headers The request headers
     * @return string
     */
    public static function upload(string $url, array $params = array(), array $headers = array()): string {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('POST', $exploded[0], array('multipart' => Request::setMultipart($params), 'headers' => $headers));
        return $response->getContent(false);
    }

    /**
     * Make a HEAD request
     * 
     * @param string $url The url to make the HEAD request
     * @param array $headers The request headers
     * @return array
     */
    public static function head(string $url, array $headers = array()): array {
        $client = HttpClient::create(array('verify_peer' => false, 'cafile' => ''));
        $exploded = explode("?", $url);
        $response = $client->request('HEAD', $exploded[0], array('headers' => $headers));
        return $response->getHeaders();
    }

    // PRIVATE METHODS

    /**
     * Sets the multipart for upload request 
     * 
     * @param array $params The request parameters
     * @return array
     */
    private static function setMultipart(array $params = array()): array {
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
