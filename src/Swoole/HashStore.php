<?php

namespace Flysion\Swoolaravel\Swoole;

class HashStore implements \ArrayAccess
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
     * @param null|float $conflictProportion
     * @throws
     */
    public function __construct(array $columns, $conflictProportion = 0.2)
    {
        $this->data = new \Swoole\Table(1, $conflictProportion);

        foreach($columns as $name => list($dataType, $length))
        {
            $this->addColumn($name, $dataType, $length);
        }

        $this->data->create();
    }

    /**
     * @param array $data
     * @param float $conflictProportion
     * @return HashStore
     */
    public static function createFromData(array $data, $conflictProportion = 0.2)
    {
        $columns = [];
        foreach($data as $key => $value)
        {
            $type = gettype($value);
            $length = $type === 'string' ? strlen($value) * 4 : null;

            $columns[$key] = [$type, $length];
        }

        $instance = new static($columns, $conflictProportion);

        foreach($data as $key => $value)
        {
            $instance->set($key, $value);
        }

        return $instance;
    }

    /**
     * @param string $name
     * @param string $dataType
     * @param int|null $length
     * @throws
     */
    private function addColumn($name, $dataType, $length = null)
    {
        switch ($dataType) {
            case 'boolean':
            case 'integer':
                $this->data->column($name, \Swoole\Table::TYPE_INT);
                break;
            case 'double':
                $this->data->column($name, \Swoole\Table::TYPE_FLOAT);
                break;
            case 'string':
                $this->data->column($name, \Swoole\Table::TYPE_STRING, $length);
                break;
            default:
                throw new \Exception("Not support data-type: {$dataType}");
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
            case 'integer':
            case 'double':
            case 'string':
                return $value;
            case 'boolean':
                return $value === 1;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function serialize($key, $value)
    {
        $dataType = $this->columns[$key]['dataType'];

        switch ($dataType) {
            case 'integer':
                return intval($value);
            case 'double':
                return doubleval($value);
            case 'string':
                return strval($value);
            case 'boolean':
                return $value ? 1 : 0;
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
    public function toArray()
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

    /**
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return $this->data->get(0, $offset) !== false;
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     * @throws
     */
    public function offsetUnset($offset)
    {
        throw new \Exception("Dont unset \"{$offset}\"");
    }
}