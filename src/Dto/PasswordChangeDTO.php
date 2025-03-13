<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordChangeDTO
{
    #[Groups(['user:write'])]
    #[Assert\NotBlank(message: "L'ancien mot de passe est obligatoire.")]
    public ?string $last_password = null;

    #[Groups(['user:write'])]
    #[Assert\NotBlank(message: "Le nouveau mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères.")]
    #[Assert\Regex(pattern: "/[A-Z]/", message: "Le mot de passe doit contenir au moins une majuscule.")]
    #[Assert\Regex(pattern: "/[a-z]/", message: "Le mot de passe doit contenir au moins une minuscule.")]
    #[Assert\Regex(pattern: "/[0-9]/", message: "Le mot de passe doit contenir au moins un chiffre.")]
    #[Assert\Regex(pattern: "/[\W]/", message: "Le mot de passe doit contenir au moins un caractère spécial.")]
    public ?string $new_password = null;

    #[Groups(['user:write'])]
    #[Assert\NotBlank(message: "La confirmation du mot de passe est obligatoire.")]
    #[Assert\Expression("this.new_password === this.confirmation_password", message: "Le mot de passe et la confirmation doivent être identiques.")]
    public ?string $confirmation_password = null;
}
