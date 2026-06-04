<?php

namespace App\Model\Dto;

class GetFileRequest
{
    private readonly string $fileName;

    public function __construct($arrGET, $arrPOST, $arrFILES)
    {
        $this->fileName = trim($arrGET['filename'] ?? '');
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

}