<?php

namespace Reflection;


use Reflection\internal\Builder\BuilderFactory;
use Reflection\internal\CachedProxyClassFactory;
use Reflection\internal\ProxyClassFactory;
use Reflection\internal\ProxyClassFactoryImpl;
use Reflection\internal\ProxyMark;

class Proxy
{
    private static $instance;
    /**
     * @var ProxyClassFactory
     */
    private $proxyClassFactory;

    private function __construct()
    {
        $this->proxyClassFactory = new CachedProxyClassFactory(new ProxyClassFactoryImpl(new BuilderFactory()));
    }

    /**
     * @param string|string[] $classOrInterfaces
     * @return ProxyClass
     */
    public static function getProxyClass($classOrInterfaces)
    {
        return self::instance()->get($classOrInterfaces);
    }

    /**
     * @param object $object
     * @return bool
     */
    public static function isProxyClass($object)
    {
        return $object instanceof ProxyMark;
    }

    /**
     * @param string|string[] $classOrInterfaces
     * @param InvocationHandler $handler
     * @return object
     */
    public static function newInstance($classOrInterfaces, InvocationHandler $handler)
    {
        $proxyClass = self::getProxyClass($classOrInterfaces);

        return $proxyClass->newInstance($handler);
    }

    /**
     * @return Proxy
     */
    private static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string|string[] $classOrInterfaces
     * @return ProxyClass
     * @throws \ReflectionException
     */
    private function get($classOrInterfaces)
    {
        if (!is_array($classOrInterfaces)) {
            $classOrInterfaces = [$classOrInterfaces];
        }

        $class = $this->extractReflectionClass($classOrInterfaces);
        $interfaces = $this->extractReflectionInterfaces($classOrInterfaces);

        if (count($interfaces) + 1 < count($classOrInterfaces)) {
            throw new \ReflectionException('Something went wrong :)');
        }

        $proxyClass = $this->proxyClassFactory->get($class, $interfaces);

        return $proxyClass;
    }

    /**
     * @param string[] $classOrInterfaces
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private function extractReflectionClass($classOrInterfaces)
    {
        $classes = [];

        foreach ($classOrInterfaces as $name) {
            if (class_exists($name)) {
                $classes[] = new \ReflectionClass($name);
            }
        }

        if (count($classes) > 1) {
            throw new \ReflectionException('Cannot proxy class with more then 1 base class!');
        }

        if (count($classes) == 1) {
            return reset($classes);
        }

        return new \ReflectionClass(\stdClass::class);
    }

    /**
     * @param $classOrInterfaces
     * @return array
     */
    private function extractReflectionInterfaces($classOrInterfaces)
    {
        $interfaces = [];

        foreach ($classOrInterfaces as $name) {
            if (interface_exists($name)) {
                $classes[] = new \ReflectionClass($name);
            }
        }

        return $interfaces;
    }
}