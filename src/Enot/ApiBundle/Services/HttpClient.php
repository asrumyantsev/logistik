<?php

namespace Enot\ApiBundle\Services;

use Enot\ApiBundle\Services\Exceptions\HttpClientException;
use Enot\ApiBundle\Services\Main\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpClient implements HttpClientInterface
{
    const
        HEAD_CONTENT_TYPE = 'Content-Type',
        CONTENT_TYPE_JSON = 'application/json; charset=utf-8',
        CONTENT_TYPE_XML = 'application/xml; charset=utf-8';

    private $httpClient;
    private $headerAuthName = 'Access-Token';

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param $headerAuthName
     */
    public function setHeaderAuthName($headerAuthName)
    {
        $this->headerAuthName = $headerAuthName;
    }

    /**
     * @param $url
     * @param $body
     * @param string $contentType
     * @param array $curlOpt
     * @return null|string
     * @throws HttpClientException
     */
    public function getToken($url, $body, $contentType = 'json', $curlOpt = [])
    {
        $result = null;
        $httpContentType = $this->_setContentType($contentType);

        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    self::HEAD_CONTENT_TYPE => $httpContentType
                ],
                'body' => $body,
                'curl' => $curlOpt
            ]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpClientException(null, $e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $url
     * @param string $body Custom field for ChatMe API
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     * @throws HttpClientException
     */
    public function get($url, $body = '', $authToken = '', $contentType = 'json', $curlOpt = [])
    {
        $result = null;
        $httpContentType = $this->_setContentType($contentType);

        try {
            $response = $this->httpClient->get($url, [
                'headers' => [
                    $this->headerAuthName => $authToken,
                    self::HEAD_CONTENT_TYPE => $httpContentType
                ],
                'body' => $body,
                'curl' => $curlOpt
            ]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpClientException(null, $e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $url
     * @param $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     * @throws HttpClientException
     */
    public function post($url, $body, $authToken = '', $contentType = 'json', $curlOpt = [])
    {
        $result = null;
        $httpContentType = $this->_setContentType($contentType);

        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    $this->headerAuthName => $authToken,
                    self::HEAD_CONTENT_TYPE => $httpContentType
                ],
                'body' => $body,
                'curl' => $curlOpt
            ]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpClientException(null, $e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $url
     * @param $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     * @throws HttpClientException
     */
    public function put($url, $body, $authToken = '', $contentType = 'json', $curlOpt = [])
    {
        $result = null;
        $httpContentType = $this->_setContentType($contentType);

        try {
            $response = $this->httpClient->put($url, [
                'headers' => [
                    $this->headerAuthName => $authToken,
                    self::HEAD_CONTENT_TYPE => $httpContentType
                ],
                'body' => $body,
                'curl' => $curlOpt
            ]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpClientException(null, $e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $url
     * @param string $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     * @throws HttpClientException
     */
    public function delete($url, $body = '', $authToken = '', $contentType = 'json', $curlOpt = [])
    {
        $result = null;
        $httpContentType = $this->_setContentType($contentType);

        try {
            $response = $this->httpClient->delete($url, [
                'headers' => [
                    $this->headerAuthName => $authToken,
                    self::HEAD_CONTENT_TYPE => $httpContentType
                ],
                'body' => $body,
                'curl' => $curlOpt
            ]);
            $result = $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw new HttpClientException(null, $e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $type
     * @return string
     */
    private function _setContentType($type)
    {
        switch ($type) {
            case 'json':
                {
                    $httpContentType = self::CONTENT_TYPE_JSON;
                    break;
                }
            case 'xml':
                {
                    $httpContentType = self::CONTENT_TYPE_XML;
                    break;
                }
            default:
                {
                    $httpContentType = self::CONTENT_TYPE_JSON;
                    break;
                }
        }

        return $httpContentType;
    }
}