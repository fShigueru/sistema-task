<?php

namespace services;


class Task
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
     * @return mixed
     */
    public function findAll()
    {
        $sql = "SELECT t.id, t.nome as task, t.descricao, u.nome as usuario, u.email, t.prioridade, ts.tipo, ts.nome as status, us.nome as usuario_status, us.email as email_status
                FROM task t 
                LEFT JOIN user u ON t.user_id = u.id 
                LEFT JOIN status_task st ON t.status_id = st.id  
                LEFT JOIN tipo_status ts ON st.tipo_status_id = ts.id
                LEFT JOIN user us ON st.user_id = us.id 
                ORDER BY t.prioridade;";
        return $this->db->fetchAll($sql);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOne($id)
    {
        $sql = "SELECT * FROM task WHERE id = ?;";
        return $this->db->fetchAssoc($sql, [$id]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOneWithUser($id)
    {
        $sql = "SELECT t.id, t.nome as task, t.descricao, u.nome as usuario, u.email, t.prioridade, ts.tipo, ts.nome as status, us.nome as usuario_status, us.email as email_status 
                FROM task t 
                LEFT JOIN user u ON t.user_id = u.id 
                LEFT JOIN status_task st ON t.status_id = st.id  
                LEFT JOIN tipo_status ts ON st.tipo_status_id = ts.id
                LEFT JOIN user us ON st.user_id = us.id 
                WHERE t.id = ?;";
        return $this->db->fetchAssoc($sql, [$id]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOneWithStatus($id)
    {
        $sql = "SELECT t.id, st.user_id, ts.tipo, st.id as status_id FROM task t 
                LEFT JOIN status_task st ON t.status_id = st.id  
                LEFT JOIN tipo_status ts ON st.tipo_status_id = ts.id  
                LEFT JOIN user u ON st.user_id = u.id  
                WHERE t.id = ?;";
        return $this->db->fetchAssoc($sql, [$id]);
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id)
    {
        $this->db->update('task', $data, ['id' => $id]);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        $this->db->delete('task', ['id' => $id]);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data)
    {
        $this->db->insert('task', $data);
        return $this->db->lastInsertId();
    }

    /**
     * @param $tipo
     * @param $user
     * @param $datetime
     * @return mixed
     */
    public function insertStatusTask($tipo, $user, $datetime)
    {
        $data = [
            'user_id' => $user['id'],
            'tipo_status_id' => $tipo['id'],
            'created_at' => $datetime->format('Y-m-d h:m:s'),
            'updated_at' => $datetime->format('Y-m-d h:m:s')
        ];

        $this->db->insert('status_task', $data);
        return $this->db->lastInsertId();
    }

    public function updateStatusTask($taskStatus, $tipo, $user,$datetime)
    {
        $data = [
            'user_id' => $user['id'],
            'tipo_status_id' => $tipo['id'],
            'updated_at' => $datetime->format('Y-m-d h:m:s')
        ];

        $this->db->update('status_task', $data, ['id' => $taskStatus['status_id']]);
    }

    /**
     * @param $tipo
     * @return mixed
     */
    public function findTipoStatus($tipo)
    {
        $sql = "SELECT * FROM tipo_status WHERE tipo = ?;";
        return $this->db->fetchAssoc($sql, [$tipo]);
    }
}