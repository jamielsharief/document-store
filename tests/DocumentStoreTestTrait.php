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
namespace DocumentStore\Test;

trait DocumentStoreTestTrait
{
    protected function setUp(): void
    {
        if (! is_dir($this->storage_path('books'))) {
            mkdir($this->storage_path('books'), 0775, true);
        }
    }
}
