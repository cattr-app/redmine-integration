<?php

namespace Modules\RedmineIntegration\Tests;

use Modules\RedmineIntegration\Console\GenerateSignature;
use Tests\TestCase;

class GeneratesRequestSignatureTest extends TestCase
{
    /**
     * Generate signature test
     *
     * @return void
     * @throws \Exception
     */
    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testGeneratesSignature()
    {
        $testKey = base64_encode(random_bytes(16));
        $env = $this->app->environmentFilePath();

        $command = $this->getMockBuilder(GenerateSignature::class)
            ->onlyMethods([
                'generateRandomKey',
                'getEnvFilePath'
            ])
            ->getMock();
        $command->method('generateRandomKey')->willReturn($testKey);
        $command->method('getEnvFilePath')->willReturn($env);

        $this->app->instance(GenerateSignature::class, $command);

        $this->artisan('redmine:plugin:signature-generate');

        $this->assertEquals($testKey, config('redmineintegration.request.signature'));

    }
}
