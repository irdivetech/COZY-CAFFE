<?php

require_once dirname(__DIR__, 2) . '/config/database.php';

class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
