<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'modelPessoa.php';

function json_out($success, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(
        ['success' => $success, 'message' => $message, 'data' => $data],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function get_request_payload(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $raw = file_get_contents('php://input');
    if (stripos($contentType, 'application/json') !== false && $raw) {
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

function validate_nome($nome): bool { 
    return is_string($nome) && trim($nome) !== ''; 
}

function normalize_digits($s): string { 
    return preg_replace('/\D+/', '', (string)$s); 
}

function validate_cpf_format($cpf): bool {
    $digits = normalize_digits($cpf);
    return strlen($digits) === 11; 
}

function validate_telefone($telefone): bool { 
    return is_string($telefone) && trim($telefone) !== ''; 
}

try {
    $op = $_GET['op'] ?? $_POST['op'] ?? null;
    if (!$op) {
        json_out(false, 'Parâmetro "op" é obrigatório. Use create/read/update/delete.', null, 400);
    }

    $model = new ModelPessoa();

    switch ($op) {
        case 'create': {
            $data = get_request_payload();
            $nome = $data['nome'] ?? '';
            $cpf = $data['cpf'] ?? '';
            $telefone = $data['telefone'] ?? '';

            if (!validate_nome($nome)) json_out(false, 'Nome é obrigatório.', null, 422);
            if (!validate_cpf_format($cpf)) json_out(false, 'CPF deve conter 11 dígitos.', null, 422);
            if (!validate_telefone($telefone)) json_out(false, 'Telefone é obrigatório.', null, 422);

            $p = new Pessoa(null, trim($nome), normalize_digits($cpf), trim($telefone));

            try {
                $id = $model->create($p);
                json_out(true, 'Pessoa criada com sucesso.', ['id' => $id], 201);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    json_out(false, 'CPF já cadastrado.', null, 409);
                }
                json_out(false, 'Erro no banco de dados.', null, 500);
            }
            break;
        }
        case 'read': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            try {
                $rows = $model->read($id);
                json_out(true, 'Consulta realizada.', $rows, 200);
            } catch (PDOException $e) {
                json_out(false, 'Erro de conexão.', null, 500);
            }
            break;
        }
        case 'update': {
            $data = get_request_payload();
            $id = isset($data['id']) ? (int)$data['id'] : null;
            $nome = $data['nome'] ?? '';
            $cpf = $data['cpf'] ?? '';
            $telefone = $data['telefone'] ?? '';

            if (!$id) json_out(false, 'ID é obrigatório.', null, 422);
            if (!validate_nome($nome)) json_out(false, 'Nome é obrigatório.', null, 422);
            if (!validate_cpf_format($cpf)) json_out(false, 'CPF deve conter 11 dígitos.', null, 422);
            if (!validate_telefone($telefone)) json_out(false, 'Telefone é obrigatório.', null, 422);

            $p = new Pessoa($id, trim($nome), normalize_digits($cpf), trim($telefone));

            try {
                $ok = $model->update($p);
                json_out(true, $ok ? 'Pessoa atualizada com sucesso.' : 'Nada foi alterado.', null, 200);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    json_out(false, 'CPF já cadastrado para outra pessoa.', null, 409);
                }
                json_out(false, 'Erro no banco de dados.', null, 500);
            } catch (InvalidArgumentException $e) {
                json_out(false, 'Erro nos dados enviados.', null, 422);
            }
            break;
        }
        case 'delete': {
            $data = get_request_payload();
            $id = isset($data['id']) ? (int)$data['id'] : null;
            if (!$id) json_out(false, 'ID é obrigatório.', null, 422);

            try {
                $ok = $model->delete($id);
                json_out(true, $ok ? 'Pessoa excluída com sucesso.' : 'Registro não encontrado.', null, 200);
            } catch (PDOException $e) {
                json_out(false, 'Erro no banco de dados.', null, 500);
            }
            break;
        }
        default:
            json_out(false, 'Operação inválida. Use create/read/update/delete.', null, 400);
    }
} catch (Throwable $t) {
    json_out(false, 'Erro inesperado.', null, 500);
}