<?php 

namespace abricotdepot\core\domain\entities\auth;

interface AuthzServiceInterface
{
    public function isAuthorized(UserProfile $user, string $resource, string $action): void;
}
