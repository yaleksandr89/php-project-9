<?php

function truncate(?string $value, int $limit = 200): string
{
    if ($value === null || $value === '') {
        return '';
    }

    return mb_strlen($value) > $limit
        ? mb_substr($value, 0, $limit) . '...'
        : $value;
}
