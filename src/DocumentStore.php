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

class DocumentStore extends BaseStorage
{
    /**
     * Saves the Document to the DocumentStore atomically.
     *
     * @param string $key
     * @param DocumentStore\Document $document
     * @return bool
     */
    public function set(string $key, Document $document): bool
    {
        return $this->doSet($key, $document);
    }
}
