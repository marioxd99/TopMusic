<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class PublishController
{

    public function publish(HubInterface $hub): Response
    {
        $update = new Update(
            'https:localhost:8000/show',
            json_encode(['status' => 'OutOfStock'])
        );

        $hub->publish($update);

        return new Response('Published');
    }
}