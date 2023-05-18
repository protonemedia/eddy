<?php

namespace App\Tasks;

enum CallbackType: string
{
    case Custom = 'custom';
    case Timeout = 'timeout';
    case Failed = 'failed';
    case Finished = 'finished';
}
