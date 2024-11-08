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

    #[Route('/blocks', name: 'get_blocks', methods: ['GET'])]
    public function getBlocks(Request $request, Blockchain $blockchain): JsonResponse
    {
        $page = $request->query->getInt('page', 1);

        $blocks = $blockchain->getBlocks($page);
        $count = $blockchain->countBlocks();

        foreach ($blocks as $block) {
            $blockchain->verifySignature($block);
        }

        return new JsonResponse([
            'blocks' => $blocks,
            'count' => $count,
        ]);
    }

    #[Route('/blocks/validity', name: 'check_blockchain_validity', methods: ['GET'])]
    public function checkBlockchainValidity(Blockchain $blockchain): JsonResponse
    {
        $wrongBlock = $blockchain->checkBlockchainValidity();

        if ($wrongBlock === null) {
            return new JsonResponse(['message' => 'The blockchain is valid.']);
        } else {
            return new JsonResponse(['message' => 'The blockchain is not valid. The block with UUID ' . $wrongBlock->getUuid() . ' is invalid. payload: ' . $wrongBlock->payloadToSign()], 400);
        }
    }
}
