<?php

namespace Espo\Custom\EntryPoints;

use Espo\EntryPoints\Pdf as BasePdf;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Util;
use Espo\Entities\Template;
use Espo\Tools\Pdf\Service;

class PdfEx extends BasePdf
{
    private EntityManager $entityManager;
    private Service $service;

    public function __construct(EntityManager $entityManager, Service $service)
    {
        $this->entityManager = $entityManager;
        $this->service = $service;
    }

    public function run(Request $request, Response $response): void
    {
        $ids = $request->getQueryParam('ids');
        $entityType = $request->getQueryParam('entityType');
        $templateId = $request->getQueryParam('templateId');
        $foreignLink = $request->getQueryParam('foreignLink');

        $foreignEntity = null;

        if (!$ids || !$entityType || !$templateId) {
            throw new BadRequest();
        }

        $foreignEntity = $this->entityManager->createEntity($foreignLink);

        $ids = explode(',', $ids);
        $foreignEntity->set('contactsIds', $ids);

        $this->entityManager->saveEntity($foreignEntity);
        $entityId = $foreignEntity->getId();


        $entity = $this->entityManager->getEntityById($foreignLink, $entityId);
        /** @var ?Template $template */
        $template = $this->entityManager->getEntityById(Template::ENTITY_TYPE, $templateId);

        if (!$entity || !$template) {
            throw new NotFound();
        }

        $contents = $this->service->generate($foreignLink, $entityId, $templateId);

        $fileName = Util::sanitizeFileName($entity->get('name') ?? 'unnamed');

        $fileName = $fileName . '.pdf';

        $response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Cache-Control', 'private, must-revalidate, post-check=0, pre-check=0, max-age=1')
            ->setHeader('Pragma', 'public')
            ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($fileName) . '"');

        if (!$request->getServerParam('HTTP_ACCEPT_ENCODING')) {
            $response->setHeader('Content-Length', (string)$contents->getStream()->getSize());
        }

        $response->writeBody($contents->getStream());

        if ($foreignEntity) {
            $this->entityManager->removeEntity($foreignEntity);
        }
    }
}
