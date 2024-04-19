<?php

declare(strict_types=1);

namespace Alura\Mvc\Controller;

use Alura\Mvc\Entity\Video;
use Alura\Mvc\Helper\FlashMessageTrait;
use Alura\Mvc\Repository\VideoRepository;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditVideoController implements RequestHandlerInterface
{
    use FlashMessageTrait;

    public function __construct(private VideoRepository $videoRepository)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $id = filter_var($queryParams['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            $this->addErrorMessage('ID inválido');
            return new Response(302, [
                'Location' => '/',
            ]);
        }

        $url = filter_var($queryParams['url'], FILTER_VALIDATE_URL);
        if ($url === false) {
            $this->addErrorMessage('URL inválida');
            return new Response(302, [
                'Location' => '/',
            ]);
        }
        $titulo = filter_var($queryParams['titulo']);
        if ($titulo === false) {
            $this->addErrorMessage('Título inválido');
            return new Response(302, [
                'Location' => '/',
            ]);
        }

        $video = new Video($url, $titulo);
        $video->setId($id);

        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['image']['tmp_name']);

            if (str_starts_with($mimeType, 'image/')) {
                $safeFileName = uniqid('upload_') . '_' . pathinfo($_FILES['image']['name'], PATHINFO_BASENAME);
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    __DIR__ . '/../../public/img/uploads/' . $safeFileName
                );
                $video->setFilePath($safeFileName);
            }
        }

        $success = $this->videoRepository->update($video);

        if ($success === false) {
            $this->addErrorMessage('Erro ao atualizar vídeo');
            return new Response(302, [
                'Location' => '/',
            ]);
        } else {
            return new Response(302, [
                'Location' => '/',
            ]);
        }
    }
}