<?php
// Configurações dos caminhos no seu servidor
$xmlMainFile = "loja_temas.xml"; // O arquivo XML principal que você me enviou
$targetDirXml = "Xml/";          // Pasta onde os arquivos .xml individuais dos usuários serão salvos
$targetDirScreen = "Screen/";    // Pasta onde os prints/screenshots dos temas serão salvos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $themeName = isset($_POST['theme_name']) ? $_POST['theme_name'] : 'Tema Sem Nome';
    $creatorName = isset($_POST['creator_name']) ? $_POST['creator_name'] : 'Anônimo';
    
    // Tratamento do nome do arquivo para evitar espaços ou problemas com caracteres
    $cleanThemeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $themeName));
    $xmlFileName = $cleanThemeName . ".xml";
    $targetXmlPath = $targetDirXml . $xmlFileName;

    // 1. Recebe e salva o arquivo XML enviado pelo Mod
    if (isset($_FILES['theme_file']) && move_uploaded_file($_FILES['theme_file']['tmp_name'], $targetXmlPath)) {
        
        // Se o usuário também enviar um print (opcional), você salva aqui
        if (isset($_FILES['screenshot_file'])) {
            $screenFileName = $cleanThemeName . ".jpg";
            move_uploaded_file($_FILES['screenshot_file']['tmp_name'], $targetDirScreen . $screenFileName);
        }

        // 2. Abre o XML principal da sua loja para injetar o novo tema
        if (file_exists($xmlMainFile)) {
            $dom = new DOMDocument();
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            $dom->load($xmlMainFile);

            // Seleciona a tag raiz <themes>
            $root = $dom->documentElement;

            // Cria o novo bloco <theme>
            $newTheme = $dom->createElement('theme');

            // Cria e adiciona a tag <title>
            $titleTag = $dom->createElement('title', htmlspecialchars($themeName));
            $newTheme->appendChild($titleTag);

            // Cria e adiciona a tag <date> com os créditos do criador (conforme seu padrão "By Nome")
            $dateTag = $dom->createElement('date', 'By ' . htmlspecialchars($creatorName));
            $newTheme->appendChild($dateTag);

            // Injeta o novo tema no topo ou no final da lista da loja
            $root->appendChild($newTheme);

            // Salva as alterações de volta no arquivo da loja
            $dom->save($xmlMainFile);
        }

        echo "Sucesso";
    } else {
        echo "Erro ao fazer upload do arquivo XML.";
    }
} else {
    echo "Método de requisição inválido.";
}
?>

