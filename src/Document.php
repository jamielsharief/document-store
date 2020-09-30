<?php
/**
 * DocumentStore
 * Copyright 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace DocumentStore;

use ArrayAccess;
use Serializable;
use JsonSerializable;

class Document implements ArrayAccess, JsonSerializable, Serializable
{
    /**
     * Holds the documentData
     */
    protected array $documentData = [];

    /**
     * Key used to save this file
     */
    private ?string $key;

    public function __construct(array $data = [], array $options = [])
    {
        $options += ['key' => null];
        $this->documentData = $data;
        $this->key = $options['key'];
    }

    /**
     * Returns the key used to save this Document in the DocumentStore
     *
     * @return string|null
     */
    public function key(): ?string
    {
        return $this->key;
    }

    /**
     * Magic method for classes exported by var_export
     *
     * @param array $data
     * @return self
     */
    public static function __set_state(array $data): self
    {
        return new Self($data);
    }

    /**
     * Magic method for setting data on inaccessible properties.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set(string $property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * Magic method to get data from inaccessible properties.
     *
     * @param string $property
     * @return mixed
     */
    public function &__get(string $property)
    {
        return $this->get($property);
    }

    /**
     * Magic method is triggered by calling isset() or empty() on inaccessible properties.
     *
     * @param string $property
     * @return boolean
     */
    public function __isset(string $property)
    {
        return $this->has($property);
    }

    /**
     * Magic method is triggered by unset on inaccessible properties.
     *
     * @param string $property
     * @return boolean
     */
    public function __unset(string $property)
    {
        $this->unset($property);
    }

    /**
    * Checks if property set and has a non null value
    *
    * @param string $key
    * @return boolean
    */
    public function has(string $key): bool
    {
        return isset($this->documentData[$key]);
    }

    /**
     * Gets a value
     *
     * @param string $key
     * @param mixed $default default value
     * @return mixed
     */
    public function &get(string $key, $default = null)
    {
        $value = $default;
        
        if (isset($this->documentData[$key])) {
            $value = &$this->documentData[$key];
        }

        return $value;
    }

    /**
     * Sets a value or an array of values
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value = null): void
    {
        $data = is_array($key) ? $key : [$key => $value];

        foreach ($data as $key => $value) {
            $this->documentData[$key] = $value;
        }
    }

    /**
    * Deletes a value
    *
    * @param string $key
    * @return boolean
    */
    public function unset(string $key): bool
    {
        if (isset($this->documentData[$key])) {
            unset($this->documentData[$key]);

            return true;
        }

        return false;
    }

    /**
    * Gets or sets the Document ID if used. It uses the _id to not clash
    * with maybe database records which might already have this id field.
    *
    * @param string $id
    * @return string|null
    */
    public function id(string $id = null): ?string
    {
        if ($id) {
            $this->documentData = ['_id' => $id] + $this->documentData;
        }

        return $this->documentData['_id'] ?? null;
    }

    /**
     * Gets this Document as an Array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->documentData;
    }

    /**
     * Returns the Document as JSON
     *
     * @param array $options
     * @return string
     */
    public function toJson(array $options = []): string
    {
        $options += ['pretty' => false];

        return json_encode($this->documentData, $options['pretty'] ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * JsonSerializable Interface for json_encode($store). Returns the properties that will be
     * serialized as JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->documentData;
    }

    /**
     * Serializable Interface
     *
     * @return void
     */
    public function serialize()
    {
        return serialize($this->documentData);
    }

    /**
     * Serializable Interface
     *
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->documentData = unserialize($serialized);
    }

    /**
     * ArrayAcces Interface for isset($store);
     *
     * @param mixed $offset
     * @return bool result
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess Interface for $store[$offset];
     *
     * @param mixed $offset
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess Interface for $store[$offset] = $value;
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess Interface for unset($store[$offset]);
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * Magic method converts this Document to a JSON pretty print
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->documentData, JSON_PRETTY_PRINT);
    }

    /**
     * Magic method called by var_dump
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->documentData;
    }
}
