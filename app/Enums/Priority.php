<?php

namespace App\Enums;

enum Priority: string
{
    case LOW = 'laag';
    case MEDIUM = 'middel';
    case HIGH = 'hoog';
}