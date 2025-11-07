<?php

namespace Models;

use Core\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $timestamps = false;

    public function incrementDownload($reportId)
    {
        $this->query("UPDATE reports SET download_count = download_count + 1 WHERE id = ?", [$reportId]);
    }
}
