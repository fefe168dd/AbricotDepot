<?php 

namespace abricotdepot\core\domain\entities\auth;

class UserProfile
{
    private string $id;
    private string $name;
    private string $email;
    private string $role;

    public function __construct(string $id, string $name, string $email, string $role = 'user')
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role
        ];
    }
}
