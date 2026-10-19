<?php

namespace Modules\RedmineIntegration\Services;

use Generator;
use RuntimeException;

final class RedminePaginator
{
    private const PAGE_SIZE = 100;

    /**
     * Yield every item from a Redmine REST collection, respecting the 100-item
     * API cap. Process each page immediately instead of loading all rows.
     *
     * @param callable(int, int): mixed $fetchPage
     * @return Generator<array>
     */
    public static function items(callable $fetchPage, string $collection): Generator
    {
        $offset = 0;

        do {
            $response = $fetchPage($offset, self::PAGE_SIZE);

            if (!is_array($response)
                || !isset($response[$collection], $response['total_count'])
                || !is_array($response[$collection])
                || !is_numeric($response['total_count'])) {
                throw new RuntimeException("Invalid Redmine {$collection} page response");
            }

            $items = $response[$collection];
            $total = (int) $response['total_count'];
            $fetched = count($items);

            if ($fetched === 0 && $offset < $total) {
                throw new RuntimeException("Redmine {$collection} pagination returned an empty page at offset {$offset}");
            }

            foreach ($items as $item) {
                yield $item;
            }

            $offset += $fetched;
        } while ($offset < $total);
    }
}
