<?php

use Slim\Http\Request;
use Slim\Http\Response;


if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, GET, DELETE,OPTIONS');
header('always_populate_raw_post_data: -1');

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();

$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);

/*______________________________________________________
|                                                       |
|                RESTS's - Evolução                     |
|______________________________________________________*/

function getEvolucoes($request) {
	$idPaciente = $request->getAttribute('idPaciente');
    $idFonoaudiologo = $request->getAttribute('idFonoaudiologo');
    
    $sql = "SELECT id, dsc_evolucao, fk_flag_evolucao, 
                   fk_fonoaudiologo, fk_paciente, 
                   dsc_titulo, dat_evolucao
            FROM tb_evolucao 
            WHERE fk_paciente = ". $idPaciente ." 
            and fk_fonoaudiologo = ". $idFonoaudiologo ." 
            order by dat_evolucao DESC";

    try {
        $stmt = getConnection()->query($sql);
        $evolucoes = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
		
        return json_encode($evolucoes, JSON_UNESCAPED_UNICODE);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}



function addEvolucao($request) {
    $evolucao = json_decode($request->getBody());
	
    $sql = "INSERT INTO tb_evolucao(dsc_evolucao,fk_flag_evolucao,fk_fonoaudiologo,fk_paciente,dsc_titulo) VALUES (:dsc_evolucao, :fk_flag_evolucao, :fk_fonoaudiologo, :fk_paciente, :dsc_titulo)";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("dsc_evolucao", $evolucao->dsc_evolucao);
        $stmt->bindParam("fk_flag_evolucao", $evolucao->fk_flag_evolucao);
        $stmt->bindParam("fk_fonoaudiologo", $evolucao->fk_fonoaudiologo);
        $stmt->bindParam("fk_paciente", $evolucao->fk_paciente);
        $stmt->bindParam("dsc_titulo", $evolucao->dsc_titulo);
        $stmt->execute();
        $db = null;
        echo json_encode($evolucao);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateEvolucao($request) {
    $evolucao = json_decode($request->getBody());
	$id = $request->getAttribute('id');
    $sql = "UPDATE tb_evolucao 
            SET dsc_evolucao =:dsc_evolucao,
                fk_flag_evolucao =:fk_flag_evolucao,
                fk_fonoaudiologo =:fk_fonoaudiologo,
                fk_paciente =:fk_paciente,
                dsc_titulo =:dsc_titulo,
                dat_evolucao =:dat_evolucao
            WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("dsc_evolucao", $evolucao->dsc_evolucao);
        $stmt->bindParam("fk_flag_evolucao", $evolucao->fk_flag_evolucao);
        $stmt->bindParam("fk_fonoaudiologo", $evolucao->fk_fonoaudiologo);
        $stmt->bindParam("fk_paciente", $evolucao->fk_paciente);
        $stmt->bindParam("dsc_titulo", $evolucao->dsc_titulo);
        $stmt->bindParam("dat_evolucao", $evolucao->dat_evolucao);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($evolucao);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function deleteEvolucao($request) {
	$id = $request->getAttribute('id');
    $sql = "DELETE FROM tb_evolucao WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
		echo '{"error":{"text":"successfully! deleted Records"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/*______________________________________________________
|                                                       |
|               RESTS's - Fonoaudiologo                 |
|______________________________________________________*/

function addFonoaudiologo(Request $request, Response $response){
    $fonoaudiologo = json_decode($request->getBody());
	
    $sqlPessoa = "INSERT INTO tb_pessoa(dsc_cpf,dsc_nome,img_perfil,dsc_email,dat_nascimento,
                                    dsc_telefone1,dsc_telefone2,frg_cor,frg_endestado,
                                    frg_endcidade,dsc_endbairro,dsc_endcep,dsc_endnum,
                                    dsc_endrua,dsc_nomemae,dsc_nomepai,frg_estado_civil,
                                    frg_sexo,frg_nasestado,frg_nascidade,frg_tipo_sanguineo
                                    )
     VALUES (:dsc_cpf,:dsc_nome,:img_perfil,:dsc_email,:dat_nascimento,
            :dsc_telefone1,:dsc_telefone2,:frg_cor,:frg_endestado,
            :frg_endcidade,:dsc_endbairro,:dsc_endcep,:dsc_endnum,
            :dsc_endrua,:dsc_nomemae,:dsc_nomepai,:frg_estado_civil,
            :frg_sexo,:frg_nasestado,:frg_nascidade,:frg_tipo_sanguineo)";

    $sqlFono = "INSERT INTO tb_fonoaudiologo(frg_pessoa,num_crf,frg_grau_formacao,arr_areas,arr_cursos) 
                VALUES (:frg_pessoa,:num_crf,:frg_grau_formacao,:arr_areas,:arr_cursos)";
    
    
    $db = getConnection();
    try {
        
        $db->beginTransaction();

        $stmt = $db->prepare($sqlPessoa);

        $stmt->bindParam("dsc_cpf", $fonoaudiologo->dsc_cpf);
        $stmt->bindParam("dsc_nome", $fonoaudiologo->dsc_nome);
        $stmt->bindParam("img_perfil", $fonoaudiologo->img_perfil);
        $stmt->bindParam("dsc_email", $fonoaudiologo->dsc_email);
        $stmt->bindParam("dat_nascimento", $fonoaudiologo->dat_nascimento);
        $stmt->bindParam("dsc_telefone1", $fonoaudiologo->dsc_telefone1);
        $stmt->bindParam("dsc_telefone2", $fonoaudiologo->dsc_telefone2);
        $stmt->bindParam("frg_cor", $fonoaudiologo->frg_cor);
        $stmt->bindParam("frg_endestado", $fonoaudiologo->frg_endestado);
        $stmt->bindParam("frg_endcidade", $fonoaudiologo->frg_endcidade);
        $stmt->bindParam("dsc_endbairro", $fonoaudiologo->dsc_endbairro);
        $stmt->bindParam("dsc_endcep", $fonoaudiologo->dsc_endcep);
        $stmt->bindParam("dsc_endnum", $fonoaudiologo->dsc_endnum);
        $stmt->bindParam("dsc_endrua", $fonoaudiologo->dsc_endrua);
        $stmt->bindParam("dsc_nomemae", $fonoaudiologo->dsc_nomemae);
        $stmt->bindParam("dsc_nomepai", $fonoaudiologo->dsc_nomepai);
        $stmt->bindParam("frg_estado_civil", $fonoaudiologo->frg_estado_civil);
        $stmt->bindParam("frg_sexo", $fonoaudiologo->frg_sexo);
        $stmt->bindParam("frg_nasestado", $fonoaudiologo->frg_nasestado);
        $stmt->bindParam("frg_nascidade", $fonoaudiologo->frg_nascidade);
        $stmt->bindParam("frg_tipo_sanguineo", $fonoaudiologo->frg_tipo_sanguineo);
       
        $stmt->execute();

        $idPessoa = $db->lastInsertId();

        $fonoaudiologo->frg_pessoa = $idPessoa;

        $stmt2 = $db->prepare($sqlFono);

        $stmt2->bindParam("frg_pessoa", $fonoaudiologo->frg_pessoa);
        $stmt2->bindParam("num_crf", $fonoaudiologo->num_crf);
        $stmt2->bindParam("frg_grau_formacao", $fonoaudiologo->frg_grau_formacao);
        $stmt2->bindParam("arr_areas", $fonoaudiologo->arr_areas);
        $stmt2->bindParam("arr_cursos", $fonoaudiologo->arr_cursos);

        $stmt2->execute();

        $fonoaudiologo->id = $db->lastInsertId();

        $db->commit();
        $db = null;

        return $response->withJson($fonoaudiologo, 201)
        ->withHeader('Content-type', 'application/json');

    } catch(PDOException $e) {
        $db->rollBack();
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getFonoaudiologos(Request $request, Response $response) {
    $sql = "SELECT * FROM tb_pessoa p 
            INNER JOIN tb_fonoaudiologo f 
            ON p.id = f.frg_pessoa";

    try {
        $stmt = getConnection()->query($sql);
        $fonoaudiologos = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return  $response->withJson($fonoaudiologos, 200)
        ->withHeader('Content-type', 'application/json');

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    
}

function getFonoaudiologo(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    
    $sql = "SELECT * FROM tb_pessoa p 
    INNER JOIN tb_fonoaudiologo f 
    ON p.id = f.frg_pessoa WHERE f.id=:id";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(":id", $id);

        $stmt->execute();

        $fonoaudiologo = $stmt->fetch(PDO::FETCH_OBJ);
        $db = null;
        
        return  $response->withJson($fonoaudiologo, 200)
        ->withHeader('Content-type', 'application/json');

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/*______________________________________________________
|                                                       |
|                 RESTS's - Paciente                    |
|______________________________________________________*/


function getPacientes(Request $request, Response $response) {
    $sql = "SELECT pe.*, (
                SELECT flag_situacao FROM tb_fonoaudiologo_paciente WHERE flag_situacao = 1 AND PE.ID = FRG_PACIENTE
            ) AS situacao
            FROM tb_pessoa pe 
            INNER JOIN tb_paciente pa 
            ON pe.id = pa.id_pessoa";
        
    try {
        $stmt = getConnection()->query($sql);
        $pacientes = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($pacientes, JSON_UNESCAPED_UNICODE);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}



/*______________________________________________________
|                                                       |
|                  Conecção com BD                      |
|______________________________________________________*/

function getConnection() {
      $dbhost="127.0.0.1";
      $dbuser="root";
      $dbpass="";
      $dbname="db_maisfono";
    // $dbhost="jrpires.com";
    // $dbuser="jrpiresc_ifpe";
    // $dbpass="maisfono_0001";
    // $dbname="jrpiresc_maisfono_rest";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}