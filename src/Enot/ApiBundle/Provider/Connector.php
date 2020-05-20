<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Provider;


use Enot\ApiBundle\Model\ConnectorRequestModel;
use Enot\ApiBundle\Utils\EnotError;
use Enot\LogBundle\Utils\Logger;

class Connector implements ConnectorInterface
{

    private $connectionParams = null;
    private $logger = null;
    private $httpClient = null;
    private $requestId = null;
    const FAULT_CONNECTION_MSG = 'Could not connect to host';

    const SOAP_OCPP_1_6_NAMESPACE = 'urn://Ocpp/Cp/2015/10/';
//    const SOAP_WSA_NAMESPACE = 'http://www.w3.org/2005/08/addressing';
    const SOAP_DELTRANS_NAMESPACE = 'http://deltransmsk.ru';

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;


    /**
     * @param ConnectorRequestModel $request
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    public function __construct($request)
    {
        $connection = $request->connectionString;
        $logger = $request->logger;
        $httpClient = $request->httpClient;

        if ($connection) {
            $this->connectionParams = $this->parseConnectionString($connection);
        }

        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->requestId = Logger::getGuid();

        $this->ip = $this->connectionParams['ip'];
        $this->port = $this->connectionParams['port'];
        $this->protocol = $this->connectionParams['protocol'];
        $this->endpoint = $this->connectionParams['endpoint'];
        $this->login = $this->connectionParams['login'];
        $this->password = $this->connectionParams['password'];

    }

    /**
     * Разбирает строку подключения на массив параметров для дальшейшего использования
     *
     * @param string $connectionString Строка с параметрами подключения
     * @return array Параметры подключения
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    private function parseConnectionString($connectionString)
    {
        $result = array();
        $parts = explode(';', $connectionString);
        foreach ($parts as $part) {
            if (!empty($part)) {
                $keyValRegExp = '#([^=]+)=(.+)#i'; //Парсинг строки вида key=value на два куска вне зависимости, содержит ли value знаки = или нет
                $matches = array();
                $partData = preg_match($keyValRegExp, $part, $matches);
                if (!$partData) {
                    $this->logger->log(['Incorrect connection string (must contain key=value). Connection string part: `\' . $part . \'`\''], __FUNCTION__, __CLASS__);
                    continue;
                }
                $key = $matches[1];
                $value = $matches[2];
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Записывает запрос к поставщику в таблицу с логами и возвращает идентификатор записи
     *
     * @param $request
     * @param $event
     * @param $domain
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    protected function loggingRequest($request, $event, $domain)
    {
        if (!is_array($request)) {
            $request = (array)json_decode($request);
        }
        $this->logger->setParameters(null, $this->requestId);
        $this->logger->logSupplier($request, $event, $domain, Logger::CATEGORY_REQUEST);
    }

    /**
     * Записывает ответ поставщика в таблицу с логами
     *
     * @param $response
     * @param $event
     * @param $domain
     * @throws \Enot\LogBundle\Exceptions\LoggerException
     */
    protected function loggingResponse($response, $event, $domain)
    {
        if (!is_array($response)) {
            $response = (array)json_decode($response);
        }
        $this->logger->setParameters(null, $this->requestId);
        $this->logger->logSupplier($response, $event, $domain, Logger::CATEGORY_RESPONSE);
    }

    /**
     * Отправляет запрос поставщику и получает от него ответ
     *
     * @param string $method
     * @param null $header
     * @param null $body
     * @return mixed Ответ поставщика
     * @throws ProviderException
     */
    public function sendRequest($method, $header = null, $body = null)
    {
        try {
            if (isset($this->port) && !empty($this->port)) {
                $url = $this->protocol . '://' . $this->ip . ':' . $this->port . '/' . $this->endpoint . '/';
            } else {
                $url = $this->protocol . '://' . $this->ip . '/' . $this->endpoint . '/';
            }

            $connectionTimout = 20;
            /** @var \SoapClient $soapClient */
            $soapClient = new \SoapClient(null, [
                'login' => $this->login,
                'password' => $this->password,
                'location' => $url,
                'uri' => self::SOAP_DELTRANS_NAMESPACE,
                'trace' => 1,
                "connection_timeout" => $connectionTimout,
                'soap_version' => SOAP_1_2
            ]);

            $params = [];
            foreach ((array)$body as $key => $item) {
                $params[] = new \SoapParam($item, $key);
            }

            $response = $soapClient->__soapCall(ucfirst($method), $params);
            $this->loggingRequest(json_encode($soapClient->__getLastRequest()), $method, __CLASS__);
            $this->loggingResponse(json_encode($soapClient->__getLastResponse()), $method, __CLASS__);

            return $response;
        } catch (\Exception $exception) {
            if ($exception->getMessage() === self::FAULT_CONNECTION_MSG) {
                throw new ProviderException(EnotError::WRONG_STATION_CONNECTION, '', $exception->getCode());
            } else {
                throw new ProviderException(null, $exception->getMessage(), $exception->getCode());
            }
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws ProviderException
     */
    public function getDriversAvtoTrailers($data)
    {
        $response = $this->sendRequest(__FUNCTION__, null, $data->body);
        return $response;
    }

    /**
     * @param $data
     * @return mixed
     * @throws ProviderException
     */
    public function putInfoCargoTransportation($data)
    {
        $response = $this->sendRequest(__FUNCTION__, null, $data->body);
        return $response;
    }

    /**
     * @param $data
     * @return mixed
     * @throws ProviderException
     */
    public function putInfoCarState($data)
    {
        $response = $this->sendRequest(__FUNCTION__, null, $data->body);
        return $response;
    }

    /**
     * @param $data
     * @return mixed
     * @throws ProviderException
     */
    public function putInforAppointment($data)
    {
        $response = $this->sendRequest(__FUNCTION__, null, $data->body);
        return $response;
    }
}