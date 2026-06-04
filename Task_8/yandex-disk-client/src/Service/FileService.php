<?php

namespace App\Service;

use App\Core\Config;
use Arhitector\Yandex\Disk;
use Exception;

class FileService
{

    public function __construct(private readonly Disk $disk)
    {
    }

    /**
     * @throws Exception
     */
    public function getAll(): array
    {
        $path = $this->buildResourcePath();
        $appDirectory = $this->disk->getResource($path);

        if (!$appDirectory->has() || !$appDirectory->isDir()) {
            throw new Exception("Каталог приложения недоступен");
        }

        $files = [];
        foreach ($appDirectory->items as $item)
        {
            $files[] = [
                'name' => $item->name ?? 'Без названия',
                'mime_type' => $item->mime_type ?? 'Неизвестный mime_type',
            ];
        }

        return $files;
    }

    public function upload($fileName, $filePath) {
        $path = $this->buildResourcePath($fileName);
        $resource = $this->disk->getResource($path);

        $resource->upload(file_path: $filePath, overwrite: false);
    }

    public function delete($fileName) {
        $path = $this->buildResourcePath($fileName);
        $resource = $this->disk->getResource($path);

        $resource->delete(permanently: true);
    }

    public function update($oldFileName, $newFilePath): bool
    {
        $oldFilePath = $this->buildResourcePath($oldFileName);
        $resource = $this->disk->getResource($oldFilePath);

        return $resource->upload(file_path: $newFilePath, overwrite: true);
    }

    public function getPreviewLink($fileName): string {
        $path = $this->buildResourcePath($fileName);
        $resource = $this->disk->getResource($path);

        return $resource->docviewer;
    }

    private function buildResourcePath(string $fileName = ''): string {
        return Config::getInstance()->getBasePath() . basename($fileName);
    }
}