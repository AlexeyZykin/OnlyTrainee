<?php

namespace App\Validator;

use App\Model\Dto\UpdateFileRequest;
use Exception;

class UpdateFileRequestValidator
{

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function validate(UpdateFileRequest $request): void
    {
        $error = self::getErrorMessageByCode($request->getErrorCode());
        if (!empty($error) || !$request->getOldFileName()) {
            $errorMessage = empty($request->getOldFileName())
                ? "Отсутствует файл для перезаписи"
                : $error;

            throw new Exception($errorMessage);
        }

        $oldFileMime = self::getMimeType($request->getOldFileName());
        $newFileMime = self::getMimeType($request->getFileName());

        if (empty($oldFileMime) || empty($newFileMime)) {
            throw new Exception("Отсутствует mime type");
        }

        if ($oldFileMime !== $newFileMime) {
            throw new Exception("Не совпадают mime тип файлов $oldFileMime - $newFileMime");
        }
    }

    private static function getErrorMessageByCode(int $code): string {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'Слишком большой файл',

            UPLOAD_ERR_NO_FILE => "Файл для загрузки отсутствует",

            UPLOAD_ERR_NO_TMP_DIR => 'Нет временной папки',

            UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_PARTIAL, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Ошибка загрузки файла',

            default => '',
        };
    }


    private static function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'pdf'         => 'application/pdf',
            'doc'         => 'application/msword',
            'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'mp4'         => 'video/mp4',
            'avi'         => 'video/x-msvideo',
            'mov'         => 'video/quicktime',
            'mp3'         => 'audio/mpeg',
            default => '',
        };
    }
}