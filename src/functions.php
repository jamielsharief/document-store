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

use DocumentStore\Document;

/**
 * Sets a Document in the cache
 *
 * @param string $key
 * @param DocumentStore\Document $document
 * @return boolean
 */
function cache_set(string $key, Document $document): bool
{
    $path = sys_get_temp_dir() . '/document-store';
    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }

    $tmpfile = $path . '/' . uniqid();

    if (file_put_contents($tmpfile, $document->toJson(), LOCK_EX)) {
        return rename($tmpfile, $path .  '/' . $key .'.json');
    }

    return false;
}

/**
 * Gets a Document from the cache
 *
 * @param string $key
 * @return DocumentStore\Document|null
 */
function cache_get(string $key): ?Document
{
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.json';

    if (file_exists($filename)) {
        return new Document(
            json_decode(file_get_contents($filename), true)
        );
    }

    return null;
}

/**
 * Checks if the cache has a key
 *
 * @param string $key
 * @return boolean
 */
function cache_has(string $key): bool
{
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.json';

    return file_exists($filename);
}

/**
 * Deletes a Document from the cache
 *
 * @param string $key
 * @return bool
 */
function cache_delete(string $key): bool
{
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.json';
    if (file_exists($filename)) {
        return unlink($filename);
    }

    return false;
}

/**
 * Clears all items from the cache
 *
 * @return void
 */
function cache_clear(): void
{
    $path = sys_get_temp_dir() . '/document-store';

    foreach (scandir($path) as $file) {
        $filename = $path . '/' . $file;
        if (is_file($filename)) {
            unlink($filename);
        }
    }
}
