<?php

$entidades = descobrirEntidades();
$sistemaCriado = is_dir(__DIR__ . '/sistema');

include 'mensagens.php';

function descobrirEntidades()
{
    $pastaModel = __DIR__ . '/sistema/model/';

    // verifica se a pasta sistema existe
    if (!is_dir(__DIR__ . '/sistema') || !is_dir($pastaModel)) {
        return [];
    }

    // scaneia arquivos da pasta model
    $arquivos = scandir($pastaModel); //scandir basicamente sacneia um diretório e retorna uma lista de arquivos e pastas dentro dele dentro de um array
    if (!$arquivos) return [];

    // filtra arquivos válidos
    $entidades = [];
    foreach ($arquivos as $arquivo) {
        if (isValidModelFile($arquivo, $pastaModel)) {
            $entidades[] = str_replace('.php', '', $arquivo);
        }
    }

    return $entidades;
}

// verifica se um arquivo é um model válido
function isValidModelFile($arquivo, $pastaModel)
{
    return $arquivo !== '.' &&
        $arquivo !== '..' &&
        $arquivo !== 'conexao.php' &&
        str_ends_with($arquivo, '.php') &&
        is_file($pastaModel . $arquivo);
}

//formatacao de nome
function formatarNome($nomeEntidade)
{
    return ucfirst(strtolower($nomeEntidade));
}


// verifica se arquivo de view existe no sistema
function verificarArquivoView($entidade, $tipo = 'cadastro')
{
    $caminhos = [
        'cadastro' => __DIR__ . "/sistema/view/{$entidade}.php",
        'lista' => __DIR__ . "/sistema/view/lista" . ucfirst($entidade) . ".php"
    ];

    return file_exists($caminhos[$tipo] ?? $caminhos['cadastro']);
}

// limpa o sistema apagando todas as pastas
function limparSistemaCompleto()
{
    $itensParaRemover = [
        __DIR__ . '/sistema',      
        __DIR__ . '/sistema.zip'   
    ];

    foreach ($itensParaRemover as $item) {
        if (is_dir($item)) {
            removeDirectory($item);
        } elseif (file_exists($item)) {
            unlink($item); //deleta o arquivo
        }
    }

    return true;
}

//remove diretório e todo seu conteúdo recursivamente
function removeDirectory($dir)
{
    if (!is_dir($dir)) return false;

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }

    return rmdir($dir);
}




// processar solicitação de limpeza
if (isset($_GET['limpar']) && $_GET['limpar'] == '1') {
    if (limparSistemaCompleto()) {
        $mensagemLimpeza = $mensagens[6]; 
        

        $entidades = descobrirEntidades();
        $sistemaCriado = is_dir(__DIR__ . '/sistema');
    } else {
        $mensagemErroLimpeza = $mensagens[7]; 
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestão</title>
    <style>
        :root {
            --cor-primaria: #FEF3E2;
            /* Bege claro */
            --cor-secundaria: #FAB12F;
            /* Amarelo dourado */
            --cor-terciaria: #FA812F;
            /* Laranja */
            --cor-acento: #DD0303;
            /* Vermelho */
            --borda-radius: 20px;
            /* Bordas arredondadas */
            --sombra: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--cor-primaria) 0%, #f8f0e3 100%);
            min-height: 100vh;
        }

        /* Cabeçalho com gradiente e bordas arredondadas */
        .cabecalho {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--cor-terciaria) 0%, var(--cor-acento) 100%);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            font-weight: bold;
            border-radius: 0 0 var(--borda-radius) var(--borda-radius);
            box-shadow: var(--sombra);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Menu principal com nova paleta */
        .menu {
            width: 100%;
            height: 100px;
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-terciaria) 100%);
            display: flex;
            align-items: center;
            padding: 0 30px;
            border-radius: var(--borda-radius);
            margin: 20px;
            width: calc(100% - 40px);
            box-shadow: var(--sombra);
        }

        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 50px;
        }

        .menu li {
            position: relative;
        }

        .menu a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            padding: 15px 25px;
            display: block;
            border-radius: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .menu a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Submenu com bordas arredondadas */
        .menu li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: linear-gradient(135deg, var(--cor-acento) 0%, #b30202 100%);
            list-style: none;
            padding: 10px 0;
            margin: 0;
            min-width: 220px;
            box-shadow: var(--sombra);
            border-radius: var(--borda-radius);
            backdrop-filter: blur(10px);
        }

        .menu li ul li a {
            padding: 15px 20px;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin: 5px 10px;
            border-radius: 12px;
        }

        .menu li ul li:last-child a {
            border-bottom: none;
        }

        .menu li ul li a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        /* Exibir submenu com animação */
        .menu li:hover ul {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Conteúdo */
        .conteudo {
            min-height: calc(100vh - 340px);
            padding: 30px;
            background: transparent;
        }

        .cartao {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: var(--borda-radius);
            box-shadow: var(--sombra);
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-sistema {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .info-item {
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-terciaria) 100%);
            color: white;
            padding: 25px;
            border-radius: var(--borda-radius);
            text-align: center;
            box-shadow: var(--sombra);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .info-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        .info-item h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .info-item p {
            margin: 0;
            opacity: 0.9;
        }

        /* Botões modernos */
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            text-align: center;
            font-size: 16px;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--cor-acento) 0%, #b30202 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(221, 3, 3, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(221, 3, 3, 0.4);
        }

        .btn-voltar {
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-terciaria) 100%);
            color: white;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(250, 177, 47, 0.3);
        }

        .btn-voltar:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(250, 177, 47, 0.4);
        }

        /* Mensagens com bordas arredondadas */
        .mensagem-sucesso,
        .mensagem-warning,
        .mensagem-erro {
            padding: 20px;
            border-radius: var(--borda-radius);
            margin-bottom: 25px;
            text-align: center;
            box-shadow: var(--sombra);
        }

        .mensagem-sucesso {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .mensagem-warning {
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-terciaria) 100%);
            color: white;
        }

        .mensagem-erro {
            background: linear-gradient(135deg, var(--cor-acento) 0%, #b30202 100%);
            color: white;
        }
    </style>
</head>

<body>
    <div class="cabecalho">
        Sistema de Gestão - Framework PHP
    </div>

    <div class="menu">
        <ul>
            <?php if ($sistemaCriado && count($entidades) > 0): ?>
                <li>
                    <a href="#">📝 Cadastros</a>
                    <ul>
                        <?php foreach ($entidades as $entidade): ?>
                            <?php if (verificarArquivoView($entidade, 'cadastro')): ?>
                                <li><a href="sistema/view/<?php echo $entidade; ?>.php">Cadastro de <?php echo formatarNome($entidade); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li>
                    <a href="#">📊 Relatórios</a>
                    <ul>
                        <?php foreach ($entidades as $entidade): ?>
                            <?php if (verificarArquivoView($entidade, 'lista')): ?>
                                <li><a href="sistema/view/lista<?php echo ucfirst($entidade); ?>.php">Lista de <?php echo formatarNome($entidade); ?>s</a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php elseif (!$sistemaCriado): ?>
                <li>
                    <a href="index.php">🔧 Configurar Sistema</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="conteudo">
        <?php if (isset($_GET['criado']) && $_GET['criado'] == '1'): ?>
            <div class="mensagem-sucesso">
                <h3><?php echo $mensagens[4]; ?></h3>
                <p><?php echo $mensagens[5]; ?></p>
                <div class="info-adicional">
                    <strong><?php echo $mensagens[16]; ?></strong><br>
                    <small><?php echo $mensagens[17]; ?></small>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($mensagemLimpeza)): ?>
            <div class="mensagem-warning">
                <h3>🧹 <?php echo $mensagemLimpeza; ?></h3>
            </div>
        <?php endif; ?>

        <?php if (isset($mensagemErroLimpeza)): ?>
            <div class="mensagem-erro">
                <h3>❌ <?php echo $mensagemErroLimpeza; ?></h3>
            </div>
        <?php endif; ?>

        <div class="cartao">
            <h2>🎉 Bem-vindo ao Sistema de Gestão!</h2>
            <p>Aqui você poderá encontrar informações gerais sobre seu novo framework!</p>

            <?php if ($sistemaCriado): ?>
                <div class="cartao" style="background: linear-gradient(135deg, rgba(254,243,226,0.8) 0%, rgba(248,240,227,0.8) 100%); text-align: center;">
                    <h4 style="color: var(--cor-acento); margin-bottom: 15px;">🛠️ Controles do Sistema</h4>
                    <p style="margin-bottom: 20px; color: #666;">Gerencie seu sistema com cuidado:</p>
                    <a href="?limpar=1"
                        onclick="return confirm('⚠️ ATENÇÃO: Tem certeza que deseja limpar completamente o sistema?\n\nEsta ação irá:\n• Remover todas as pastas e arquivos do sistema\n• Apagar todos os dados gerados\n• Não pode ser desfeita!\n\nDeseja continuar?')"
                        class="btn btn-danger">
                        �️ Limpar Sistema Completo
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($sistemaCriado && count($entidades) > 0): ?>
                <h3>📋 Módulos Disponíveis:</h3>
                <div class="info-sistema">
                    <?php foreach ($entidades as $entidade): ?>
                        <div class="info-item">
                            <h3><?php echo formatarNome($entidade); ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($sistemaCriado && count($entidades) == 0): ?>
                <div class="info-item" style="background: linear-gradient(135deg, var(--cor-acento) 0%, #b30202 100%);">
                    <h3><?php echo $mensagens[10]; ?></h3>
                    <p><?php echo $mensagens[11]; ?></p>
                </div>

            <?php else: ?>
                <div class="info-item" style="background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-terciaria) 100%);">
                    <h3><?php echo $mensagens[8]; ?></h3>
                    <p><?php echo $mensagens[9]; ?> <a href="index.php" style="color: white; text-decoration: underline; font-weight: bold;">formulário de configuração</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>