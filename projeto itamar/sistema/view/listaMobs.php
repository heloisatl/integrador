
<html>
    <head>
        <title>Lista de mobs</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
      <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
      <?php
      require_once("../dao/mobsDao.php");
   $dao=new mobsDAO();
   $dados=$dao->listaGeral();
    echo "<table border=1>";
    foreach($dados as $dado){
        echo "<tr>";
       echo "<td>{$dado['id']}</td>";
echo "<td>{$dado['nome']}</td>";
echo "<td>{$dado['tipo']}</td>";
echo "<td>{$dado['dificuldade']}</td>";
echo "<td>{$dado['versao']}</td>";
echo "<td>{$dado['imagem']}</td>";

       echo "<td>".
       "<a href='../control/mobsControl.php?id={$dado['id']}&a=2'> Excluir</a>".
       "</td>";
       echo "<td>".
       "<a href='alterarMobs.php?id={$dado['id']}'> Alterar</a>".
       "</td>";
       echo "</tr>";
    }
    echo "</table>";
     ?>  
    </body>
</html>