<?php

namespace Royalcms\Laravel\JsonRpcClient;

use Datto\JsonRpc\Http\Client;
use Royalcms\Laravel\JsonRpcClient\Exception\HTTPException;
use Royalcms\Laravel\JsonRpcClient\Exception\RPCException;

abstract class JsonRpcHttpClient
{
    /**
     * The service name of the target service.
     *
     * @var string
     */
    protected $serviceName = '';

    /**
     * The protocol of the target service, this protocol name
     *
     * @var string
     */
    protected $protocol = 'jsonrpc-http';

    /**
     * @param string $method
     * @param array $params
     * @param string|null $id
     * @return mixed
     * @throws \Datto\JsonRpc\Http\Exceptions\HttpException
     * @throws \ErrorException
     */
    protected function __request(string $method, array $params, string $id = null)
    {
        try {
            $headers = $this->getHeaders();
            $client  = new BasicClient(self::getUri(), $headers);
            $service = $this->getService();
            if ($service != 'default') {
                $method = "{$service}/{$method}";
            }
            $client->query($method, $params, $response);
            $client->send();
            //如果返回异常，会提示message和code
            if (method_exists($response, 'getMessage') && method_exists($response, 'getCode')) {
                $message = $response->getMessage();
                $code    = $response->getCode();
                throw new RPCException($message, $code);
            }
            return $response;
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code    = $exception->getCode() ?: -1;
            throw new HTTPException($message, $code);
        }
    }

    /**
     * @return string
     */
    private function getUri(): string
    {
        $services = $this->getServices();
        $nodes    = $services [strtoupper($this->serviceName)];
        $max      = count($nodes);
        $r        = rand(1, $max);
        $node     = $nodes[$r - 1];
        if (is_array($node)) {
            $url = (new Node($node['port'], $node['port'], isset($node['path']) ?: null, isset($node['query']) ?: null))->getUrl();
        }
        else {
            $node = parse_url($node);
            if ($node['scheme'] == 'https' && empty($node['port'])) {
                $node['port'] = 443;
            }
            elseif ($node['scheme'] == 'http' && empty($node['port'])) {
                $node['port'] = 80;
            }
            $url = (new Node($node['port'], $node['port'], isset($node['path']) ?: null, isset($node['query']) ?: null))->getUrl();
        }

        return $url;
    }

    private function getHeaders()
    {
        $auth_user = config('rpc-services.auth_user');
        $auth_password = config('rpc-services.auth_password');
        return (new BasicAuthentication($auth_user, $auth_password))->getAuthorizationHeaders();
    }

    /**
     * '获取services'
     * @return array
     */
    private function getServices(): array
    {
        $array    = config('rpc-services.services');
        $services = [];
        foreach ($array as $key => $val) {
            foreach ($val['services'] as $k => $v) {
                $serviceName            = strtoupper($v);
                $services[$serviceName] = $val['nodes'];
            }
        }
        return $services;
    }

    /**
     * @return string
     */
    private function getService(): string
    {
        $service = explode("Service", $this->serviceName)[0];
        return strtolower($service);
    }

}

