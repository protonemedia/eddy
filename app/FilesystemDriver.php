<?php

namespace App;

enum FilesystemDriver: string
{
    case S3 = 's3';
    case FTP = 'ftp';
    case SFTP = 'sftp';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::S3 => 'S3',
            self::FTP => 'FTP',
            self::SFTP => 'SFTP',
        };
    }
}
