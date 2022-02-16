<?php

namespace App\Events;
use App\Key;


class KeyActivated
{
    /**
     * @var Key
     */
    public $key;

    /**
     * KeyActivated constructor.
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }
}
