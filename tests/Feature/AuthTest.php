<?php

it('registers a user, logs in, and returns a bearer token', function () {
    $registerResponse = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'test102030',
        'password_confirmation' => 'test102030'
    ]);

    $registerResponse->assertStatus(201);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'test102030'
    ]);

    $loginResponse->assertStatus(200);
    $loginResponse->assertJsonStructure(['token']);
});
