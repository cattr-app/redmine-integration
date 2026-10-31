<?php

namespace Modules\RedmineIntegration\Tests;

use Illuminate\Support\Carbon;
use Modules\RedmineIntegration\Entities\RedmineGroupUser;
use Tests\TestCase;

class RedmineGroupUserTest extends TestCase
{
    public function test_redmine_user_id_is_cast_to_integer(): void
    {
        $groupUser = new RedmineGroupUser();
        $groupUser->setRawAttributes([
            'redmine_user_id' => '42',
        ]);

        $this->assertSame('integer', $groupUser->getCasts()['redmine_user_id']);
        $this->assertSame(42, $groupUser->redmine_user_id);
    }

    public function test_created_at_uses_eloquent_timestamp_cast(): void
    {
        $groupUser = new RedmineGroupUser();
        $groupUser->setRawAttributes([
            'created_at' => '2026-10-30 12:34:56',
        ]);

        $this->assertInstanceOf(Carbon::class, $groupUser->created_at);
        $this->assertSame('2026-10-30 12:34:56', $groupUser->created_at->format('Y-m-d H:i:s'));
    }
}
