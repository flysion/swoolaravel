<?php

namespace Flysion\Swoolaravel\Swoole;

class HashStore
{
    /**
     * @var \Swoole\Table
     */
    protected $data;

    /**
     * @var
     */
    protected $columns;

    /**
     * @param array $columns
     * @param null|float $conflict_proportion
     * @throws
     */
    public function __construct(array $columns, $conflict_proportion = null)
    {
        $this->data = new \Swoole\Table(1, $conflict_proportion);

        foreach($columns as $name => list($dataType, $length))
        {
            $this->addColumn($name, $dataType, $length);
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
     * @param string $name
     * @param string $dataType
     * @param int|null $length
     * @throws
     */
    protected function addColumn($name, $dataType, $length = null)
    {
        switch ($dataType) {
            case 'bool':
            case 'int':
                $this->data->column($name, \Swoole\Table::TYPE_INT);
                break;
            case 'float':
                $this->data->column($name, \Swoole\Table::TYPE_FLOAT);
                break;
            case 'array':
            case 'string':
                $this->data->column($name, \Swoole\Table::TYPE_STRING, $length);
                break;
            default:
                throw new \Exception("Not suppert data-type: {$dataType}");
        }

        $this->columns[$name]['dataType'] = $dataType;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function unserialize($key, $value)
    {
        $dataType = $this->columns[$key]['dataType'];

        switch ($dataType) {
            case 'int':
            case 'float':
            case 'string':
                return $value;
            case 'bool':
                return $value !== 0;
            case 'array':
                return unserialize($value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function serialize($key, $value)
    {
        $dataType = $this->columns[$key]['dataType'];

        switch ($dataType) {
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'string':
                return strval($value);
            case 'bool':
                return $value ? 1 : 0;
            case 'array':
                return serialize($value);
        }
    }

    /**
     * @param string $key
     * @param int $incrby
     * @return int
     * @throws
     */
    public function incr($key, $incrby = 1)
    {
        return $this->data->incr(0, $key, $incrby);
    }

    /**
     * @param string $key
     * @param int $decrby
     * @return int
     * @throws
     */
    public function decr($key, $decrby = 1)
    {
        return $this->data->decr(0, $key, $decrby);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws
     */
    public function set($key, $value)
    {
        return $this->data->set(0, [
            $key => $this->serialize($key, $value),
        ]);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|false
     * @throws
     */
    public function get($key, $default = null)
    {
        $value = $this->data->get(0, $key);
        if($value === false) {
            return $default;
        }

        return $this->unserialize($key, $value);
    }

    /**
     * @return array
     * @throws
     */
    public function all()
    {
        $data = $this->data->get(0) ?: [];

        foreach($data as $key => $value)
        {
            $data[$key] = $this->unserialize($key, $value);
        }

        return $data;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}