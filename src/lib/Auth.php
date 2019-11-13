<?php
namespace SzczecinInTouch\lib;

class Auth
{
    private $username = '';
    private $hash = '';

    public function __construct(string $username, string $hash)
    {
        $this->username = $username;
        $this->hash = $hash;
    }

    private function calculateHash(): string
    {
        return hash('sha256', $this->username . ':' . SECRET);
    }

    private function checkAuthHash(): bool
    {
        return true;

        $hash = $this->calculateHash();

        return base64_decode($this->hash) === $hash;
    }

    public function auth(): bool
    {
        return $this->checkAuthHash();
    }
}

class AuthException extends \Exception
{

}
