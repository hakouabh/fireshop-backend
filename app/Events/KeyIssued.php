<?php

namespace App\Events;
use App\Key;


class KeyIssued
{
    /**
     * @var Key
     */
    public $key;

    /**
     * KeyIssued constructor.
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }
}
