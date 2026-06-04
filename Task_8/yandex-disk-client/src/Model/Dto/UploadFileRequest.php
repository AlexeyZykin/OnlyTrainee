<?php

namespace App\Model\Dto;

class UploadFileRequest
{

    private readonly string $fileName;

    private readonly string $filePath;

    private readonly int $errorCode;

    public function __construct($arrGET, $arrPOST, $arrFILES)
    {
        $this->fileName = trim($arrFILES['filename']['name'] ?? '');
        $this->filePath = $arrFILES['filename']['tmp_name'] ?? '';
        $this->errorCode = $arrFILES['filename']['error'];
    }


    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }


}