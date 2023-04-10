<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class ExpireAtTest extends TestCase
{
   
    public function testWillExpireAt()
    {
        // Define inputs and expected output
        $due_time = '2023-04-30 18:00:00';
        $created_at = '2023-04-01 14:00:00';
        $expected_output = '2023-04-30 18:00:00';
        // Call the function and check the output
        $this->assertEquals($expected_output, TeHelper::willExpireAt($due_time, $created_at));
    }


}
