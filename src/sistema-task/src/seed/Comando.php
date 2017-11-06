<?php

namespace seed;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Comando extends Command
{
    protected $db;

    /**
     * @param $db
     */
    public function db($db)
    {
        $this->db = $db;
    }

    protected function configure()
    {
        $this
            ->setName('sistema:init')
            ->setDescription('Executar a seed para carregar dados no banco de dados')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $retorno = "";
        if ($name == 'seed') {
            $sql = "SELECT * FROM tipo_status;";
            $tipo =  $this->db->fetchAssoc($sql);
            if (empty($tipo)) {
                $dadoAtivo = ['tipo' => 'ativo', 'nome' => 'Ativo'];
                $dadoDone = ['tipo' => 'done', 'nome' => 'Done'];
                $dadoProce = ['tipo' => 'processada', 'nome' => 'Processada'];

                $this->db->insert('tipo_status', $dadoAtivo);
                $this->db->insert('tipo_status', $dadoDone);
                $this->db->insert('tipo_status', $dadoProce);
                $retorno = 'Dados inicializados com sucesso!';
            } else {
                $retorno = "Dados já foram inicializados";
            }

        }
        $output->writeln($retorno);
    }
}