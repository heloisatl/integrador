<html>
    <head>
        <title>Alterar mobs</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
        <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
        <?php
        require_once("../dao/mobsDao.php");
        $dao = new MobsDao();
        $dados = $dao->buscarId($_GET['id']);
        ?>
        <form action="../control/mobsControl.php?a=3" method="post">
        <h1>Alterar mobs</h1>
            <input type='hidden' name='id' value="<?php echo $dados['id']; ?>">
<label for='nome'>nome</label>
<input type='text' name='nome' value="<?php echo $dados['nome']; ?>"><br>
<label for='tipo'>tipo</label>
<input type='text' name='tipo' value="<?php echo $dados['tipo']; ?>"><br>
<label for='dificuldade'>dificuldade</label>
<input type='text' name='dificuldade' value="<?php echo $dados['dificuldade']; ?>"><br>
<label for='versao'>versao</label>
<input type='text' name='versao' value="<?php echo $dados['versao']; ?>"><br>
<label for='imagem'>imagem</label>
<input type='text' name='imagem' value="<?php echo $dados['imagem']; ?>"><br>

             <button type="submit">Alterar</button>
             <a href="listaMobs.php">Voltar</a>
        </form>
    </body>
</html>