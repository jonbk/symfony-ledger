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

        if (!is_array($data)) {
            return new JsonResponse(['message' => 'The request body must be a JSON object.'], 400);
        }

        foreach (['action', 'identifier', 'author', 'date'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
                return new JsonResponse(['message' => sprintf('The "%s" field must be a non-empty string.', $field)], 400);
            }
        }

        foreach (['action', 'identifier', 'author'] as $field) {
            if (mb_strlen($data[$field]) > 255) {
                return new JsonResponse(['message' => sprintf('The "%s" field must not exceed 255 characters.', $field)], 400);
            }
        }

        if (!isset($data['metadata']) || !is_array($data['metadata'])) {
            return new JsonResponse(['message' => 'The "metadata" field must be a JSON object or array.'], 400);
        }

        try {
            $date = new \DateTimeImmutable($data['date']);
        } catch (\Exception) {
            return new JsonResponse(['message' => 'The "date" field must be a valid date.'], 400);
        }

        $block = $blockchain->addBlock(
            $data['action'],
            $data['identifier'],
            $data['author'],
            $date,
            $data['metadata']
        );

        return $this->json($block);
    }

    #[Route('/blocks', name: 'get_blocks', methods: ['GET'])]
    public function getBlocks(Request $request, Blockchain $blockchain): JsonResponse
    {
        $page = $request->query->getInt('page', 1);

        if ($page < 1) {
            return new JsonResponse(['message' => 'The "page" parameter must be greater than or equal to 1.'], 400);
        }

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
}
