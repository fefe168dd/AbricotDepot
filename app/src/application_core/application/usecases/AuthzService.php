<?php 
namespace abricotdepot\core\application\usecases;
use abricotdepot\core\domain\entities\auth\AuthzServiceInterface;
use abricotdepot\core\domain\entities\auth\UserProfile;
use abricotdepot\core\domain\exceptions\AuthorizationException;

class AuthzService implements AuthzServiceInterface
{
      public function isAuthorized(UserProfile $user, string $resource, string $action): void
    {
        // Implement your authorization logic here
    }

}