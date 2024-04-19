<?php

// 命名空间
namespace Xzb\CodeIgniter3\Support\Traits;

// PHP 异常类
use BadMethodCallException;
use Error;
use Throwable;

/**
 * 呼叫转接
 */
trait ForwardsCalls
{
    /**
     * 转发 调用方法
     *
     * @param  mixed  $object
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     * @throws \Error
     * @throws \Throwable
     */
    protected function forwardCallToObject($object, $method, $parameters)
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Error|BadMethodCallException $e) {
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';

            if (! preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }

            if (
				$matches['class'] != get_class($object)
				|| $matches['method'] != $method
			) {
                throw $e;
            }

			throw new BadMethodCallException(sprintf(
				'Call to undefined method %s::%s()', static::class, $method
			));
        } catch (Throwable $e) {
			throw $e;
		}
    }

}
