<?php

namespace Espo\Custom\Tools\ExternalApi\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\ORM\EntityManager;

class Get implements Action
{

    public function __construct(
        EntityManager $entityManager,
    )
    {
        $this->entityManager = $entityManager;
    }

    public function process(Request $request): Response
    {
        $data = $request->getQueryParams();
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];

        if (empty($firstName) || empty($lastName) || empty($email)) {
//            return ResponseComposer::json([
//                'error' => 'Invalid data'
//            ]);
        }

        $startDate = '15-02-2024';
        $endDate = '16-02-2024';

//        $firstName = $entity->get('firstName');
//        $lastName = $entity->get('lastName');
//        $email = $entity->get('email');

        $url = 'https://evds2.tcmb.gov.tr/service/evds/series=TP.DK.USD.A-TP.DK.EUR.A&startDate=' . $startDate . '&endDate=' . $endDate . '&type=json&key=LYhCggxPJW';

        $params = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email
        ];

        $query = http_build_query($params);

        $url = $url . '?' . $query;

        $response = json_decode(file_get_contents($url), true);

        return ResponseComposer::json([
            'url' => $url,
            'response' => $response,
        ]);
    }
}
