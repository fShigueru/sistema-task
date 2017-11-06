<?php

namespace services;

class User
{
    private $db;

    /**
     * Task constructor.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function findOneByEmail($email)
    {
        $sql = "SELECT * FROM user WHERE email = ?;";
        return $this->db->fetchAssoc($sql, [$email]);
    }

    /**
     * @param $data
     */
    public function insert($data)
    {
        $this->db->insert('user', $data);
    }
}