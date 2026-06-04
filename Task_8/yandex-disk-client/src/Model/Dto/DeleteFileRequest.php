<?php

namespace App\Model\Dto;


class DeleteFileRequest
{

    private readonly string $fileName;

    public function __construct($arrGET, $arrPOST, $arrFILES)
    {
        $this->fileName = trim($arrPOST['filename'] ?? '');
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

}