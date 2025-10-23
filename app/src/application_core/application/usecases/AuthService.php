<?php 
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\UserRepositoryInterface;
use abricotdepot\core\domain\entities\auth\AuthServiceInterface;
use abricotdepot\core\domain\entities\auth\UserProfile; 

class AuthService implements AuthServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticateuser(string $username, string $password): ?UserProfile
    {
        $userData = $this->userRepository->findbyPseudo($username);
        if ($userData && password_verify($password, $userData['password_hash'])) {
            return new UserProfile($userData['id'], $userData['username'], $userData['email'], $userData['role'] ?? 'user');
        }
        return null;
    }
    public function authenticateemail(string $email, string $password): ?UserProfile
    {
        $userData = $this->userRepository->findbyEmail($email);
        if ($userData && password_verify($password, $userData['password_hash'])) {
            return new UserProfile($userData['id'], $userData['username'], $userData['email'], $userData['role'] ?? 'user');
        }
        return null;
    }
    public function userExists(string $email): bool
    {
        return $this->userRepository->existsByEmail($email);
    }
}
