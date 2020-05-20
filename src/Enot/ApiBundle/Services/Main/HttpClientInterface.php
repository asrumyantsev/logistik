<?php
/**
 * ...
 *
 * @author Vladimir Minkovich
 * @date 12/07/2017
 * @time 23:43
 */

namespace Enot\ApiBundle\Services\Main;

interface HttpClientInterface
{
    /**
     * @param $headerAuthName
     */
    public function setHeaderAuthName($headerAuthName);

    /**
     * @param $url
     * @param $body
     * @param string $contentType
     * @param array $curlOpt
     * @return null|string
     */
    public function getToken($url, $body, $contentType = 'json', $curlOpt = []);

    /**
     * @param $url
     * @param string $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     */
    public function get($url, $body = '', $authToken = '', $contentType = 'json', $curlOpt = []);

    /**
     * @param $url
     * @param $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     */
    public function post($url, $body, $authToken = '', $contentType = 'json', $curlOpt = []);

    /**
     * @param $url
     * @param $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     */
    public function put($url, $body, $authToken = '', $contentType = 'json', $curlOpt = []);

    /**
     * @param $url
     * @param string $body
     * @param $authToken
     * @param string $contentType
     * @param array $curlOpt
     * @return mixed|null|string
     */
    public function delete($url, $body = '', $authToken = '', $contentType = 'json', $curlOpt = []);
}