# Sistema
  - php 7.1
  - ysql 5.7
  - nginx
     
  ###frameworks
  - Silex para php
  - jquery para Javascript
  - Bootstrap para Html
  

- Instalação do sistema

- O projeto será executado em containers Docker

- As portas usadas serão: 8087 e 33066
- Caso esteja usando essas portas, alterar no docker-compose.yml

- Executar o camandos :

sudo docker build -t fshigueru/nginx nginx/.

sudo docker build -t fshigueru/php7.1 php/.

sudo docker-compose up -d

- Após executar os comandos acima, será criado três drivers
- Caso você não altere o nome da pasta do projeto ao fazer o clone, os três drivers teram os nomes:
sistematask_db_1
sistematask_php_1
sistematask_nginx_1

- Caso altere, a o executar o comando "sudo docker-compose up -d" o sistem ira informar os drivers criados

- Acessar o container para instalar o projeto

sudo docker exec -it sistematask_php_1 bash

- após acessar o container, acessar o path
 
 cd /code/sistema-task
 
- dar permissão as pastas

chmod 777 -R var/

chmod 777 -R web/
 
- instalar dependencias do composer

composer install

- executar migration e seed para criar as tabelas e iniciar alguns dados

- Referencias criação migration

-- https://www.codediesel.com/mysql/creating-sql-schemas-with-doctrine-dbal/
-- http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/schema-representation.html

- Executar nessa ordem

bin/console mi:ex 20171102011632 --up

bin/console sistema:init seed

- http://localhost:8087

