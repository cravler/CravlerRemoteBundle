<?php

namespace Cravler\RemoteBundle\Service;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EndpointsChain
{
    /**
     * @var array
     */
    private $endpoints = array();

    /**
     * @param $transport
     */
    public function addEndpoint($endpoint)
    {
        $reflection = new \ReflectionClass($endpoint);
        list($bundle, $prefix) = explode('Bundle\\', $reflection->getNamespaceName());
        $prefix = explode('\\', $prefix);
        array_shift($prefix);

        $key = str_replace('\\', '', $bundle);
        if (count($prefix)) {
            $key .= '_' . implode('_', $prefix);
        }
        $key .= '_' . $reflection->getShortName();

        $this->endpoints[$key] = $endpoint;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getEndpoint($key)
    {
        return $this->endpoints[$key];
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        $result = array();
        foreach ($this->endpoints as $name => $endpoint) {
            $methods = get_class_methods($endpoint);
            foreach ($methods as $method) {
                if ('__construct' === $method) {
                    continue;
                }
                if (!isset($result[$name])) {
                    $result[$name] = array();
                }
                $result[$name][] = $method;
            }
        }

        return $result;
    }
}
