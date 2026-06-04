<?php

namespace App\Model;

enum LogLevel: string {

    case INFO = "INFO";

    case ERROR = "ERROR";

    case WARN = "WARN";

    case DEBUG = "DEBUG";

}