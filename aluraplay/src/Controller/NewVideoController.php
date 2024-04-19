<?php

declare(strict_types=1);

namespace Alura\Mvc\Controller;

use Alura\Mvc\Entity\Video;
use Alura\Mvc\Helper\FlashMessageTrait;
use Alura\Mvc\Repository\VideoRepository;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class NewVideoController implements Controller
{
    use FlashMessageTrait;

    public function __construct(private VideoRepository $videoRepository)
    {
    }

    public function processaRequisicao(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $url = filter_var($queryParams['url'], FILTER_VALIDATE_URL);
        if ($url === false) {
            $this->addErrorMessage("URL inválida");
            return new Response(302, [
                'Location' => '/novo-video',
            ]);
        }
        $titulo = filter_var($queryParams['titulo']);
        if ($titulo === false) {
            $this->addErrorMessage("Título não informado");
            return new Response(302, [
                'Location' => '/novo-video',
            ]);
        }

        $video = new Video($url, $titulo);
        $files = $request->getUploadedFiles();

        /** @var UploadedFileInterface $uploadedImage */
        $uploadedImage = $files['image'];

        if ($uploadedImage->getError() === UPLOAD_ERR_OK) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $tmpFile = $uploadedImage->getStream()->getMetadata('uri');
            $mimeType = $finfo->file($tmpFile);

            if (str_starts_with($mimeType, 'image/')) {
                $safeFileName = uniqid('upload_') . '_' . pathinfo($uploadedImage->getClientFilename(), PATHINFO_BASENAME);
                $uploadedImage->moveTo(__DIR__ .'/../../public/img/uploads/'. $safeFileName);
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    __DIR__ . '/../../public/img/uploads/' . $safeFileName
                );
                $video->setFilePath($safeFileName);
            }
        }

        $success = $this->videoRepository->add($video);
        if ($success === false) {
            $this->addErrorMessage("Erro ao cadastrar vídeo");
            return new Response(302, [
                'Location' => '/novo-video',
            ]);
        } else {
            return new Response(302, [
                'Location' => '/',
            ]);
        }
    }
}
