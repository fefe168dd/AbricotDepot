<?php 
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

interface UserRepositoryInterface
{
    public function findbyEmail(string $email): ?array;
    public function findbyPseudo(string $pseudo): ?array;
    public function existsByEmail(string $email): bool;
}