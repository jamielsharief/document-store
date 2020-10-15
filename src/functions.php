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
 * This has been added to Master branch but not included in the release, this would be configured
 * in composer.tmp, however I am currently undecided if this should be added or not since the original
 * concept was to use var_export, but it was actually slower than this, at least on small objects.
 *
 * Benchmarks on a 2MB JSON file, here is example
 *
 * DocumentStore\Document Object
 * (
 *     [meta] => Array
 *         (
 *             [name] => S&P 500 SPDR
 *             [symbol] => SPY
 *             [exchange] => ARCX
 *             [country_code] => US
 *             [currency] => USD
 *             [type] => stock
 *         )
 *
 *     [history] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [date] => 1993-01-29
 *                     [open] => 43.9687
 *                     [high] => 43.9687
 *                     [low] => 43.75
 *                     [close] => 43.9375
 *                     [volume] => 1003200
 *                     [adj_open] => 26.1978
 *                     [adj_high] => 26.1978
 *                     [adj_low] => 26.0675
 *                     [adj_close] => 26.1792
 *                     [adj_volume] => 1003200
 *                 )
 *
 * Using DocumentStore set/get
 * write: 0.035
 * read: 0.018
 *
 * Serialize
 * write: 0.070
 * read: 0.011
 *
 * PHP (var dump)
 * write:  0.090
 * read: 0.037
 *
 * JSON
 * write: 0.30
 * read: 0.17
 *
 * Serialize Data Only (best caching read/write)
 * write: 0.028
 * read: 0.012
 *
 * Conclusion: serializing only data offers faster read/write than any other cache, and a faster read time than using
 * DocumentStore. In test 50% faster.
 */

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

    if (file_put_contents($tmpfile, serialize($document->toArray()), LOCK_EX)) {
        return rename($tmpfile, $path .  '/' . $key .'.tmp');
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
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.tmp';

    if (file_exists($filename)) {
        return new Document(unserialize(file_get_contents($filename)));
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
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.tmp';

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
    $filename = sys_get_temp_dir() . '/document-store/' . $key .'.tmp';
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
