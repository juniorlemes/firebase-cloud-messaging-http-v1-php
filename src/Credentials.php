<?php

namespace Kedniko\FCM;

use Firebase\JWT\JWT;
use GuzzleHttp;

class Credentials
{
     public const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

     public const TOKEN_URL = 'https://www.googleapis.com/oauth2/v4/token';

     public const EXPIRE = 3600;

     public const ALG = 'RS256';

     public const CONTENT_TYPE = 'form_params';

     public const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

     public const METHOD = 'POST';

     public const HTTP_ERRORS_OPTION = 'http_errors';

    private $keyFilePath;

    private $keyFileContent;

    /**
     * @return string Access token for a project
     */
    public function getAccessToken()
    {
        $requestBody = ['grant_type' => self::GRANT_TYPE, 'assertion' => $this->getTokenPayload()];

        $result = $this->getToken($requestBody);

        if (isset($result['error'])) {
            throw new \RuntimeException($result['error_description']);
        }

        return $result['access_token'];
    }

    public function setKeyFileContent(array $content)
    {
        $this->keyFileContent = json_encode($content, JSON_THROW_ON_ERROR);
        return $this;
    }

    /**
     * @return string Signed payload (with private key using algorithm)
     */
    private function getTokenPayload()
    {
        $keyBody = json_decode(
            (string) $this->getKeyFileContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $now = (new \DateTime())->format('U');
        $iat = (int) $now;

        $payload = [
            'iss' => $keyBody['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $iat,
            'exp' => $iat + self::EXPIRE,
            'sub' => null,
        ];

        return JWT::encode($payload, $keyBody['private_key'], self::ALG);
    }

    /**
     * @param $requestBody array    Payload with assertion data (which is signed)
     * @return array                Associative array of cURL result
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     *                              This exception is intentional
     */
    private function getToken(array $requestBody)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request(
            self::METHOD,
            self::TOKEN_URL,
            [self::CONTENT_TYPE => $requestBody, self::HTTP_ERRORS_OPTION => false]
        );

        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getProjectID()
    {
        $keyBody = json_decode(
            (string) $this->getKeyFileContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $keyBody['project_id'];
    }

    public function getKeyFileContent()
    {
        if ($this->keyFileContent) {
            return $this->keyFileContent;
        }

        return file_get_contents($this->keyFilePath);
    }

    public function getKeyFilePath()
    {
        return $this->keyFilePath;
    }

    public function setKeyFilePath(mixed $keyFilePath)
    {
        if (is_file($keyFilePath)) {
            $this->keyFilePath = $keyFilePath;
        } else {
            throw new \InvalidArgumentException('Key file could not be found', 1);
        }
    }
}
