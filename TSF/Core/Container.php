<?php
/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/8/18
 * Time: 17:30
 */

namespace TSF\Core;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use TSF\Exception\Core\ContainerException;

class Container
{
    protected $relationMap = [];
    protected $sharedRelationMap = [];
    protected $globalSharedRelationMap = [];
    protected $singletons = [];
    protected $globalSingletons = [];

    public function bind($abstract, $concrete)
    {
        unset($this->sharedRelationMap[$abstract]);
        unset($this->globalSharedRelationMap[$abstract]);
        $this->relationMap[$abstract] = $concrete;
    }

    /**
     * 单条协程里共享
     * @param $abstract
     * @param $concrete
     */
    public function singleton($abstract, $concrete)
    {
        unset($this->relationMap[$abstract]);
        unset($this->globalSharedRelationMap[$abstract]);
        $this->sharedRelationMap[$abstract] = $concrete;
        if (is_object($concrete)) {
            $cUid = \Swoole\Coroutine::getuid();
            if (!isset($this->singletons[$cUid])) {
                $this->singletons[$cUid] = [];
            }
            $this->singletons[$cUid][$abstract] = $concrete;
        }
    }

    /**
     * 运行期间共享
     * @param $abstract
     * @param $concrete
     */
    public function globalSingleton($abstract, $concrete)
    {
        unset($this->relationMap[$abstract]);
        unset($this->sharedRelationMap[$abstract]);
        $this->globalSharedRelationMap[$abstract] = $concrete;
        if (is_object($concrete)) {
            $this->globalSingletons[$abstract] = $concrete;
        }
    }

    public function make($abstract, $params = [])
    {
        $cUid = \Swoole\Coroutine::getuid();
        $abstract = $this->normalize($abstract);

        if (isset($this->singletons[$cUid][$abstract])) {
            return $this->singletons[$cUid][$abstract];
        }
        if (isset($this->globalSingletons[$abstract])) {
            return $this->globalSingletons[$abstract];
        }
        $concrete = $this->getConcrete($abstract);

        if ($concrete === $abstract || $concrete instanceof Closure) {
            $object = $this->build($concrete, $params);
        } else {
            $object = $this->make($concrete, $params);
        }

        if (isset($this->sharedRelationMap[$abstract])) {
            if (!isset($this->singletons[$cUid])) {
                $this->singletons[$cUid] = [];
            }
            $this->singletons[$cUid][$abstract] = $object;
        }
        if (isset($this->globalSharedRelationMap[$abstract])) {
            $this->globalSharedRelationMap[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 删除斜杠
     *
     * @param  mixed  $service
     * @return mixed
     */
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

    /**
     * @param $abstract
     * @return mixed
     */
    public function getConcrete($abstract)
    {
        if (isset($this->sharedRelationMap[$abstract])) {
            return $this->sharedRelationMap[$abstract];
        }

        if (isset($this->relationMap[$abstract])) {
            return $this->relationMap[$abstract];
        }

        if (isset($this->globalSharedRelationMap[$abstract])) {
            return $this->globalSharedRelationMap[$abstract];
        }

        return $abstract;
    }

    public function build($concrete, $params = [])
    {
        // 匿名函数直接返回
        if ($concrete instanceof Closure) {
            return $concrete($this, $params);
        }
        // 创建反射类
        $reflector = new ReflectionClass($concrete);

        // 检测是否可实例化
        if (! $reflector->isInstantiable()) {
            
            //TODO::返回具体原因
            $message = "Target [$concrete] is not instantiable.";

            throw new ContainerException($message);
        }

        $constructor = $reflector->getConstructor();

        //构造函数没有依赖直接返回
        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        $params = $this->keyParametersByArgument(
            $dependencies, $params
        );

        $instances = $this->getDependencies(
            $dependencies, $params
        );

        return $reflector->newInstanceArgs($instances);
    }

    public function resolveParametersForMethod($class, $method, array $parameters = [])
    {
        $method = (new ReflectionClass($class))->getMethod($method);
        if (is_null($method)) {
            throw new ContainerException("Cannot resolve parameters for {$class} {$method}");
        }
        $dependencies = $method->getParameters();
        $params = $this->keyParametersByArgument(
            $dependencies, $parameters
        );
        $params = $this->getDependencies(
            $dependencies, $params
        );

        return $params;
    }

    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);

                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }

    protected function getDependencies(array $parameters, array $primitives = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return $dependencies;
    }

    /**
     * 解决不是类的依赖
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     * @throws ContainerException
     */
    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new ContainerException($message);
    }

    /**
     * 解决类依赖
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     * @throws ContainerException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        } catch (ContainerException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    public function clearCurrentSingleton()
    {
        unset($this->singletons[\Swoole\Coroutine::getuid()]);
    }
}
/*
$con = new Container();

$fun = $con->bind( 'User' , function(){} );
$obj = $con->make('TSF\Core\Config');
$ret = $obj->get('jdjdj');
var_dump( $obj ,$ret);
*/
