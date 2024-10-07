<?php

namespace App\Controller;

use App\Service\Blockchain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BlockchainController extends AbstractController
{
    #[Route('/blocks', name: 'post_block', methods: ['POST'])]
    public function addBlock(Request $request, Blockchain $blockchain): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $block = $blockchain->addBlock(
            $data['action'],
            $data['identifier'],
            $data['author'],
            new \DateTimeImmutable($data['date']),
            $data['metadata']
        );

        return $this->json($block);
    }
}
