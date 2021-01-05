<?php
/**
 * DocumentStore
 * Copyright 2020-2021 Jamiel Sharief.
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

/**
 * An adapter which converts the DocumentStore into a Document Database
 */
class DocumentDatabase extends BaseStorage
{
    /**
    * Inserts a Document into the DocumentStore with a unique ObjectID, this will be
    * added to the Document and used as the key. On success it will return the ObjectID
    *
    * @param DocumentStore\Document $document
    * @param array $options
    *  - prefix: default:null where to create the Document contacts or europe/contacts
    * @return bool
    */
    public function insert(Document $document, array $options = []): bool
    {
        $options += ['prefix' => null];

        if (empty($document->_id)) {
            $document->_id($this->objectId());

            $path = $options['prefix'] ? trim($options['prefix'], '/') . '/' : null;
    
            return $this->doSet($path . $document->_id(), $document);
        }

        return false;
    }

    /**
     * Inserts multiple Documents into the DocumentDatabase
     *
     * @param array $documents
     * @param array $options
     * @return bool
     */
    public function insertMany(array $documents, array $options = []): bool
    {
        $out = [];
        foreach ($documents as $document) {
            if (! $document instanceof Document) {
                throw new DocumentStoreException('Invalid Document');
            }
            if (! $this->insert($document, $options)) {
                throw new DocumentStoreException('Error saving Document');
            }
            $out[] = $document->_id;
        }

        return ! empty($out);
    }

    /**
     * Updates an existing Document in the DocumentDatabase
     *
     * @param \DocumentStore\Document $document
     * @return boolean
     */
    public function update(Document $document): bool
    {
        if (! empty($document->_id)) {
            return $this->doSet($document->_id, $document);
        }

        return false;
    }

    /**
     * Updates multiple documents, all Documents must have an ID or this fail
     *
     * @param array $documents
     * @return boolean
     */
    public function updateMany(array $documents): bool
    {
        $out = [];
        foreach ($documents as $document) {
            if (! $document instanceof Document) {
                throw new DocumentStoreException('Invalid Document');
            }
            if (empty($document->_id) || ! $this->doSet($document->_id, $document)) {
                throw new DocumentStoreException('Error updating Document');
            }
            $out[] = $document->_id;
        }

        return ! empty($out);
    }
}
