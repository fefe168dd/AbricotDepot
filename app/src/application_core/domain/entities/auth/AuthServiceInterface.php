<?php 
namespace abricotdepot\core\domain\entities\auth;

interface AuthServiceInterface
{
    public function authenticateuser(string $username, string $password): ?UserProfile;
    public function authenticateemail(string $email, string $password): ?UserProfile;
    public function userExists(string $email): bool;
}