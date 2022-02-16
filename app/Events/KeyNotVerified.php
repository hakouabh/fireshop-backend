<?php

namespace App\Events;

use App\Key;


class KeyNotVerified
{
     /**
     * @var Key
     */
    public $key;

    /**
     * KeyNotVerified constructor.
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }
}
