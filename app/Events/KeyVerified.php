<?php

namespace App\Events;

use App\Key;


class KeyVerified
{
    /**
     * @var Key
     */
    public $key;

    /**
     * KeyVerified constructor.
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }
}
