<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class CreateOrUpdateUserTest extends TestCase
{

public function testCreateOrUpdate()
    {
        // Arrange
        $user = new User();
        $request = [
            'role' => 'customer',
            'name' => 'John Doe',
            'company_id' => '1',
            'department_id' => '2',
            'email' => 'johndoe@example.com',
            'dob_or_orgid' => '1990-01-01',
            'phone' => '1234567890',
            'mobile' => '9876543210',
            'password' => 'password123',
            'consumer_type' => 'paid',
            'customer_type' => 'regular',
            'username' => 'johndoe',
            'post_code' => '12345',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'town' => 'Anytown',
            'country' => 'US',
            'reference' => 'yes',
            'additional_info' => 'Additional info',
            'cost_place' => 'Cost place',
            'fee' => 'Fee',
            'time_to_charge' => 'Time to charge',
            'time_to_pay' => 'Time to pay',
            'charge_ob' => 'Charge OB',
            'customer_id' => '123',
            'charge_km' => 'Charge KM',
            'maximum_km' => 'Maximum KM',
            'translator_ex' => [1, 2, 3],
            'translator_type' => 'Medical',
            'worked_for' => 'yes',
            'organization_number' => 'ABC123',
            'gender' => 'Male',
            'translator_level' => 'Advanced',
            'additional_info' => 'Additional info',
            'post_code' => '12345',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'town' => 'Anytown',
            'country' => 'US',
        ];

        // Act
        $result = $user->createOrUpdate(null, $request);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('customer', $result->user_type);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals(1, $result->company_id);
        $this->assertEquals(2, $result->department_id);
        $this->assertEquals('johndoe@example.com', $result->email);
        $this->assertEquals('1990-01-01', $result->dob_or_orgid);
        $this->assertEquals('1234567890', $result->phone);
        $this->assertEquals('9876543210', $result->mobile);
        $this->assertTrue(Hash::check('password123', $result->password));
}


}

