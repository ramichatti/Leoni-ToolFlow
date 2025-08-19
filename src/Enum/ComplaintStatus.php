<?php

namespace App\Enum;

enum ComplaintStatus: string
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case RESOLVED = 'RESOLVED';
    case REJECTED = 'REJECTED';
} 