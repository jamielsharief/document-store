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

use DocumentStore\Exception\NotFoundException;
use DocumentStore\Exception\DocumentStoreException;

class DocumentStore
{
    /**
     * Path where the collection is saved
     */
    private string $path;

    /**
     * @param string $path path to the collection e.g. __DIR__ . '/users'
     */
    public function __construct(string $path)
    {
        $this->createDirectoryIfNotExists($path);
       
        $this->path = $path;
    }

    /**
     * Gets a document
     *
     * @param string $id
     * @return DocumentStore\Document
     * @throws DocumentStore\NotFoundException
     * @throws DocumentStore\Exception
     */
    public function get(string $id): Document
    {
        $this->checkExists($id);

        $data = json_decode(file_get_contents($this->filename($id)), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DocumentStoreException('JSON decoding data Error: ' . json_last_error());
        }

        return new Document($data);
    }

    /**
     * Saves the Document to the DocumentStore atomically.
     *
     * @param string $key
     * @param DocumentStore\Document $document
     * @return bool
     */
    public function set(string $key, Document $document): bool
    {
        if (strpos($key, '/') !== false) {
            $this->createDirectoryIfNotExists($this->path . '/' .pathinfo($key, PATHINFO_DIRNAME));
        }

        $tmpfile = sys_get_temp_dir() . '/' . uniqid() . '.tmp';

        if (file_put_contents($tmpfile, $document->toJson(['pretty' => true]), LOCK_EX)) {
            return rename($tmpfile, $this->filename($key));
        }

        return false;
    }

    /**
     * Inserts a Document into the DocumentStore with a unique ObjectID, this will be
     * added to the Document and used as the key. On success it will return the ObjectID
     *
     * @param DocumentStore\Document $document
     * @param array $options
     *  - prefix: default:null where to create the Document contacts or europe/contacts
     * @return string|null $objectID
     */
    public function insert(Document $document, array $options = []): ?string
    {
        $options += ['prefix' => null];

        $document->id($this->objectId());

        $path = $options['prefix'] ? trim($options['prefix'], '/') . '/' : null;

        if ($this->set($path . $document->id(), $document)) {
            return $document->id();
        }

        return null;
    }

    /**
     * Deletes a document from the collection
     *
     * @param string $key
     * @return boolean
     * @throws DocumentStore\NotFoundException
     */
    public function delete(string $key): bool
    {
        $this->checkExists($key);

        return unlink($this->filename($key));
    }

    /**
     * Checks if a document exists in a collection
     *
     * @param string $id
     * @return boolean
     */
    public function has(string $id): bool
    {
        return file_exists($this->filename($id));
    }

    /**
     * Lists Documents in the Collection
     *
     * @param string $path
     * @param boolean $recursive
     * @return array
     */
    public function list(string $path = '', bool $recursive = true): array
    {
        if (! is_dir($this->path . '/' . $path)) {
            return [];
        }

        return $this->scandir($this->path . '/' . $path, $recursive);
    }

    /**
     * Generates a 12 byte ID e.g. 5f706fd211a958bede86003e
     *
     * @return string
     */
    public function objectId(): string
    {
        return dechex(time()) . bin2hex(random_bytes(8));
    }

    /**
     * @param string $id
     * @return void
     * @throws DocumentStore\NotFoundException
     */
    private function checkExists(string $id): void
    {
        if (! file_exists($this->filename($id))) {
            throw new NotFoundException('Not found');
        }
    }

    /**
     * Gets the filename for a document
     *
     * @param string $id
     * @return string
     */
    private function filename(string $key): string
    {
        return $this->path . '/' . $key . '.json';
    }

    /**
     * @param string $directory
     * @return void
     */
    private function createDirectoryIfNotExists(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        if (! is_writable($directory)) {
            throw new DocumentStoreException($directory . ' is not writable');
        }
    }

    /**
     * Recursive scans the document location and builds the list
     *
     * @param string $directory
     * @return array
     */
    private function scandir(string $directory, bool $recursive = true): array
    {
        $out = [];
        foreach (array_diff(scandir($directory), ['.', '..']) as $filename) {
            $isDirectory = is_dir($directory . '/' . $filename);
 
            if ($isDirectory && $recursive) {
                $out = array_merge($out, $this->scandir($directory . '/' . $filename));
            }
            if ($isDirectory) {
                continue;
            }
            $filename = ltrim(str_replace($this->path .'/', '', $directory . '/' . $filename), '/');
            if (substr($filename, -5) === '.json') {
                $out[] = substr($filename, 0, -5);
            }
        }

        return $out;
    }
}
