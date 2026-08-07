<html>
    <head>
        <title>Cadastro de mobs</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
        <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
        <form action="../control/mobsControl.php?a=1" method="post">
        <h1>Cadastro de mobs</h1>
            <label for='nome'>nome</label>
<input type='text' name='nome'><br>
<label for='tipo'>tipo</label>
<input type='text' name='tipo'><br>
<label for='dificuldade'>dificuldade</label>
<input type='text' name='dificuldade'><br>
<label for='versao'>versao</label>
<input type='text' name='versao'><br>
<label for='imagem'>imagem</label>
<input type='text' name='imagem'><br>

             <button type="submit">Enviar</button>
        </form>
    </body>
</html>