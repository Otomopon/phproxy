<?php

namespace Reflection\fixture;


class CallArgsType
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __call($name, array $args)
    {
        return $args;
    }
}