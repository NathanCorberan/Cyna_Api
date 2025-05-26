<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;

final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        // Récupère le path pour /api/carousels
        $pathItem = $openApi->getPaths()->getPath('/api/carousels');
        if ($pathItem) {
            $operation = $pathItem->getPost();
            if ($operation instanceof Operation) {
                // Ajoute la description avec le cURL d’exemple
                $operation = $operation->withDescription(
                    "Exemple cURL :\n".
                    "```bash\n".
                    "curl --location 'http://127.0.0.1:8000/api/carousels' \\\n".
                    "--header 'Authorization: Bearer ...TOKEN...' \\\n".
                    "--form 'imageFile=@/chemin/vers/image.jpg' \\\n".
                    "--form 'panel_order=1'\n".
                    "```"
                );
                // Réinjecte l'opération modifiée
                $pathItem = $pathItem->withPost($operation);
                $openApi->getPaths()->addPath('/api/carousels', $pathItem);
            }
        }

        return $openApi;
    }
}
