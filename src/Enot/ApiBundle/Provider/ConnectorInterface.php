<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Provider;


interface ConnectorInterface
{
    /**
     * Отправляет запрос поставщику и получает от него ответ
     *
     * @param string $method
     * @param null $header
     * @param null $params
     * @return mixed Ответ поставщика
     */
    public function sendRequest($method, $header = null, $params = null);
}