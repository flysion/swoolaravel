<?php

namespace Flysion\Swoolaravel\Swoole;

class Mem
{
    /**
     * @var \Swoole\Table
     */
    protected $data;

    /**
     * @param null $conflict_proportion
     */
    public function __construct($fields, $conflict_proportion = null)
    {
        $this->data = new \Swoole\Table(1, $conflict_proportion);

        foreach($fields as $field => $option)
        {
            $this->data->column($field, ...$option);
        }

        $this->data->create();
    }

    /**
     * @param null $conflict_proportion
     * @return static
     * @throws \ReflectionException
     */
    public static function createFromProperty($conflict_proportion = null)
    {
        $fields = [];

        $ref = new \ReflectionClass(static::class);
        foreach(explode("\n", $ref->getDocComment()) as $line)
        {
            if(preg_match('/^\*\s*@property\s+(int|float|string\((\d+)\))\s+\$(\w+)/', trim($line), $result))
            {
                if($result[2]) {
                    $fields[$result[3]] = [\Swoole\Table::TYPE_STRING, intval($result[2])];
                } elseif($result[1] === 'int') {
                    $fields[$result[3]] = [\Swoole\Table::TYPE_INT];
                } elseif($result[1] === 'float') {
                    $fields[$result[3]] = [\Swoole\Table::TYPE_FLOAT];
                }
            }
        }

        return new static($fields);
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
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->data->set(0, [$key => $value]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return @$this->data->get(0, $key);
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