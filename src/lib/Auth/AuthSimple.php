<?php
namespace SzczecinInTouch\lib\Auth;

class AuthSimple
{
    /** @var string  */
    private $username = '';
    /** @var string  */
    private $hash = '';

    /**
     * AuthSample constructor.
     *
     * @param string $username
     * @param string $hash
     */
    public function __construct(string $username, string $hash)
    {
        $this->username = $username;
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    private function calculateHash(): string
    {
        return hash('sha256', $this->username . ':' . SECRET);
    }

    /**
     * @return bool
     */
    private function checkAuthHash(): bool
    {
        $hash = $this->calculateHash();

        return base64_decode($this->hash) === $hash;
    }

    /**
     * @return bool
     */
    public function auth(): bool
    {
        return $this->checkAuthHash();
    }
}

class AuthException extends \Exception
{

}
