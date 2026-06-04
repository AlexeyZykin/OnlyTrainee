<?php

namespace App\Validator;

use App\Model\Dto\UploadFileRequest;
use Exception;

class UploadFileRequestValidator
{
    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function validate(UploadFileRequest $request): void
    {
        $error = self::getErrorMessageByCode($request->getErrorCode());
        if (!empty($error)) {
            throw new Exception($error);
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

}