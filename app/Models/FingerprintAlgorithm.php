<?php

namespace App\Models;

enum FingerprintAlgorithm: string
{
    case Md5 = 'md5';
    case Sha256 = 'sha256';
}
