<?php

namespace Acme\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102011632 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $user = $schema->createTable('user');
        $user->addColumn('id', 'integer', ['unsigned' => true, 'nullable' => true, 'autoincrement' => true]);
        $user->addColumn('nome', 'string', ['nullable' => false]);
        $user->addColumn('email', 'string', ['nullable' => false, 'unique' => true]);
        $user->addColumn('created_at','datetime');
        $user->addColumn('updated_at','datetime');
        $user->setPrimaryKey(['id']);

        $tipoStatus = $schema->createTable('tipo_status');
        $tipoStatus->addColumn('id', 'integer', ['unsigned' => true, 'nullable' => false, 'autoincrement' => true]);
        $tipoStatus->addColumn('tipo', 'string', ['length' => 20, 'nullable' => false]);
        $tipoStatus->addColumn('nome', 'string', ['length' => 20, 'nullable' => false]);
        $tipoStatus->setPrimaryKey(['id']);

        $statusTask = $schema->createTable('status_task');
        $statusTask->addColumn('id', 'integer', ['unsigned' => true, 'nullable' => true, 'autoincrement' => true]);
        $statusTask->addColumn('tipo_status_id', 'integer', ['unsigned' => true, 'nullable' => true]);
        $statusTask->addColumn('user_id', 'integer', ['unsigned' => true, 'nullable' => true]);
        $statusTask->addColumn('created_at','datetime');
        $statusTask->addColumn('updated_at','datetime');
        $statusTask->setPrimaryKey(['id']);
        $statusTask->addForeignKeyConstraint($user, ["user_id"], ["id"]);
        $statusTask->addForeignKeyConstraint($tipoStatus, ["tipo_status_id"], ["id"]);

        $task = $schema->createTable('task');
        $task->addColumn('id', 'integer', ['unsigned' => true, 'nullable' => true, 'autoincrement' => true]);
        $task->addColumn('nome', 'string', ['length' => 80, 'nullable' => false]);
        $task->addColumn('descricao', 'text');
        $task->addColumn('prioridade', 'integer', ['length' => 5]);
        $task->addColumn('user_id', 'integer', ['unsigned' => true, 'nullable' => true]);
        $task->addColumn('status_id', 'integer', ['unsigned' => true, 'nullable' => true]);
        $task->addColumn('created_at','datetime');
        $task->addColumn('updated_at','datetime');
        $task->setPrimaryKey(['id']);
        $task->addForeignKeyConstraint($user, ["user_id"], ["id"]);
        $task->addForeignKeyConstraint($statusTask, ["status_id"], ["id"]);

        $anexo = $schema->createTable('anexo');
        $anexo->addColumn('id', 'integer', ['unsigned' => true, 'nullable' => false, 'autoincrement' => true]);
        $anexo->addColumn('anexo', 'text');
        $anexo->addColumn('created_at','datetime');
        $anexo->addColumn('updated_at','datetime');
        $anexo->addColumn('task_id', 'integer', ['unsigned' => true, 'nullable' => true]);
        $anexo->setPrimaryKey(['id']);

        $anexo->addForeignKeyConstraint($task, ["task_id"], ["id"], ["onDelete" => "CASCADE"]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable( 'user' );
        $schema->dropTable( 'tipo_status' );
        $schema->dropTable( 'status_task' );
        $schema->dropTable( 'task' );
        $schema->dropTable( 'anexo' );
    }
}
