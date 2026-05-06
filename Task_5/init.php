<?php

use Bitrix\Main\Loader;
use Dev\Site\config\AgentsConfig;
use Dev\Site\Handlers\Iblock as IblockHandler;

Loader::includeModule("dev.site");
Loader::includeModule('iblock');

AddEventHandler(
    FROM_MODULE_ID: "iblock",
    MESSAGE_ID: "OnAfterIBlockElementAdd",
    CALLBACK: [IblockHandler::class, "OnAfterIBlockElementAddHandler"]
);

AddEventHandler(
    FROM_MODULE_ID: "iblock",
    MESSAGE_ID: "OnAfterIBlockElementUpdate",
    CALLBACK: [IblockHandler::class, "OnAfterIBlockElementUpdateHandler"]
);


$existingAgent = CAgent::GetList(
    [],
    [
        'MODULE_ID' => 'dev.site',
        'NAME' => AgentsConfig::CLEAR_OLD_LOGS_AGENT_NAME
    ]
)->Fetch();
if (!$existingAgent) {
    CAgent::AddAgent(
        name: AgentsConfig::CLEAR_OLD_LOGS_AGENT_NAME,
        module: 'dev.site',
        interval: 3600,
    );
}

