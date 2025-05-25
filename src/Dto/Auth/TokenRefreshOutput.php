<?php

namespace App\Dto\Auth;

final class TokenRefreshOutput
{
    public string $token;
    public string $refresh_token;

    public function __construct(string $token, string $refresh_token)
    {
        $this->token = $token;
        $this->refresh_token = $refresh_token;
    }
}
