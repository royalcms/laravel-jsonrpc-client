<?php

namespace Royalcms\Laravel\JsonRpcClient;

use Datto\JsonRpc\Http\Client;

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


    public function __construct()
    {

    }

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
        try{
            $client = new Client(self::getUri());
            $service = $this->getService();
            if ($service != 'default') {
                $method = "{$service}/{$method}";
            }
            $client->query($method, $params, $response);
            $client->send();
            //如果返回异常，会提示message和code
            if(method_exists($response,'getMessage') && method_exists($response,'getCode')){
                $message = $response->getMessage();
                $code = $response->getCode();
                throw new \Exception("code:$code,message:$message");
            }
            return $response;
        }catch(\Exception $exception){
            return ['code'=>-1,'message'=>$exception->getMessage()];
        }
    }

    /**
     * @return string
     */
    private function getUri(): string
    {
        $services = $this->getServices();
        $nodes = $services [strtoupper($this->serviceName)];
        $max = count($nodes);
        $r = rand(1, $max);
        $node = $nodes[$r - 1];

        if ($node['port'] == 80) {
            $url = "http://{$node['host']}";
        }
        elseif ($node['port'] == 443) {
            $url = "https://{$node['host']}";
        }
        else {
            $url = "http://{$node['host']}:{$node['port']}";
        }

        if (isset($node['path'])) {
            $url = $url . $node['path'];
        }

        return $url;
    }

    /**
     * '获取services'
     * @return array
     */
    private function getServices(): array
    {
        $array = config('rpc-services.services');
        $services = [];
        foreach ($array as $key => $val) {
            foreach ($val['services'] as $k => $v) {
                $serviceName = strtoupper($v);
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

