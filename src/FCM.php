<?php

namespace Kedniko\FCM;

class FCM
{
    /**
     * @param  array<string, mixed>  $keyContent  Service account key file content
     */
    public static function getBearerToken(array $keyContent): string
    {
        $c = new Credentials();
        $c->setKeyFileContent($keyContent);

        return $c->getAccessToken();
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function send(string $bearerToken, string $projectID, array $body): void
    {
        $client = new Client();
        $client->send($bearerToken, $projectID, $body);
    }

    /**
     * @param  list<string>  $tokens
     */
    public static function subscribeToTopic(string $bearerToken, string $topic, array $tokens): void
    {
        $host = Client::FCM_TOPIC_MANAGEMENT_HOST;
        $path = Client::FCM_TOPIC_MANAGEMENT_ADD_PATH;
        $client = new Client();
        $url = "https://{$host}{$path}";
        $body = [
            'to' => "/topics/{$topic}",
            'registration_tokens' => $tokens,
        ];
        $client->post($bearerToken, $url, $body);
    }

    /**
     * @param  list<string>  $tokens
     */
    public static function unsubscribeFromTopic(string $bearerToken, string $topic, array $tokens): void
    {
        $host = Client::FCM_TOPIC_MANAGEMENT_HOST;
        $path = Client::FCM_TOPIC_MANAGEMENT_REMOVE_PATH;
        $client = new Client();
        $url = "https://{$host}{$path}";
        $body = [
            'to' => "/topics/{$topic}",
            'registration_tokens' => $tokens,
        ];
        $client->post($bearerToken, $url, $body);
    }
}
