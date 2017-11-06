<?php

namespace services;

class Anexo
{
    private $db;
    private $path = __DIR__.'/../../web/upload/';

    /**
     * Task constructor.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $data
     */
    public function insert($data)
    {
        $this->db->insert('anexo', $data);
    }

    /**
     * @param $files
     * @param $datetime
     * @param $id
     */
    public function uploadLocalFile($files, $datetime, $id)
    {
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $filename = sprintf('%s%s.%s', $datetime->format('hmsu'), $key, $file->guessExtension());
                $file->move($this->path, $filename);
                $data = [
                    'task_id' => $id,
                    'anexo' => $filename,
                    'created_at' => $datetime->format('Y-m-d h:m:s'),
                    'updated_at' => $datetime->format('Y-m-d h:m:s')
                ];
                $this->insert($data);
            }
        }
    }

    /**
     * @param $taskId
     * @return mixed
     */
    public function findAnexos($taskId)
    {
        $sql = "SELECT * FROM anexo WHERE task_id = ?";
        return $this->db->fetchAll($sql, [$taskId]);
    }
}