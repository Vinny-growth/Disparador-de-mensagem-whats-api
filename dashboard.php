<?php
// Inclua o arquivo de configuração do banco de dados
require_once 'includes/config.php';

// Importação necessária para leitura da planilha
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Verifica se o usuário está logado
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Processar envio de arquivo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $errors = [];

    $fileName = $_FILES["file"]["name"];
    $fileTmpName = $_FILES["file"]["tmp_name"];
    $fileSize = $_FILES["file"]["size"];
    $fileError = $_FILES["file"]["error"];
    $fileType = $_FILES["file"]["type"];

    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = ['xls', 'xlsx'];

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 500000) {
                $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                $destination = 'uploads/' . $fileNameNew;
                move_uploaded_file($fileTmpName, $destination);
                
                // Processando a planilha
                $spreadsheet = IOFactory::load($destination);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                $templateName = isset($_POST['template_name']) ? $_POST['template_name'] : '';

                foreach ($rows as $row) {
                    $phone = $row[0];

                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $phone,
                        "type" => "template",
                        "template" => [
                            "name" => $templateName,
                            "language" => ["code" => "pt_BR"]
                        ]
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v17.0/101868529198814/messages');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer EAAQH4zfslksBOZCsiZB2hckP6sRMvrk9bpQkWQHhbN7c0Cl65BEAcH0WqnzDBhNKAQuOCfdk0pJSQrnFRfzJ7bZBtlZA7CLEk8hEHrNupF1SXtqAt2YnHy47EUfxQrljMpxOomHV7En0yPhQsMORDQzPAZBAfXQRo2QtgjEuAXqdQlkrZAgVfKIpJ4XimPrE8J',
                        'Content-Type: application/json'
                    ));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $errors[] = 'Erro ao enviar a mensagem para ' . $phone . ': ' . curl_error($ch);
                    }
                    curl_close($ch);
                }
            } else {
                $errors[] = "Seu arquivo é muito grande!";
            }
        } else {
            $errors[] = "Houve um erro ao fazer upload de seu arquivo!";
        }
    } else {
        $errors[] = "Você não pode enviar arquivos desse tipo!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Disparo de WhatsApp</title>
    <!-- Incluindo o Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Painel de Disparo de WhatsApp</h2>

    <!-- Mensagens de erro, se houver -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulário de upload -->
    <div class="card">
        <div class="card-body">
            <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Escolha uma planilha:</label>
                    <input type="file" name="file" class="form-control-file" required>
                </div>
                <div class="form-group">
                    <label for="template_name">Nome do Template:</label>
                    <input type="text" id="template_name" name="template_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
</div>

<!-- Incluindo o Bootstrap JS e o jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
