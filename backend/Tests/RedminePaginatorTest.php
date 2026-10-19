<?php

namespace Modules\RedmineIntegration\Tests;

use Modules\RedmineIntegration\Services\RedminePaginator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RedminePaginatorTest extends TestCase
{
    public function testIteratesAcrossAllPages(): void
    {
        $requests = [];

        $items = iterator_to_array(RedminePaginator::items(
            static function (int $offset, int $limit) use (&$requests): array {
                $requests[] = [$offset, $limit];
                $ids = range($offset + 1, min(205, $offset + $limit));

                return [
                    'total_count' => 205,
                    'projects' => array_map(static fn (int $id): array => ['id' => $id], $ids),
                ];
            },
            'projects'
        ), false);

        $this->assertCount(205, $items);
        $this->assertSame(205, $items[204]['id']);
        $this->assertSame([[0, 100], [100, 100], [200, 100]], $requests);
    }

    public function testRejectsNonArrayApiResponse(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Redmine projects page response');

        iterator_to_array(RedminePaginator::items(
            static fn (int $offset, int $limit) => false,
            'projects'
        ));
    }

    public function testEmptyCollection(): void
    {
        $items = iterator_to_array(RedminePaginator::items(
            static fn (int $offset, int $limit): array => [
                'total_count' => 0,
                'issues' => [],
            ],
            'issues'
        ));

        $this->assertSame([], $items);
    }

    public function testRefusesToSilentlyTruncateWhenPageIsEmpty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('empty page at offset 0');

        iterator_to_array(RedminePaginator::items(
            static fn (int $offset, int $limit): array => [
                'total_count' => 101,
                'issues' => [],
            ],
            'issues'
        ));
    }
}
