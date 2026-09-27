<?php

namespace App\Services;

class Tracking
{
    public static function status(array $states): string
    {
        if ($states === []) {
            return 'awaiting_dropoff';
        }
        if (count(array_unique($states)) === 1 && $states[0] === 'claimed') {
            return 'completed';
        }
        if (in_array('claimed', $states)) {
            return 'partially_claimed';
        }
        if (count(array_unique($states)) === 1 && $states[0] === 'ready') {
            return 'ready';
        }
        if (in_array('ready', $states)) {
            return 'partially_ready';
        }
        if (in_array('processing', $states)) {
            return 'processing';
        }
        if (in_array('received', $states)) {
            return 'received';
        }

        return 'awaiting_dropoff';
    }
}
