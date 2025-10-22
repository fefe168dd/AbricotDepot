<?php 
namespace abricotdepot\api\provider;
use abricotdepot\core\domain\entities\auth\AuthTokenDTO;
interface AuthProviderInterface
{
    public function signIn(string $email, string $password): ?AuthTokenDTO;
    public function refresh(string $refreshToken): ?AuthTokenDTO;
    public function validateToken(string $accessToken): ?AuthTokenDTO;

}