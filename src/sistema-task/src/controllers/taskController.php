<?php

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

$task = $app['controllers_factory'];

$task->get('/new', function () use ($app) {
    return  $app['twig']->render('task/create.html.twig');
})->bind('task.new');

$task->post('/create', function (Request $request) use ($app) {
    $data = $request->request->all();

    $valida = validaTask($app['validator'], $data);
    if (!empty($valida)) {
        return  $app['twig']->render('task/create.html.twig', ['error' => $valida]);
    }

    try {
        $datetime = new \DateTime();
        $user = $app['service.user']->findOneByEmail($app['user']->getEmail());
        $tipoStatus = $app['service.task']->findTipoStatus('ativo');
        $statusId = $app['service.task']->insertStatusTask($user, $tipoStatus, $datetime);

        $user = $app['service.user']->findOneByEmail($app['user']->getEmail());
        $data['user_id'] = $user['id'];
        $data['status_id'] = $statusId;
        $data['created_at'] = $datetime->format('Y-m-d h:m:s');
        $data['updated_at'] = $datetime->format('Y-m-d h:m:s');

        $id = $app['service.task']->insert($data);

    } catch (\Exception $e) {
        return  $app['twig']->render('task/create.html.twig', ['error' => "Erro ao criar uma task"]);
    }

    $files = $request->files->get('anexos');
    $app['service.anexo']->uploadLocalFile($files, $datetime, $id);
    return $app->redirect('/task');
})->bind('task.store');

$task->get('/', function () use ($app) {
    return  $app['twig']->render('task/list.html.twig', [
        'tasks' => $app['service.task']->findAll()
    ]);
})->bind('task.list');

$task->get('/edit/{id}', function ($id) use ($app) {
    $task = $app['service.task']->findOne($id);
    if(!$task){
        $app->abort(404, "Task não encontrada!");
    }

    return $app['twig']->render('task/edit.html.twig', ['task' => $task]);
})->bind('task.edit');

$task->get('/show/{id}', function ($id) use ($app) {
    $task = $app['service.task']->findOneWithUser($id);
    $anexos = $app['service.anexo']->findAnexos($task['id']);
    if(!$task){
        $app->abort(404, "Task não encontrada!");
    }
    return $app['twig']->render('task/show.html.twig', ['task' => $task, 'anexos' => $anexos]);
})->bind('task.show');

$task->post('/edit/{id}', function (Request $request, $id) use ($app) {
    $task = $app['service.task']->findOne($id);
    if(!$task){
        $app->abort(404, "Task não encontrada!");
    }

    $data = $request->request->all();
    $app['service.task']->update($data, $id);

    $datetime = new \DateTime();
    $files = $request->files->get('anexos');
    $app['service.anexo']->uploadLocalFile($files, $datetime, $id);
    return $app->redirect(sprintf('/task/show/%s', $id));
})->bind('task.update');

$task->get('/delete/{id}', function ($id) use ($app) {
    $task = $app['service.task']->findOne($id);
    if(!$task){
        $app->abort(404, "Task não encontrada!");
    }
    $app['service.task']->delete($id);
    return $app->redirect('/task/');
})->bind('task.delete');

$task->post('/done', function (Request $request) use ($app) {
    $data = $request->request->all();
    $id = null;
    if (!empty($data)) {
        $id = str_replace('done-', '', $data);
    }

    $taskStatus = $app['service.task']->findOneWithStatus($id['id']);
    $user = $app['service.user']->findOneByEmail($app['user']->getEmail());
    $tipoStatus = $app['service.task']->findTipoStatus('done');
    if ($taskStatus['tipo'] == 'done') {
        return $app->json(['status_sucesso' => false, 'message' => 'Essa Task já esta marcada como Done']);
    }

    $datetime = new \DateTime();
    $app['service.task']->updateStatusTask($taskStatus, $tipoStatus, $user, $datetime);

    return $app->json(['status_sucesso' => true, 'message' => 'Status Alterado com sucesso', 'status' => $tipoStatus['nome']]);

})->bind('task.done');

$task->post('/submeter', function (Request $request) use ($app) {
    $data = $request->request->all();
    $id = null;
    if (!empty($data)) {
        $id = str_replace('done-', '', $data);
    }

    $taskStatus = $app['service.task']->findOneWithStatus($id['id']);
    $user = $app['service.user']->findOneByEmail($app['user']->getEmail());
    $tipoStatus = $app['service.task']->findTipoStatus('processada');
    if ($taskStatus['tipo'] == 'done') {
        return $app->json(['status_sucesso' => false, 'message' => 'Essa Task já esta marcada como Done']);
    }

    $datetime = new \DateTime();
    $app['service.task']->updateStatusTask($taskStatus, $tipoStatus, $user, $datetime);

    return $app->json(['status_sucesso' => true, 'message' => 'Task Submetida com sucesso', 'status' => $tipoStatus['nome']]);

})->bind('task.submeter');

return $task;

function validaTask($validator, $data)
{
    $constraint = new Assert\Collection(array(
        'nome' => new Assert\NotBlank(),
        'descricao' => new Assert\NotBlank(),
        'prioridade' => [new Assert\NotBlank(), new Assert\Length(array('max' => 5))],
    ));
    $errors = $validator->validate($data, $constraint);
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $input = null;
            if (!empty($error->getPropertyPath())) {
                switch ($error->getPropertyPath()) {
                    case '[nome]':
                        $input = 'Nome da Task';
                        break;
                    case '[descricao]':
                        $input = 'Descrição';
                        break;
                    case '[prioridade]':
                        $input = 'Prioridade';
                        break;
                }
                return sprintf('%s : %s', $input, $error->getMessage());
            }
        }
    }
    return false;
}