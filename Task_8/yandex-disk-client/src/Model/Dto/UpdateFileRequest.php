<?php

namespace App\Model\Dto;

class UpdateFileRequest
{

    private readonly string $oldFileName;

    private readonly string $fileName;

    private readonly string $filePath;

    private readonly int $errorCode;

    public function __construct($arrGET, $arrPOST, $arrFILES)
    {
        $this->oldFileName = trim($arrPOST['old_filename'] ?? '');
        $this->fileName = trim($arrFILES['new_filename']['name'] ?? '');
        $this->filePath = $arrFILES['new_filename']['tmp_name'];
        $this->errorCode = $arrFILES['new_filename']['error'];
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getOldFileName(): string
    {
         return $this->oldFileName;
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