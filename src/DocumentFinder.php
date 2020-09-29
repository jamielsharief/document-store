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

use DocumentStore\Exception\DocumentStoreException;

class DocumentFinder
{
    private $conditions = [];

    public function __construct(array $conditions)
    {
        $this->conditions = $this->parseConditions($conditions);
    }

    /**
     * Converts a SQL likes statement to regex
     *
     * @example a%, %a, %or%, _r%, a_%, a__%, a%o
     *
     * @param string $like e.g a__de, a%, %a, %or%, _r%, a_%, a__%, a%o
     * @return string $regex e.g. /^.*bc$/1
     */
    private function likeToRegex(string $like): string
    {
        $like = str_replace('_', '.', $like);

        return '/^' .str_replace('%', '.*', $like) . '$/';
    }

    /**
     * Asserts that a document matches the conditions
     *
     * @param DocumentStore\Document   $document
     * @param array $conditions
     * @return boolean
     */
    public function assertConditions(Document $document): bool
    {
        foreach ($this->conditions as $condition) {
            if (! $this->assertCondition($document, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Asserts an indivdual condition against a Document
     *
     * @param DocumentStore\Document $document
     * @param array $condition
     * @return boolean
     */
    private function assertCondition(Document $document, array $condition): bool
    {
        $value = $this->getValue($condition['field'], $document->toArray());
        
        switch ($condition['operator']) {
            case '=':
            case 'IN':
                if (is_array($value)) {
                    if (is_array($condition['value'])) {
                        // $conditions = ['addresses.street' => ['4646 Malibu Drive']];
                        foreach ($value as $v) {
                            if (in_array($v, $condition['value'])) {
                                return true;
                            }
                        }

                        return false;
                    } elseif (! is_array($condition['value'])) {
                        return in_array($condition['value'], $value);
                    }
                } elseif (is_array($condition['value'])) {
                    return in_array($value, $condition['value']);
                }

                return $value === $condition['value'];
                break;
            case '!=':
            case 'NOT IN':
                if (is_array($value)) {
                    if (is_array($condition['value'])) {
                        // $conditions = ['addresses.street' => ['4646 Malibu Drive']];
                        foreach ($value as $v) {
                            if (in_array($v, $condition['value'])) {
                                return false;
                            }
                        }

                        return true;
                    } elseif (! is_array($condition['value'])) {
                        return ! in_array($condition['value'], $value);
                    }
                } elseif (is_array($condition['value'])) {
                    return ! in_array($value, $condition['value']);
                }

                return $value !== $condition['value'];
                break;
                /**
                 * These operators don't work with arrays since it wont make sense
                 */
            case '>':
                if (is_numeric($condition['value']) && is_numeric($value)) {
                    return $value > $condition['value'];
                }
                break;
            case '>=':
                if (is_numeric($condition['value']) && is_numeric($value)) {
                    return $value >= $condition['value'];
                }
                break;
            case '<':
                if (is_numeric($condition['value']) && is_numeric($value)) {
                    return $value < $condition['value'];
                }
                break;
            case '<=':
                if (is_numeric($condition['value']) && is_numeric($value)) {
                    return $value <= $condition['value'];
                }
                break;

            case 'LIKE':
                $value = (array) $value;
                foreach ($value as $check) {
                    if (preg_match($condition['value'], $check)) {
                        return true;
                    }
                }

                return false;
            break;

            case 'NOT LIKE':
                $value = (array) $value;
                foreach ($value as $check) {
                    if (preg_match($condition['value'], $check)) {
                        return false;
                    }
                }

                return true;
            break;
        }

        return false;
    }

    /**
     * Checks if an array is non-associative array
     *
     * @param array $data
     * @return boolean
     */
    private function isNumericalArray(array $data): bool
    {
        return ctype_digit(implode('', array_keys($data)));
    }

    /**
     * Parse the conditions array used by find
     *
     * @param array $conditions
     * @return array
     */
    private function parseConditions(array $conditions): array
    {
        $out = [];

        foreach ($conditions as $key => $value) {
            if (strpos($key, ' ') === false) {
                $field = $key;
                $expression = '=';
            } else {
                list($field, $expression) = explode(' ', $key, 2); //['id !=' => 1]
            }
            if (! in_array($expression, ['=', '!=', '>', '<', '>=', '<=','IN','NOT IN','LIKE','NOT LIKE'])) {
                throw new DocumentStoreException('Invalid operator ' . $expression);
            }
            if (in_array($expression, ['LIKE','NOT LIKE'])) {
                if (! is_string($value)) {
                    throw new DocumentStoreException('None string value for LIKE/NOT LIKE');
                }
                $value = $this->likeToRegex($value);
            }
          
            $out[] = [
                'field' => $field,
                'operator' => $expression,
                'value' => $value
            ];
        }

        return $out;
    }

    /**
     * Gets the value for a field from the document
     *
     * @param string $field
     * @param array $document
     * @return mixed
     */
    private function getValue(string $field, array $document)
    {
        if (strpos($field, '.') === false) {
            return $document[$field] ?? null;
        }
        foreach (explode('.', $field) as $key) {
            $isNumericalArray = is_array($document) && $this->isNumericalArray($document);
            if (! array_key_exists($key, $document) && ! $isNumericalArray) {
                return null;
            }

            /**
             * Lets say item hasMany addresses, you can search like this
             * addresses.street. Only works with = !=
             */
            if ($isNumericalArray) {
                $values = [];
                foreach ($document as $r) {
                    $value = $this->getValue($key, $r);
                    if ($value !== null) {
                        $values[] = $value;
                    }
                }

                return ! empty($values) ? $values : null;
            }
            $document = $document[$key];
        }

        return $document;
    }

    /**
     * Returns the parsed conditions
     *
     * @return array
     */
    public function conditions(): array
    {
        return $this->conditions;
    }
}
