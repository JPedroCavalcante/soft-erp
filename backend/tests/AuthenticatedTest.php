<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait AuthenticatedTest
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticate();
    }

    protected function authenticate(): void
    {
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function authenticatedHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
        ];
    }

    public function getJson($uri, array $headers = [], $options = 0)
    {
        return parent::getJson($uri, array_merge($this->authenticatedHeaders(), $headers), $options);
    }

    public function postJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        return parent::postJson($uri, $data, array_merge($this->authenticatedHeaders(), $headers), $options);
    }

    public function putJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        return parent::putJson($uri, $data, array_merge($this->authenticatedHeaders(), $headers), $options);
    }

    public function deleteJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        return parent::deleteJson($uri, $data, array_merge($this->authenticatedHeaders(), $headers), $options);
    }
}
