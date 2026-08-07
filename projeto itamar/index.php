<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Conexão</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="js/funcoes.js"></script>
</head>
<body>
<div class="container">

<form action="creator.php" method="POST">
    <?php
    include 'mensagens.php';
    if (isset($_GET['msg']) ){
        $msg=$_GET['msg'];
        $classe=$msg==2?'mensagem':'mensagem_erro';
        echo "<div class=\"$classe\">"  . ($mensagens[$msg] ?? "Erro desconhecido") . "</div>";
    }

?>
    <h1>EasyMVC</h1><h2>Configuração</h2>
    
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="home.php" style="color: #3498db; text-decoration: none;">🏠 Ir para a página inicial</a>
    </div>

    <label for="servidor">Servidor:</label>
    <input type="text" id="servidor" name="servidor" required>
    <label for="usuario">Usuário:</label>
    <input type="text" id="usuario" name="usuario" required>
    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha" onblur="carregarBanco()">
    <label for="banco">Banco de Dados:</label>
    <select id="banco" name="banco" required>

    </select>
    <label id="carregando"></label>
    <button type="submit">Enviar</button>
</form>
</div>
</body>
</html>
