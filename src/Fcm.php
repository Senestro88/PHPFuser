<?php

namespace PHPFuser;

use Google\Auth\Credentials\ServiceAccountCredentials;
use \PHPFuser\Utils;

/**
 * Firebase Cloud Messaging (FCM) helper class.
 *
 * Provides utility methods for sending notifications
 * through Firebase Cloud Messaging (FCM) using the
 * FCM HTTP v1 API.
 *
 * @author Senestro
 */
class Fcm {

    /**
     * Send a notification message to a single device via FCM.
     *
     * @param string $accessToken   OAuth2 access token for FCM authorization.
     * @param string $projectId     Firebase project ID.
     * @param string $deviceToken   Target device FCM registration token.
     * @param string $title         Notification title.
     * @param string $body          Notification body message.
     *
     * @return array Decoded FCM API response as an associative array.
     */
    public static function send(string $accessToken, string $projectId, string $deviceToken, string $title, string $body): array {
        $result = [];
        try {
            // FCM v1 endpoint for sending messages
            $url = "https://fcm.googleapis.com/v1/projects/" . $projectId . "/messages:send";
            // HTTP headers with Bearer token authentication
            $headers = ["Authorization: Bearer $accessToken", "Content-Type: application/json; UTF-8"];
            // The notification data
            $notification = ["title" => $title, "body"  => $body];
            // Message payload structure
            $postData = [
                "validate_only" => false,
                "message" => [
                    "token" => $deviceToken,
                    "notification" => $notification,
                    "android" => [
                        "notification" => $notification,
                    ]
                ]
            ];
            // Initialize cURL for HTTP request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);               // Set request URL
            curl_setopt($ch, CURLOPT_POST, true);              // Set request method to POST
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    // Set HTTP headers
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // Return response as string
            // Encode the payload as JSON before sending
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            // Execute the request and get the response
            $response = curl_exec($ch);
            // Close the cURL session
            curl_close($ch);
            // Return decoded JSON response, or empty array if request failed
            $result = $response === false ? [] : Utils::jsonToArray($response);
        } catch (\Throwable $throwable) {
        }
        return $result;
    }

    /**
     * Send a notification message to multiple devices (multicast).
     *
     * @param string $accessToken    OAuth2 access token for FCM authorization.
     * @param string $projectId      Firebase project ID.
     * @param array  $deviceTokens   Array of FCM registration tokens.
     * @param string $title          Notification title.
     * @param string $body           Notification body message.
     *
     * @return array Associative array of device tokens mapped to their responses.
     */
    public static function sendMulticast(string $accessToken, string $projectId, array $deviceTokens, string $title, string $body): array {
        $results = [];
        // Loop through each device token and send a message individually
        foreach ($deviceTokens as $token) {
            $results[$token] = self::send($accessToken, $projectId, $token, $title, $body, false);
        }
        return $results;
    }

    /**
     * Get an OAuth2 access token for Firebase Cloud Messaging.
     *
     * Reads the service account credentials from a JSON key file
     * and fetches an access token with the required scope.
     *
     * @param string $serviceAccountPath Path to the service account JSON key file.
     *
     * @return string|null Access token string if successful, null if failed.
     */
    public static function getAccessToken(string $serviceAccountPath): ?string {
        // Required scope for Firebase Cloud Messaging
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        // Load service account credentials
        $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);
        // Fetch OAuth2 token
        $token = $credentials->fetchAuthToken();
        // Return access token if available, otherwise null
        return !isset($token['access_token']) ? null : $token['access_token'];
    }
}
