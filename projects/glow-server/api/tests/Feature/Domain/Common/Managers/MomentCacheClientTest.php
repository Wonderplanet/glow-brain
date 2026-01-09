<?php

namespace Feature\Domain\Common\Managers;

use App\Domain\Common\Managers\Cache\MomentoCacheClient;
use Tests\TestCase;

class MomentCacheClientTest extends TestCase
{
    public function test_prepareMomentoZAddArguments_正しく変換できる()
    {
        // Setup

        // Exercise
        $method = new \ReflectionMethod(MomentoCacheClient::class, 'prepareMomentoZAddArguments');
        $method->setAccessible(true);
        [$firstScore, $more_scores_and_mems] = $method->invoke(null, ['member1' => 1, 'member2' => 2, 'member3' => 3]);
        
        // Verify
        $this->assertSame(1.0, $firstScore);
        $this->assertSame(['member1', 2.0, 'member2', 3.0, 'member3'], $more_scores_and_mems);
    }

    public function test_prepareMomentoZAddArguments_同一のスコアでも正しく変換できる()
    {
        // Setup

        // Exercise
        $method = new \ReflectionMethod(MomentoCacheClient::class, 'prepareMomentoZAddArguments');
        $method->setAccessible(true);
        [$firstScore, $more_scores_and_mems] = $method->invoke(null, ['member1' => 1, 'member2' => 1, 'member3' => 1]);

        // Verify
        $this->assertSame(1.0, $firstScore);
        $this->assertSame(['member1', 1.0, 'member2', 1.0, 'member3'], $more_scores_and_mems);
    }
}
