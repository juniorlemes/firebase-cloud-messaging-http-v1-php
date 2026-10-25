<?php

declare(strict_types=1);

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

    private ?string $keyFilePath = null;

    private ?string $keyFileContent = null;

    /**
     * @return string Access token for a project
     */
    public function getAccessToken(): string
    {
        $requestBody = ['grant_type' => self::GRANT_TYPE, 'assertion' => $this->getTokenPayload()];

        $result = $this->getToken($requestBody);

        if (isset($result['error'])) {
            throw new \RuntimeException($this->getString($result, 'error_description'));
        }

        return $this->getString($result, 'access_token');
    }

    /**
     * @param  array<string, mixed>  $content  Service account key file content
     */
    public function setKeyFileContent(array $content): static
    {
        $this->keyFileContent = json_encode($content, JSON_THROW_ON_ERROR);

        return $this;
    }

    /**
     * @return string Signed payload (with private key using algorithm)
     */
    private function getTokenPayload(): string
    {
        $keyBody = $this->getKeyBody();
        $now = new \DateTime()->format('U');
        $iat = (int) $now;

        $payload = [
            'iss' => $this->getString($keyBody, 'client_email'),
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $iat,
            'exp' => $iat + self::EXPIRE,
            'sub' => null,
        ];

        return JWT::encode($payload, $this->getString($keyBody, 'private_key'), self::ALG);
    }

    /**
     * @param  array<string, string>  $requestBody  Payload with assertion data (which is signed)
     * @return array<mixed> Associative array of the token response
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     *                                              This exception is intentional
     */
    private function getToken(array $requestBody): array
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request(
            self::METHOD,
            self::TOKEN_URL,
            [self::CONTENT_TYPE => $requestBody, self::HTTP_ERRORS_OPTION => false]
        );

        return $this->decodeJson((string) $response->getBody());
    }

    public function getProjectID(): string
    {
        return $this->getString($this->getKeyBody(), 'project_id');
    }

    public function getKeyFileContent(): string
    {
        if ($this->keyFileContent) {
            return $this->keyFileContent;
        }

        if ($this->keyFilePath === null) {
            throw new \LogicException('Key file content or key file path must be set');
        }

        $content = file_get_contents($this->keyFilePath);
        if ($content === false) {
            throw new \RuntimeException('Key file could not be read');
        }

        return $content;
    }

    public function getKeyFilePath(): ?string
    {
        return $this->keyFilePath;
    }

    public function setKeyFilePath(string $keyFilePath): void
    {
        if (is_file($keyFilePath)) {
            $this->keyFilePath = $keyFilePath;
        } else {
            throw new \InvalidArgumentException('Key file could not be found', 1);
        }
    }

    /**
     * @return array<mixed>
     */
    private function getKeyBody(): array
    {
        return $this->decodeJson($this->getKeyFileContent());
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            throw new \UnexpectedValueException('Expected a JSON object');
        }

        return $data;
    }

    /**
     * @param  array<mixed>  $data
     */
    private function getString(array $data, string $key): string
    {
        if (! isset($data[$key]) || ! is_string($data[$key])) {
            throw new \UnexpectedValueException("Missing or invalid \"{$key}\"");
        }

        return $data[$key];
    }
}
