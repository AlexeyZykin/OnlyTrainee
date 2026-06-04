<?php

namespace App\Controller;

use App\Core\View;
use App\Model\Dto\DeleteFileRequest;
use App\Model\Dto\GetFileRequest;
use App\Model\Dto\UpdateFileRequest;
use App\Model\Dto\UploadFileRequest;
use App\Model\Template;
use App\Service\FileService;
use App\Service\SessionService;
use App\Util\Logger;
use App\Validator\UpdateFileRequestValidator;
use App\Validator\UploadFileRequestValidator;
use Exception;

class FileController
{

    public function __construct(
        private readonly FileService $fileService,
        private readonly View $view,
        private readonly SessionService $sessionService
    )
    {
    }

    public function index(): void
    {
        try {
            $files = $this->fileService->getAll() ?? [];

            $messages = $this->sessionService->getFlashMessages();

            $this->view->render(template: Template::MAIN->value, data: [
                "files" => $files,
                "messages" => $messages
            ]);
        } catch (Exception $e) {
            Logger::log($e->getMessage());

            $this->view->render(template: Template::MAIN->value, data: [
                "files" => [],
                "messages" => ['error' => $e->getMessage()]
            ]);
        }
    }

    public function uploadFile(UploadFileRequest $request): void
    {
        try {
            UploadFileRequestValidator::validate($request);

            $this->fileService->upload($request->getFileName(), $request->getFilePath());

            $this->sessionService->setFlashMessage('success', "Файл успешно загружен");
        } catch (Exception $e) {
            Logger::log($e->getMessage());

            $this->sessionService->setFlashMessage('error', $e->getMessage());
        } finally {
            header("Location:/");
            exit;
        }
    }

    public function deleteFile(DeleteFileRequest $request): void
    {
        try {
            if (empty($request->getFileName())) {
                throw new Exception("Отсутствует название файла для удаления");
            }

            $this->fileService->delete($request->getFileName());

            $this->sessionService->setFlashMessage('success', "Файл успешно удален");
        } catch (Exception $e) {
            Logger::log($e->getMessage());

            $this->sessionService->setFlashMessage('error', $e->getMessage());
        } finally {
            header("Location:/");
            exit;
        }
    }

    public function updateFile(UpdateFileRequest $request): void
    {
        try {
            UpdateFileRequestValidator::validate($request);

            $this->fileService->update(
                oldFileName: $request->getOldFileName(),
                newFilePath: $request->getFilePath()
            );

            $this->sessionService->setFlashMessage('success', "Файл успешно изменен");
        } catch (Exception $e) {
            Logger::log($e->getMessage());

            $this->sessionService->setFlashMessage('error', $e->getMessage());
        } finally {
            header("Location:/");
            exit;
        }
    }

    public function getFile(GetFileRequest $request): void
    {
        try {
            if (empty($request->getFileName())) {
                throw new Exception("Отсутствует файл для просмотра");
            }

            $url2Redirect = $this->fileService->getPreviewLink($request->getFileName());

            header("Location:" . $url2Redirect);
            exit;
        } catch (Exception $e) {
            Logger::log($e->getMessage());

            $this->sessionService->setFlashMessage('error', $e->getMessage());
            header("Location:/");
            exit;
        }
    }

}