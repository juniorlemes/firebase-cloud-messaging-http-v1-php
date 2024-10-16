<?php

namespace Kedniko\FCM;

use GuzzleHttp;

class Client
{

    /**
     * Docs:
     * https://developers.google.com/instance-id/reference/server?hl=en
     * https://github.com/firebase/firebase-admin-node/blob/master/src/messaging/messaging.ts#L766
     */
    const FCM_SEND_HOST = 'fcm.googleapis.com';
    const FCM_SEND_PATH = '/fcm/send';
    const FCM_TOPIC_MANAGEMENT_HOST = 'iid.googleapis.com';
    const FCM_TOPIC_MANAGEMENT_ADD_PATH = '/iid/v1:batchAdd';
    const FCM_TOPIC_MANAGEMENT_REMOVE_PATH = '/iid/v1:batchRemove';

    public const CONTENT_TYPE = 'json';

    public const HTTP_ERRORS_OPTION = 'http_errors';

    public function getUrl(string $projectID)
    {
        return "https://fcm.googleapis.com/v1/projects/{$projectID}/messages:send";
    }

    public function send(string $bearerToken, string $projectID, array $body)
    {
        $url = $this->getUrl($projectID);
        return $this->post($bearerToken, $url, $body);
    }

    public function post(string $bearerToken, string $url, array $body)
    {
        $config = [
            'headers' => [
                'Authorization' => 'Bearer ' . $bearerToken,
            ]
        ];

        if ($this->getHost($url) === self::FCM_TOPIC_MANAGEMENT_HOST) {
            $config['headers']['access_token_auth'] = 'true';
        }

        $options = [
            self::CONTENT_TYPE => $body,
            self::HTTP_ERRORS_OPTION => false,
        ];
        // Class name conflict occurs, when used as "Client"
        $client = new GuzzleHttp\Client($config);
        $response = $client->request('POST', $url, $options);

        if ($response->getStatusCode() === 200) {
            return true;
        }
        $result = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $result['error']['message'];
    }

    private function getHost(string $url)
    {
        $parts = parse_url($url);
        return $parts['host'];
    }
}
