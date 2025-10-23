<?php 
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\UserRepositoryInterface;
use abricotdepot\core\domain\entities\auth\UserProfile;
use abricotdepot\core\domain\exceptions\AuthenticationException;
use Ramsey\Uuid\Uuid;

class CreateUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Crée un nouveau compte utilisateur
     * 
     * @param string $username Le nom d'utilisateur
     * @param string $email L'adresse email
     * @param string $password Le mot de passe en clair
     * @return UserProfile Le profil utilisateur créé
     * @throws AuthenticationException Si la création échoue
     */
    public function execute(string $username, string $email, string $password): UserProfile
    {
        // Validation des données
        $this->validateInput($username, $email, $password);

        // Vérifier si l'email existe déjà
        if ($this->userRepository->existsByEmail($email)) {
            throw new AuthenticationException('Un compte avec cet email existe déjà');
        }

        // Vérifier si le username existe déjà
        if ($this->userRepository->existsByUsername($username)) {
            throw new AuthenticationException('Ce nom d\'utilisateur est déjà pris');
        }

        // Hasher le mot de passe
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        if ($passwordHash === false) {
            throw new AuthenticationException('Erreur lors du hashage du mot de passe');
        }

        // Générer un UUID pour le nouvel utilisateur
        $userId = Uuid::uuid4()->toString();

        // Créer l'utilisateur
        $success = $this->userRepository->create($userId, $username, $email, $passwordHash);

        if (!$success) {
            throw new AuthenticationException('Erreur lors de la création du compte');
        }

        // Retourner le profil utilisateur créé
        return new UserProfile($userId, $username, $email, 'user');
    }

    /**
     * Valide les données d'entrée
     */
    private function validateInput(string $username, string $email, string $password): void
    {
        // Validation du username
        if (empty(trim($username))) {
            throw new AuthenticationException('Le nom d\'utilisateur est requis');
        }

        if (strlen($username) < 3) {
            throw new AuthenticationException('Le nom d\'utilisateur doit contenir au moins 3 caractères');
        }

        if (strlen($username) > 50) {
            throw new AuthenticationException('Le nom d\'utilisateur ne peut pas dépasser 50 caractères');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new AuthenticationException('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores');
        }

        // Validation de l'email
        if (empty(trim($email))) {
            throw new AuthenticationException('L\'email est requis');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthenticationException('Format d\'email invalide');
        }

        if (strlen($email) > 100) {
            throw new AuthenticationException('L\'email ne peut pas dépasser 100 caractères');
        }

        // Validation du mot de passe
        if (empty($password)) {
            throw new AuthenticationException('Le mot de passe est requis');
        }

        if (strlen($password) < 8) {
            throw new AuthenticationException('Le mot de passe doit contenir au moins 8 caractères');
        }

        if (strlen($password) > 255) {
            throw new AuthenticationException('Le mot de passe ne peut pas dépasser 255 caractères');
        }

        
    }
}
