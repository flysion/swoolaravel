<?php

namespace Flysion\Swoolaravel\Swoole;

class HashMap
{
    /**
     * @var \Swoole\Table
     */
    protected $data;

    /**
     * @param array $columns
     * @param null|float $conflict_proportion
     */
    public function __construct($columns, $conflict_proportion = null)
    {
        $this->data = new \Swoole\Table(1, $conflict_proportion);

        foreach($columns as $name => $option)
        {
            $this->data->column($name, ...$option);
        }

        $this->data->create();
    }

    /**
     * @param string|null
     * @param null $conflict_proportion
     * @return static
     * @throws \ReflectionException
     */
    public static function createFromProperty($class = null, $conflict_proportion = null)
    {
        return new static(
            \Flysion\Swoolaravel\parse_class_property_to_table_column($class ?? static::class),
            $conflict_proportion
        );
    }

    /**
     * @param string $key
     * @param int $incrby
     * @return int
     */
    public function incr($key, $incrby = 1)
    {
        return $this->data->incr(0, $key, $incrby);
    }

    /**
     * @param string $key
     * @param int $decrby
     * @return int
     */
    public function decr($key, $decrby = 1)
    {
        return $this->data->decr(0, $key, $decrby);
    }

    /**
     * @param string $key
     * @param array $value
     * @return bool
     */
    public function set($key, $value)
    {
        return $this->data->set(0, [$key => $value]);
    }

    /**
     * @param string $key
     * @return mixed|false
     */
    public function get($key)
    {
        $value = $this->data->get(0, $key);
        if($value === false) {
            return $this->defaultValue($key);
        }

        return $value === false ? null : $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function defaultValue($key)
    {
        $method = 'default' . ucfirst(\Illuminate\Support\Str::camel($key)) . 'Value';
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data->get(0);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}