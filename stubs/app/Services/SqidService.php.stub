<?php

namespace App\Services;

use Sqids\Sqids;

class SqidService
{
    protected Sqids $sqids;

    public function __construct()
    {
        $alphabet = config('sqids.alphabet');
        $minLength = config('sqids.length', 8);

        $this->sqids = $alphabet
            ? new Sqids(alphabet: $alphabet, minLength: $minLength)
            : new Sqids(minLength: $minLength);
    }

    /**
     * Encode an ID to a UID.
     */
    public function encode(int|string $id): string
    {
        return $this->sqids->encode([(int) $id]);
    }

    /**
     * Decode a UID to an ID.
     */
    public function decode(string $uid): array
    {
        return $this->sqids->decode($uid);
    }
}
