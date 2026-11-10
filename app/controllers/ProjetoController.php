<?php
namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Projeto;
use app\models\Tabela;
use app\models\Atributo;
use app\services\ProjetoService;
use app\services\BancoService;
use app\services\TabelaService;
use app\services\AtributoService;

use app\tools\SchemaInspector;
use app\tools\gerador\FiltroTabelas;
use app\tools\gerador\MotorGeracao;
use app\tools\gerador\OpcoesGeracao;
use app\services\UsuarioService;

class ProjetoController extends Controller{
    private ?ProjetoService $projetoService = null;

    public function __construct(){
        // Lazy-loading de ProjetoService para garantir que visitantes não instanciem DAOs/Repositories (RN 01 / FA01)
    }

    private function getProjetoService(): ProjetoService {
        if ($this->projetoService === null) {
            $this->projetoService = new ProjetoService();
        }
        return $this->projetoService;
    }

    
    //Verifica se há um usuário autenticado válido na sessão atual.
     
    private function usuarioEstaAutenticado(): bool {
        if (!empty($_SESSION['usuario_logado'])
            && is_object($_SESSION['usuario_logado'])
            && method_exists($_SESSION['usuario_logado'], 'getIdUsuario')
            && (int) $_SESSION['usuario_logado']->getIdUsuario() > 0
        ) {
            return true;
        }
        return false;
    }

    
    //Captura as credenciais de conexão MySQL local (enviadas via POST ou constantes padrão do ambiente).
     
    private function obterCredenciaisLocaisRequest(): array {
        $hostPadrao = defined('DB_HOST') ? DB_HOST : 'localhost';
        $userPadrao = defined('DB_USER') ? DB_USER : 'root';
        $passPadrao = defined('DB_PASS') ? DB_PASS : '';

        return [
            'host'  => trim((string) ($_POST['servidor'] ?? $_POST['host'] ?? '')) ?: $hostPadrao,
            'porta' => trim((string) ($_POST['porta'] ?? '')) ?: '3306',
            'user'  => trim((string) ($_POST['usuario'] ?? '')) ?: $userPadrao,
            'pass'  => (string) ($_POST['senha'] ?? $passPadrao),
        ];
    }

    public function getDatabases(){
        $sel = trim((string) ($_POST['selecionado'] ?? ''));

        if ($this->usuarioEstaAutenticado()) {
            echo (new BancoService())->getBancoByUsuario(
                $_SESSION['usuario_logado']->getIdUsuario(),
                "DB_OPTIONS",
                $sel
            );
            return;
        }

        // Modo Visitante: reaproveita o leitor de bancos locais do PHPMeuAmigo (SHOW DATABASES via PDO)
        try {
            $cred = $this->obterCredenciaisLocaisRequest();
            $bancos = BancoService::listarBancosMysqlLocal($cred['host'], $cred['porta'], $cred['user'], $cred['pass']);
            $options = '';
            foreach ($bancos as $nomeBanco) {
                $escaped = htmlspecialchars((string) $nomeBanco, ENT_QUOTES, 'UTF-8');
                $selected = ($nomeBanco === $sel) ? ' selected' : '';
                $options .= "<option value='{$escaped}'{$selected}>{$escaped}</option>";
            }
            echo $options;
        } catch (\Throwable $e) {
            echo '';
        }
    }

    public function getTabelas(): void {
        header('Content-Type: application/json; charset=utf-8');
        $banco = trim((string) ($_POST['banco'] ?? ''));

        if ($banco === '') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione um banco de dados antes de prosseguir.']);
            return;
        }

        try {
            if ($this->usuarioEstaAutenticado() && ctype_digit($banco)) {
                $tabelaService = new TabelaService();
                $tabelas = $tabelaService->getTabelasByFk_banco((int) $banco, 'prox_etap');
                echo json_encode(['sucesso' => true, 'tabelas' => $tabelas]);
                return;
            }

            // Modo Visitante: leitura direta das tabelas do banco local em memória (sem persistência)
            $cred = $this->obterCredenciaisLocaisRequest();
            $inspecao = $this->inspecionarBancoLocalEmMemoria(
                $banco,
                $cred['host'],
                $cred['porta'],
                $cred['user'],
                $cred['pass'],
                false
            );

            echo json_encode(['sucesso' => true, 'tabelas' => $inspecao['tabelas']]);
        } catch (\Throwable $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    
    //Inspeciona diretamente um banco MySQL local em memória (SHOW TABLES, SHOW COLUMNS e KEY_COLUMN_USAGE)
    //e monta instâncias temporárias de Tabela e Atributo sem tocar nas tabelas internas do DevStudio.
     
    private function inspecionarBancoLocalEmMemoria(string $nomeBanco,string $host = 'localhost',string $porta = '3306',string $user = 'root',string $pass = '',bool $carregarAtributos = true,?FiltroTabelas $filtroTabelas = null): array {
        if ($nomeBanco === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $nomeBanco)) {
            throw new \InvalidArgumentException('Selecione um banco de dados local válido antes de gerar o sistema.');
        }

        $dsn = "mysql:host={$host};port={$porta};dbname={$nomeBanco}";
        $schema = new SchemaInspector($dsn, $user, $pass);
        $tabelasRaw = $schema->getTabelas();

        $tabelas = [];
        foreach ($tabelasRaw as $idx => $row) {
            $nomeTabela = $row[0] ?? null;
            if (!$nomeTabela) {
                continue;
            }
            $idTabela = $idx + 1;
            $tabelas[] = new Tabela($idTabela, 0, $nomeTabela);
        }

        if ($filtroTabelas !== null) {
            $tabelas = $filtroTabelas->filtrar($tabelas);
        }

        if (!$carregarAtributos) {
            return [
                'tabelas'   => $tabelas,
                'atributos' => [],
            ];
        }

        // Etapa 1: Lê as colunas físicas de cada tabela selecionada em memória
        $colunasTempPorTabela = [];
        $mapaIdAtributo = [];
        $proximoIdAtributo = 1;

        foreach ($tabelas as $tabela) {
            $idTabela = (int) $tabela->getId_tabela();
            $nomeTabela = $tabela->getNome_tabela();
            $tabelaKey = strtolower($nomeTabela);
            $colunasFisicas = $schema->getAtributos($nomeTabela);

            $colunasTempPorTabela[$idTabela] = [];
            foreach ($colunasFisicas as $col) {
                $nomeCampo = $col['Field'] ?? '';
                if ($nomeCampo === '') {
                    continue;
                }

                $idAtributo = $proximoIdAtributo++;
                $tipo = isset($col['Type']) && is_string($col['Type']) ? strtolower(trim($col['Type'])) : 'varchar(60)';
                $pk = (isset($col['Key']) && $col['Key'] === 'PRI');
                $nn = (isset($col['Null']) && $col['Null'] === 'NO');
                $ai = (isset($col['Extra']) && str_contains(strtolower((string) $col['Extra']), 'auto_increment'));
                $uq = (isset($col['Key']) && $col['Key'] === 'UNI');

                $mapaIdAtributo[$tabelaKey][strtolower($nomeCampo)] = $idAtributo;
                $colunasTempPorTabela[$idTabela][] = [
                    'id_atributo'   => $idAtributo,
                    'fk_tabela'     => $idTabela,
                    'fk_atributo'   => null,
                    'nome_atributo' => $nomeCampo,
                    'tipo'          => $tipo,
                    'pk'            => $pk,
                    'nn'            => $nn,
                    'ai'            => $ai,
                    'uq'            => $uq,
                ];
            }
        }

        // Etapa 2: Resolve as chaves estrangeiras (Foreign Keys) diretamente em memória
        foreach ($tabelas as $tabela) {
            $idTabela = (int) $tabela->getId_tabela();
            $nomeTabela = $tabela->getNome_tabela();
            $fks = $schema->getReferenciaFk($nomeTabela);
            if (empty($fks) || empty($colunasTempPorTabela[$idTabela])) {
                continue;
            }

            $mapaFkOrigem = [];
            foreach ($fks as $fk) {
                $colOrigem = strtolower((string) ($fk['COLUMN_NAME'] ?? ''));
                $tabDest   = strtolower((string) ($fk['REFERENCED_TABLE_NAME'] ?? ''));
                $colDest   = strtolower((string) ($fk['REFERENCED_COLUMN_NAME'] ?? ''));
                if ($colOrigem !== '' && isset($mapaIdAtributo[$tabDest][$colDest])) {
                    $mapaFkOrigem[$colOrigem] = $mapaIdAtributo[$tabDest][$colDest];
                }
            }

            foreach ($colunasTempPorTabela[$idTabela] as &$colData) {
                $colKey = strtolower($colData['nome_atributo']);
                if (isset($mapaFkOrigem[$colKey])) {
                    $colData['fk_atributo'] = $mapaFkOrigem[$colKey];
                }
            }
            unset($colData);
        }

        // Etapa 3: Monta os objetos Atributo temporários em memória
        $atributosPorTabela = [];
        foreach ($colunasTempPorTabela as $idTabela => $colunas) {
            $atributosPorTabela[$idTabela] = [];
            foreach ($colunas as $c) {
                $atributosPorTabela[$idTabela][] = new Atributo(
                    $c['id_atributo'],
                    $c['fk_tabela'],
                    $c['fk_atributo'],
                    $c['nome_atributo'],
                    $c['tipo'],
                    $c['pk'],
                    $c['nn'],
                    $c['ai'],
                    $c['uq']
                );
            }
        }

        return [
            'tabelas'   => $tabelas,
            'atributos' => $atributosPorTabela,
        ];
    }

    
    //Monta o contexto de geração para usuários autenticados (lê metadados persistidos no DevStudio).
     
    private function montarContextoGeracaoAutenticado(): array {
        $banco = trim((string) ($_POST['banco'] ?? ''));
        if ($banco === '') {
            throw new \InvalidArgumentException('Selecione um banco de dados antes de gerar o sistema.');
        }

        // Caso o usuário autenticado tenha selecionado um banco salvo pelo ID
        if (ctype_digit($banco)) {
            $bancoSelecionado = (new BancoService())->getBancoById($banco);
            if (!$bancoSelecionado) {
                throw new \InvalidArgumentException('Banco de dados não encontrado.');
            }

            // Etapa 1: tabelas que o usuário desmarcou em "Tabelas Detectadas"
            $tabelas = (new TabelaService())->getTabelasByFk_banco($bancoSelecionado['id_banco']);
            $tabelas = FiltroTabelas::fromRequest($_POST['tabelasDesativadas'] ?? null)->filtrar($tabelas);
            if (empty($tabelas)) {
                throw new \InvalidArgumentException('Selecione ao menos uma tabela para gerar o projeto.');
            }

            // Etapa 2: camadas/artefatos desmarcados em "Opções de Geração" (+ regra RN 06)
            $opcoes = OpcoesGeracao::fromRequest($_POST, $bancoSelecionado);
            if (empty($opcoes->camadasAtivas())) {
                throw new \InvalidArgumentException('Selecione ao menos uma camada ou artefato para gerar.');
            }

            $atributoService = new AtributoService();
            $atributosPorTabela = [];
            foreach ($tabelas as $tabela) {
                $atributosPorTabela[$tabela->getId_tabela()] = $atributoService->getAtributosByFk_tabela($tabela->getId_tabela());
            }

            return [
                'banco'     => $bancoSelecionado,
                'tabelas'   => $tabelas,
                'atributos' => $atributosPorTabela,
                'opcoes'    => $opcoes,
            ];
        }

        return $this->montarContextoGeracaoVisitante();
    }

    
    //Monta o contexto de geração em memória para o Modo Visitante (RN 01 / FA01: zero persistência).
    
    private function montarContextoGeracaoVisitante(): array {
        $nomeBanco = trim((string) ($_POST['banco'] ?? ''));
        if ($nomeBanco === '') {
            throw new \InvalidArgumentException('Selecione um banco de dados antes de gerar o sistema.');
        }

        $cred = $this->obterCredenciaisLocaisRequest();
        $filtro = FiltroTabelas::fromRequest($_POST['tabelasDesativadas'] ?? null);

        $inspecao = $this->inspecionarBancoLocalEmMemoria(
            $nomeBanco,
            $cred['host'],
            $cred['porta'],
            $cred['user'],
            $cred['pass'],
            true,
            $filtro
        );

        if (empty($inspecao['tabelas'])) {
            throw new \InvalidArgumentException('Selecione ao menos uma tabela para gerar o projeto.');
        }

        $bancoMemoria = [
            'id_banco'      => null,
            'nome_banco'    => $nomeBanco,
            'usuario_banco' => $cred['user'],
            'senha_banco'   => $cred['pass'],
            'host'          => $cred['host'],
            'porta'         => $cred['porta'],
        ];

        $opcoes = OpcoesGeracao::fromRequest($_POST, $bancoMemoria);
        if (empty($opcoes->camadasAtivas())) {
            throw new \InvalidArgumentException('Selecione ao menos uma camada ou artefato para gerar.');
        }

        return [
            'banco'     => $bancoMemoria,
            'tabelas'   => $inspecao['tabelas'],
            'atributos' => $inspecao['atributos'],
            'opcoes'    => $opcoes,
        ];
    }

    private function montarContextoGeracao(): array {
        if ($this->usuarioEstaAutenticado()) {
            return $this->montarContextoGeracaoAutenticado();
        }
        return $this->montarContextoGeracaoVisitante();
    }

    //Pré-visualização da etapa "Estrutura de Arquivos": devolve exatamente os caminhos que o
    //MotorGeracao criaria para as tabelas e camadas selecionadas (mesma fonte usada no ZIP).
    public function previewEstrutura(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $ctx = $this->montarContextoGeracao();
            $caminhos = (new MotorGeracao())->listarCaminhos($ctx['tabelas'], $ctx['atributos'], $ctx['opcoes'], $ctx['banco']);

            echo json_encode([
                'sucesso'   => true,
                'visitante' => !$this->usuarioEstaAutenticado(),
                'arquivos'  => $caminhos,
                'total'     => count($caminhos),
                'avisos'    => $ctx['opcoes']->avisos(),
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function gerarMvc(): void {
        header('Content-Type: application/json; charset=utf-8');

        $nomeProjeto = trim((string) ($_POST['nomeProjeto'] ?? '')) ?: 'meu_projeto';

        if (preg_match('/\s/', $nomeProjeto)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do projeto não deve conter espaços.']);
            return;
        }

        // O nome vira nome de pasta (que é apagada e recriada) e de .zip: só caracteres seguros.
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $nomeProjeto)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do projeto deve conter apenas letras, números, "_" e "-".']);
            return;
        }

        // Desvio de fluxo (RF 06 / RN 01): Usuário Autenticado vs Visitante
        if ($this->usuarioEstaAutenticado()) {
            $this->gerarMvcAutenticado($nomeProjeto);
            return;
        }

        $this->gerarMvcVisitante($nomeProjeto);
    }

    
    //Rotina de geração para usuários autenticados (persiste no banco, valida cota de 10 projetos e salva histórico).
     
    private function gerarMvcAutenticado(string $nomeProjeto): void {
        try {
            $ctx = $this->montarContextoGeracaoAutenticado();
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            return;
        } catch (\Throwable $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            return;
        }

        $idUsuario = (int) $_SESSION['usuario_logado']->getIdUsuario();
        $idBanco = isset($ctx['banco']['id_banco']) && $ctx['banco']['id_banco'] !== null ? (int) $ctx['banco']['id_banco'] : null;

        $projeto = new Projeto(
            $idUsuario,
            $idBanco ?? 1,
            null,
            $nomeProjeto,
            date('Y-m-d H:i:s'),
            [
                'comentarios' => $ctx['opcoes']->comentariosAtivos() ? 1 : 0,
                'views'       => $ctx['opcoes']->ativa('views') ? 1 : 0,
            ],
            null
        );

        $resultadoInsert = $this->getProjetoService()->insert($projeto);
        if (!$resultadoInsert) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível salvar o projeto no banco de dados.']);
            return;
        }

        $pastaOutput = __DIR__ . '/../projetos/temp/' . $nomeProjeto;
        $nomeZip = $nomeProjeto . '-' . $resultadoInsert . '.zip';
        $pastaDownloads = __DIR__ . '/../projetos/downloads/';
        if (!is_dir($pastaDownloads)) {
            mkdir($pastaDownloads, 0755, true);
        }
        $arquivoZip = $pastaDownloads . $nomeZip;

        try {
            $this->excluirDiretorioRecursivo($pastaOutput);

            $motor = new MotorGeracao();
            $arquivos = $motor->gerar($ctx['tabelas'], $ctx['atributos'], $ctx['opcoes'], $pastaOutput, $ctx['banco']);

            $geradorZip = new \app\tools\gerador\GeradorZip();
            if (!$geradorZip->compactarPasta($pastaOutput, $arquivoZip)) {
                throw new \RuntimeException('Não foi possível criar o arquivo .zip do projeto.');
            }

            $this->excluirDiretorioRecursivo($pastaOutput);

            $downloadUrl = URL_BASE . '/projetos/downloadZip?file=' . urlencode($nomeZip);
            \app\services\LogService::registrar("Geração de sistema MVC Creator: " . $nomeProjeto);
            $this->registrarProjetoNoHistorico($nomeProjeto, 'MVC Creator', [
                'nome'        => $nomeProjeto,
                'arquivo'     => $nomeZip,
                'downloadUrl' => $downloadUrl,
                'banco'       => $ctx['banco']['nome_banco'],
                'banco_id'    => $ctx['banco']['id_banco'],
                'gerado_em'   => date('c'),
                'tipo'        => 'MVC Creator',
            ]);

            echo json_encode([
                'sucesso'     => true,
                'visitante'   => false,
                'mensagem'    => 'Sistema MVC gerado com sucesso!',
                'downloadUrl' => $downloadUrl,
                'total'       => count($arquivos),
                'arquivos'    => $arquivos,
                'avisos'      => $ctx['opcoes']->avisos(),
            ]);
        } catch (\Throwable $e) {
            $this->excluirDiretorioRecursivo($pastaOutput);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    
    //Rotina de geração para visitantes (RF 06 / UC 06 / RN 01 / FA01):
    //Zero persistência nas tabelas internas (`usuario`, `projeto`, `banco`, `tabela`, `atributo`, `log`) ou histórico.
    //Compila em diretório temporário isolado (`temp/guest_{hash}`) e remove imediatamente após o download.
     
    private function gerarMvcVisitante(string $nomeProjeto): void {
        $this->limparTemporariosVisitanteExpirados();

        try {
            $ctx = $this->montarContextoGeracaoVisitante();
        } catch (\Throwable $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            return;
        }

        $guestHash = substr(hash('sha256', session_id() . '_' . uniqid('', true) . '_' . bin2hex(random_bytes(8))), 0, 16);
        $pastaTempRaiz = __DIR__ . '/../projetos/temp';
        $pastaGuest = $pastaTempRaiz . '/guest_' . $guestHash;
        $pastaBuild = $pastaGuest . '/' . $nomeProjeto;
        $nomeZip = $nomeProjeto . '.zip';
        $arquivoZip = $pastaGuest . '/' . $nomeZip;

        try {
            $this->excluirDiretorioRecursivo($pastaGuest);
            if (!is_dir($pastaBuild) && !mkdir($pastaBuild, 0755, true) && !is_dir($pastaBuild)) {
                throw new \RuntimeException('Não foi possível criar o diretório temporário para geração.');
            }

            $motor = new MotorGeracao();
            $arquivos = $motor->gerar($ctx['tabelas'], $ctx['atributos'], $ctx['opcoes'], $pastaBuild, $ctx['banco']);

            $geradorZip = new \app\tools\gerador\GeradorZip();
            if (!$geradorZip->compactarPasta($pastaBuild, $arquivoZip)) {
                throw new \RuntimeException('Não foi possível criar o arquivo .zip temporário do projeto.');
            }

            // Remove imediatamente os arquivos-fonte descompactados, mantendo apenas o .zip temporário até o envio HTTP
            $this->excluirDiretorioRecursivo($pastaBuild);

            $_SESSION['guest_downloads'][$guestHash] = [
                'dir'        => $pastaGuest,
                'zip'        => $arquivoZip,
                'filename'   => $nomeZip,
                'created_at' => time(),
            ];

            $downloadUrl = URL_BASE . '/projetos/downloadZip?file=' . urlencode($nomeZip) . '&guest=' . urlencode($guestHash);

            echo json_encode([
                'sucesso'     => true,
                'visitante'   => true,
                'mensagem'    => 'Sistema MVC gerado com sucesso!',
                'avisoSessao' => 'Modo Visitante: os dados desta sessão não serão salvos no servidor.',
                'downloadUrl' => $downloadUrl,
                'total'       => count($arquivos),
                'arquivos'    => $arquivos,
                'avisos'      => $ctx['opcoes']->avisos(),
            ]);
        } catch (\Throwable $e) {
            $this->excluirDiretorioRecursivo($pastaGuest);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    /**
     * Remove pastas temporárias de visitantes expiradas (mais de 10 minutos) caso algum download tenha sido cancelado.
     */
    private function limparTemporariosVisitanteExpirados(int $ttlSegundos = 600): void {
        $pastaTempRaiz = __DIR__ . '/../projetos/temp';
        if (!is_dir($pastaTempRaiz)) {
            return;
        }

        $agora = time();
        $itens = array_diff(scandir($pastaTempRaiz) ?: [], ['.', '..']);
        foreach ($itens as $item) {
            if (strpos($item, 'guest_') !== 0) {
                continue;
            }
            $caminho = $pastaTempRaiz . '/' . $item;
            if (is_dir($caminho) && ($agora - @filemtime($caminho)) > $ttlSegundos) {
                $this->excluirDiretorioRecursivo($caminho);
            }
        }
    }

    private function excluirDiretorioRecursivo(string $dir): void {
        if (!file_exists($dir)) return;
        $items = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->excluirDiretorioRecursivo($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    public function downloadZip(): void {
        $nomeZip = basename((string) ($_GET['file'] ?? 'projeto.zip'));
        $guestHash = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['guest'] ?? ''));

        $geradorZip = new \app\tools\gerador\GeradorZip();

        // Fluxo de download temporário para visitante: entrega via HTTP e apaga imediatamente do servidor
        if ($guestHash !== '' && strlen($guestHash) === 16) {
            $pastaGuest = __DIR__ . '/../projetos/temp/guest_' . $guestHash;
            $caminhoZip = $pastaGuest . '/' . $nomeZip;

            if (isset($_SESSION['guest_downloads'][$guestHash])) {
                unset($_SESSION['guest_downloads'][$guestHash]);
            }

            $geradorZip->enviarDownload($caminhoZip, $nomeZip, true, $pastaGuest);
            return;
        }

        $caminhoZip = __DIR__ . '/../projetos/downloads/' . $nomeZip;
        if (!file_exists($caminhoZip)) {
            $caminhoPublic = __DIR__ . '/../../public/downloads/' . $nomeZip;
            if (file_exists($caminhoPublic)) {
                $caminhoZip = $caminhoPublic;
            }
        }
        $geradorZip->enviarDownload($caminhoZip, $nomeZip, false);
    }

    private function getHistoricoUsuarioId(): int {
        $usuario = $_SESSION['usuario_logado'] ?? null;
        if (!$usuario || !method_exists($usuario, 'getIdUsuario')) {
            throw new \RuntimeException('É necessário estar autenticado para acessar o histórico.');
        }

        $usuarioId = (int) $usuario->getIdUsuario();
        if ($usuarioId < 1) {
            throw new \RuntimeException('A conta autenticada não possui um identificador válido.');
        }

        return $usuarioId;
    }

    private function getHistoricoStoragePath(?int $usuarioId = null): string {
        $usuarioId = $usuarioId ?? $this->getHistoricoUsuarioId();
        return __DIR__ . '/../projetos/historicos/usuario_' . $usuarioId . '.json';
    }

    private function migrarHistoricoCompartilhadoAdmin(string $caminhoHistoricoUsuario): void {
        $usuario = $_SESSION['usuario_logado'] ?? null;
        $caminhoLegado = __DIR__ . '/../projetos/historico.json';
        $caminhoMigrado = __DIR__ . '/../projetos/historico_compartilhado_migrado.json';

        if (!$usuario || !method_exists($usuario, 'getTipoPerfil')
            || !in_array(strtolower(trim($usuario->getTipoPerfil())), ['admin', 'administrador'], true)
            || !is_file($caminhoLegado)
            || is_file($caminhoMigrado)) {
            return;
        }

        $historicoAdmin = $this->lerJsonArrayValido($caminhoHistoricoUsuario);
        $historicoLegado = $this->lerJsonArrayValido($caminhoLegado);
        $idsExistentes = array_column($historicoAdmin, 'id');

        foreach ($historicoLegado as $item) {
            if (!in_array($item['id'] ?? '', $idsExistentes, true)) {
                $historicoAdmin[] = $item;
            }
        }

        $historicoAdmin = $this->ordenarPorData($historicoAdmin);
        $historicoAdmin = array_slice($historicoAdmin, 0, 10);
        $this->salvarJsonArrayValido($caminhoHistoricoUsuario, $historicoAdmin);

        if (!@rename($caminhoLegado, $caminhoMigrado)) {
            @copy($caminhoLegado, $caminhoMigrado);
        }
    }

    private function inicializarArquivoJson(string $caminho): void {
        if (file_exists($caminho)) {
            return;
        }

        $diretorio = dirname($caminho);
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0775, true);
        }

        file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function normalizarItemHistorico(array $item): array {
        $id = trim((string)($item['id'] ?? ''));
        if ($id === '') {
            $id = 'proj_' . substr(hash('sha256', uniqid((string) microtime(true), true)), 0, 12);
        }

        $nome = trim((string)($item['nome'] ?? 'Projeto sem nome'));
        if ($nome === '') {
            $nome = 'Projeto sem nome';
        }

        $modulo = trim((string)($item['modulo'] ?? ''));
        if (!in_array($modulo, ['PageMaker', 'MVC Creator'], true)) {
            $modulo = 'MVC Creator';
        }

        $criadoEm = $this->converterStringEmData($item['criado_em'] ?? $item['created_at'] ?? null);
        if ($criadoEm === null) {
            $criadoEm = date('c');
        }

        $conteudo = is_array($item['conteudo'] ?? null) ? $item['conteudo'] : [];
        if ($modulo === 'PageMaker' && empty($conteudo['css']) && is_string($conteudo['html'] ?? null)) {
            if (preg_match('/<style\\b[^>]*>(.*?)<\\/style>/is', $conteudo['html'], $correspondencia)) {
                $conteudo['css'] = trim($correspondencia[1]);
            }
        }

        return [
            'id' => $id,
            'nome' => $nome,
            'modulo' => $modulo,
            'criado_em' => $criadoEm,
            'usuario_id' => (int) ($item['usuario_id'] ?? $this->getHistoricoUsuarioId()),
            'conteudo' => $conteudo,
        ];
    }

    private function converterStringEmData($valor): ?string {
        if (!is_string($valor) || trim($valor) === '') {
            return null;
        }

        try {
            $data = new \DateTimeImmutable($valor);
            return $data->format('c');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function lerJsonArrayValido(string $caminho): array {
        $this->inicializarArquivoJson($caminho);
        $usuarioId = $this->getHistoricoUsuarioId();

        $conteudo = file_get_contents($caminho);
        if ($conteudo === false || trim($conteudo) === '') {
            file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return [];
        }

        $json = json_decode($conteudo, true);
        if (!is_array($json)) {
            file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return [];
        }

        $itensValidos = [];
        foreach ($json as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemNormalizado = $this->normalizarItemHistorico($item);
            if ($itemNormalizado['usuario_id'] === $usuarioId) {
                $itensValidos[] = $itemNormalizado;
            }
        }

        return array_values($itensValidos);
    }

    private function salvarJsonArrayValido(string $caminho, array $itens): void {
        $this->inicializarArquivoJson($caminho);

        $dados = [];
        foreach ($itens as $item) {
            if (is_array($item)) {
                $itemNormalizado = $this->normalizarItemHistorico($item);
                if ($itemNormalizado['usuario_id'] === $this->getHistoricoUsuarioId()) {
                    $dados[] = $itemNormalizado;
                }
            }
        }

        $resultado = file_put_contents($caminho, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($resultado === false) {
            throw new \RuntimeException('Não foi possível salvar os dados do histórico em JSON.');
        }
    }

    private function ordenarPorData(array $itens): array {
        usort($itens, function (array $a, array $b): int {
            $dataA = $this->converterStringEmData($a['criado_em'] ?? null) ?? date('c');
            $dataB = $this->converterStringEmData($b['criado_em'] ?? null) ?? date('c');
            return strtotime($dataB) <=> strtotime($dataA);
        });

        return $itens;
    }

    private function sincronizarHistoricoEArquivos(): array {
        $caminhoHistoricoUsuario = $this->getHistoricoStoragePath();
        $this->migrarHistoricoCompartilhadoAdmin($caminhoHistoricoUsuario);
        $historico = $this->lerJsonArrayValido($caminhoHistoricoUsuario);

        $historico = $this->ordenarPorData($historico);
        $historico = array_slice($historico, 0, 10);
        $this->salvarJsonArrayValido($caminhoHistoricoUsuario, $historico);
        return $historico;
    }

    private function obterItemPorId(string $id): ?array {
        foreach ($this->sincronizarHistoricoEArquivos() as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }

        return null;
    }

    private function removerItemPorId(array &$lista, string $id): ?array {
        foreach ($lista as $indice => $item) {
            if (($item['id'] ?? null) === $id) {
                unset($lista[$indice]);
                return $this->normalizarItemHistorico($item);
            }
        }

        return null;
    }

    private function registrarProjetoNoHistorico(string $nome, string $modulo, array $conteudo = []): array {
        $nome = trim((string) $nome);
        $modulo = trim((string) $modulo);

        if ($nome === '') {
            return ['sucesso' => false, 'mensagem' => 'Informe o nome do projeto para registrar no histórico.'];
        }

        if (!in_array($modulo, ['PageMaker', 'MVC Creator'], true)) {
            return ['sucesso' => false, 'mensagem' => 'Módulo inválido para o histórico.'];
        }

        $historico = $this->sincronizarHistoricoEArquivos();

        $novoItem = [
            'id' => 'proj_' . substr(hash('sha256', uniqid((string) microtime(true), true)), 0, 12),
            'nome' => $nome,
            'modulo' => $modulo,
            'criado_em' => date('c'),
            'usuario_id' => $this->getHistoricoUsuarioId(),
            'conteudo' => $conteudo,
        ];
        $historico[] = $novoItem;
        $historico = $this->ordenarPorData($historico);
        $historico = array_slice($historico, 0, 10);
        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), $historico);


        \app\services\LogService::registrar("Projeto salvo no histórico (" . $modulo . "): " . $nome);
        return ['sucesso' => true, 'item' => $novoItem];
    }

    public function registrarProjetoHistoricoJson(): void {
        $this->autenticacaoJsonRequired();

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            $dados = $_POST;
        }

        $nome = trim((string)($dados['nome'] ?? ''));
        $modulo = trim((string)($dados['modulo'] ?? ''));
        $conteudo = $dados['conteudo'] ?? [];

        if (!is_array($conteudo)) {
            $conteudo = [];
        }

        header('Content-Type: application/json');
        echo json_encode($this->registrarProjetoNoHistorico($nome, $modulo, $conteudo));
        exit();
    }

    public function listarHistoricoJson(): void {
        $this->autenticacaoJsonRequired();
        $historico = $this->sincronizarHistoricoEArquivos();

        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'historico' => $historico,
        ]);
        exit();
    }

    public function baixarProjetoHistorico(): void {
        $this->autenticacaoRequired();

        $id = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
        if ($id === '') {
            http_response_code(400);
            echo 'Identificador do projeto não informado.';
            return;
        }

        $item = $this->obterItemPorId($id);
        if ($item === null) {
            http_response_code(404);
            echo 'Projeto não encontrado no histórico.';
            return;
        }

        $payload = is_array($item['conteudo'] ?? null) ? $item['conteudo'] : [];
        \app\services\LogService::registrar("Download de projeto do histórico (" . ($item['modulo'] ?? 'Geral') . "): " . ($item['nome'] ?? 'Projeto'));

        if (($item['modulo'] ?? '') === 'PageMaker' && ($_GET['arquivo'] ?? '') === 'css') {
            if (!isset($payload['css']) || !is_string($payload['css'])) {
                http_response_code(404);
                echo 'CSS não encontrado para este projeto.';
                return;
            }

            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'pagina'))) . '.css';
            header('Content-Type: text/css; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            echo $payload['css'];
            exit();
        }

        if (($item['modulo'] ?? '') === 'PageMaker' && isset($payload['html'])) {
            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'pagina'))) . '.html';
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            echo (string) $payload['html'];
            exit();
        }

        // Fluxo de download para MVC Creator e projetos com arquivo ZIP
        $nomeProjetoLimpo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'projeto')));
        $nomeDownloadZip = $nomeProjetoLimpo . '.zip';

        $arquivoOriginal = '';
        if (!empty($payload['arquivo']) && is_string($payload['arquivo'])) {
            $arquivoOriginal = basename($payload['arquivo']);
        } elseif (!empty($payload['downloadUrl']) && is_string($payload['downloadUrl'])) {
            $parsedQuery = parse_url($payload['downloadUrl'], PHP_URL_QUERY);
            if ($parsedQuery) {
                parse_str($parsedQuery, $qp);
                if (!empty($qp['file'])) {
                    $arquivoOriginal = basename($qp['file']);
                }
            }
        }

        $caminhoZip = $this->localizarZipProjeto($arquivoOriginal, $nomeProjetoLimpo);

        // Se o arquivo .zip físico não existir no servidor, reconstrói o pacote do projeto
        if ($caminhoZip === null || !file_exists($caminhoZip)) {
            $caminhoZip = $this->reconstruirZipProjetoHistorico($item, $arquivoOriginal, $nomeProjetoLimpo);
        }

        if ($caminhoZip !== null && file_exists($caminhoZip)) {
            $geradorZip = new \app\tools\gerador\GeradorZip();
            $geradorZip->enviarDownload($caminhoZip, $nomeDownloadZip, false);
            exit();
        }

        // Fallback final de segurança caso falhe a reconstrução
        $nomeArquivo = $nomeProjetoLimpo . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function localizarZipProjeto(string $arquivoOriginal, string $nomeProjetoLimpo): ?string {
        $diretorios = [
            __DIR__ . '/../projetos/downloads',
            __DIR__ . '/../../public/downloads',
            __DIR__ . '/../projetos/temp',
            __DIR__ . '/../../public/temp',
        ];

        $candidatos = [];
        foreach ($diretorios as $dir) {
            if (!is_dir($dir)) continue;
            if ($arquivoOriginal !== '') {
                $candidatos[] = $dir . '/' . $arquivoOriginal;
            }
            $candidatos[] = $dir . '/' . $nomeProjetoLimpo . '.zip';
            $globs = glob($dir . '/' . $nomeProjetoLimpo . '*.zip');
            if ($globs) {
                foreach ($globs as $g) {
                    $candidatos[] = $g;
                }
            }
        }

        foreach ($candidatos as $cand) {
            if (is_file($cand) && filesize($cand) > 0) {
                return $cand;
            }
        }

        return null;
    }

    private function reconstruirZipProjetoHistorico(array $item, string $arquivoOriginal, string $nomeProjetoLimpo): ?string {
        $pastaDownloads = __DIR__ . '/../projetos/downloads';
        if (!is_dir($pastaDownloads)) {
            @mkdir($pastaDownloads, 0755, true);
        }

        $nomeArquivoZip = ($arquivoOriginal !== '' && str_ends_with(strtolower($arquivoOriginal), '.zip'))
            ? $arquivoOriginal
            : ($nomeProjetoLimpo . '.zip');
        $arquivoZipDestino = $pastaDownloads . '/' . $nomeArquivoZip;

        $pastaTemp = __DIR__ . '/../projetos/temp/rec_' . uniqid($nomeProjetoLimpo . '_', true);
        $pastaBuild = $pastaTemp . '/' . $nomeProjetoLimpo;
        if (!is_dir($pastaBuild)) {
            @mkdir($pastaBuild, 0755, true);
        }

        $payload = is_array($item['conteudo'] ?? null) ? $item['conteudo'] : [];
        $bancoNome = trim((string)($payload['banco'] ?? ''));
        $gerouComMotor = false;

        if ($bancoNome !== '' && $bancoNome !== 'Banco não informado') {
            try {
                $conn = \app\database\ConnectionFactory::getConnection();
                $stmt = $conn->prepare("SHOW DATABASES LIKE ?");
                $stmt->execute([$bancoNome]);
                if ($stmt->fetch()) {
                    $dbConn = new \PDO("mysql:host=localhost;dbname={$bancoNome};charset=utf8mb4", 'root', '', [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    ]);
                    $tabelasNomes = $dbConn->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
                    if (!empty($tabelasNomes)) {
                        $tabelasObjs = [];
                        $atributosPorTabela = [];
                        $tabelaId = 1;
                        foreach ($tabelasNomes as $tbNome) {
                            $tabelaObj = new \app\models\Tabela($tabelaId, $tbNome, 1);
                            $tabelasObjs[] = $tabelaObj;
                            $cols = $dbConn->query("SHOW COLUMNS FROM `{$tbNome}`")->fetchAll();
                            $attrs = [];
                            $attrId = 1;
                            foreach ($cols as $col) {
                                $tipo = preg_replace('/\(.*/', '', $col['Type']);
                                $isPk = (strtoupper($col['Key'] ?? '') === 'PRI');
                                $attrs[] = new \app\models\Atributo($attrId++, $tabelaId, $col['Field'], $tipo, $isPk);
                            }
                            $atributosPorTabela[$tabelaId] = $attrs;
                            $tabelaId++;
                        }
                        $opcoes = new \app\tools\gerador\OpcoesGeracao(true, true, true, true, true, false, true, true);
                        $bancoData = ['nome_banco' => $bancoNome, 'id_banco' => $payload['banco_id'] ?? null];
                        $motor = new \app\tools\gerador\MotorGeracao();
                        $motor->gerar($tabelasObjs, $atributosPorTabela, $opcoes, $pastaBuild, $bancoData);
                        $gerouComMotor = true;
                    }
                }
            } catch (\Throwable $t) {
                $gerouComMotor = false;
            }
        }

        if (!$gerouComMotor) {
            $subpastas = [
                $pastaBuild . '/app/config',
                $pastaBuild . '/app/controllers',
                $pastaBuild . '/app/core',
                $pastaBuild . '/app/database',
                $pastaBuild . '/app/models',
                $pastaBuild . '/app/repositories',
                $pastaBuild . '/app/services',
                $pastaBuild . '/app/views',
                $pastaBuild . '/public',
            ];
            foreach ($subpastas as $sub) {
                if (!is_dir($sub)) {
                    @mkdir($sub, 0755, true);
                }
            }

            file_put_contents($pastaBuild . '/app/core/Autoload.php', \app\tools\gerador\GeradorCore::gerarAutoload());
            file_put_contents($pastaBuild . '/app/core/Controller.php', \app\tools\gerador\GeradorCore::gerarController());
            file_put_contents($pastaBuild . '/app/core/Router.php', \app\tools\gerador\GeradorCore::gerarRouter());
            file_put_contents($pastaBuild . '/app/database/ConnectionFactory.php', \app\tools\gerador\GeradorRepositorio::gerarConnectionFactory(false));
            file_put_contents($pastaBuild . '/app/config/config.php', \app\tools\gerador\GeradorConfig::gerarConfig('localhost', '3306', $bancoNome ?: 'meu_banco', 'root', false));
            file_put_contents($pastaBuild . '/public/index.php', \app\tools\gerador\geradorIndex::gerarIndex([], true, true, false));

            $nomeExibicao = (string)($item['nome'] ?? $nomeProjetoLimpo);
            $readme = "# Projeto: {$nomeExibicao}\n\n";
            $readme .= "Gerado originalmente pelo DevStudio (MVC Creator).\n";
            if ($bancoNome) {
                $readme .= "- **Banco de Dados:** {$bancoNome}\n";
            }
            if (!empty($item['criado_em'])) {
                $readme .= "- **Data de Criação:** " . date('d/m/Y H:i:s', strtotime($item['criado_em'])) . "\n";
            }
            $readme .= "\n## Estrutura do Sistema MVC\n";
            $readme .= "- `app/config/`: Configurações de banco e ambiente\n";
            $readme .= "- `app/controllers/`: Controladores da aplicação\n";
            $readme .= "- `app/core/`: Roteador, Front Controller e Autoload\n";
            $readme .= "- `app/database/`: Conexão PDO (ConnectionFactory)\n";
            $readme .= "- `app/models/`: Entidades de dados\n";
            $readme .= "- `app/repositories/`: Camada de acesso ao banco (DAO)\n";
            $readme .= "- `app/services/`: Camada de regras de negócio\n";
            $readme .= "- `app/views/`: Telas e formulários da aplicação\n";
            $readme .= "- `public/`: Ponto de entrada web (`index.php`)\n";
            file_put_contents($pastaBuild . '/README.md', $readme);

            foreach (['controllers', 'models', 'repositories', 'services', 'views'] as $sub) {
                file_put_contents($pastaBuild . "/app/{$sub}/.gitkeep", "");
            }
        }

        $geradorZip = new \app\tools\gerador\GeradorZip();
        $compactou = $geradorZip->compactarPasta($pastaBuild, $arquivoZipDestino);
        $this->excluirDiretorioRecursivo($pastaTemp);

        if ($compactou && file_exists($arquivoZipDestino) && filesize($arquivoZipDestino) > 0) {
            return $arquivoZipDestino;
        }

        return null;
    }

    public function apagarProjetoHistorico(): void {
        $this->autenticacaoRequired();

        $id = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
        if ($id === '') {
            $this->redirect(URL_BASE . '/projetos/historico');
        }

        $historico = $this->sincronizarHistoricoEArquivos();
        $item = $this->removerItemPorId($historico, $id);
        if ($item !== null) {
            \app\services\LogService::registrar("Projeto removido do histórico (ID: " . $id . ")");
        }
        if ($item === null) {
            $this->redirect(URL_BASE . '/projetos/historico');
        }

        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), $historico);

        $this->redirect(URL_BASE . '/projetos/historico');
    }

    public function limparHistorico(): void {
        $this->autenticacaoRequired();

        $this->sincronizarHistoricoEArquivos();
        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), []);
        \app\services\LogService::registrar("Histórico de projetos foi limpo pelo usuário.");
        $this->redirect(URL_BASE . '/projetos/historico');
    }

    public function index(): void {
        $this->autenticacaoRequired();
        $this->view('projetos/home');
    }

    public function guia(): void {
        $this->view('projetos/guia');
    }

    

    public function mvcCreator(): void {
        if (!$this->usuarioEstaAutenticado()) {
            // RF 06 / UC 06: Modo Visitante / Convidado — abre o MVC Creator sem exigir login nem tocar no banco
            $this->view('projetos/mvcCreator', [
                'bancoOpts'     => '',
                'modoVisitante' => true,
            ]);
            return;
        }

        $usuarioLogado = $_SESSION['usuario_logado'] ?? null;
        if (!$usuarioLogado || !method_exists($usuarioLogado, 'getEmail')) {
            $this->redirect(URL_BASE . '/login');
        }

        $usuarioService = new UsuarioService();
        $bancoService = new BancoService();
        $usuario = $usuarioService->getUsuarioPorEmail($usuarioLogado->getEmail());

        if (!$usuario || !method_exists($usuario, 'getIdUsuario')) {
            $this->redirect(URL_BASE . '/login');
        }

        $bancos = $bancoService->getBancoByUsuario($usuario->getIdUsuario());
        $bancoOpts = '';
        if (is_array($bancos)) {
            foreach ($bancos as $banco) {
                $bancoOpts .= '<option value="' . $banco['id_banco'] . '">' . $banco['nome_banco'] . '</option>';
            }
        }
        $this->view('projetos/mvcCreator', [
            'bancoOpts'     => $bancoOpts,
            'modoVisitante' => false,
        ]);
    }

    public function pageMaker(): void {
        $this->view('projetos/pageMaker');
    }

    public function historico(): void {
        $this->autenticacaoRequired();
        $historico = $this->sincronizarHistoricoEArquivos();
        $this->view('projetos/historico', ['historico' => $historico]);
    }

    public function cadastrar(): void {
        $this->view("projetos/projeto_create");
    }

    public function editar(): void {
        
        $data = [];
        $id_projeto = $_POST['id_projeto'];
        $proj = $this->getProjetoService()->getById($id_projeto);
        $nome = $proj['nome_projeto'];


        $data['nome'] = $nome;
        $this->view("projetos/projeto_edit",$data);

    }

    public function bools(){
        
        $validador = new Validador();
        $nome   = $_POST['nome'];
        $server = $_POST['server'];
        $user   = $_POST['user'];
        $pass   = $_POST['pass'];
        $banco  = $_POST['mvc-banco'];
        $this->obrigatorios($validador,$nome,$server,$user,$pass,$banco);
        $data = ["nome"=>$nome,"server"=>$server,"user"=>$user,"pass"=>$pass,'banco'=>$banco];
        $this->view("projetos/projeto_bools",$data);
    }

    public function editBools(){
        $validador = new Validador();
        $nome = $_POST['nome'];

        $validador->obrigatorio('nome',$nome);
        if($validador->temErros())$this->view("");
    }

    public function criar(){
        $validador = new Validador();
        $nome   = trim($_POST['nome']);
        $server = trim($_POST['server']);
        $user   = trim($_POST['user']);
        $pass   = trim($_POST['pass']);
        $banco  = trim($_POST['mvc-banco']);
        $options = [];
        foreach($_POST as $key=>$value){
            if(substr($key,0,3)=='opt'){
                $options[substr($key,4)] = (int)$value;
            }
        }
        $this->obrigatorios($validador,$nome,$server,$user,$pass,$banco);
        
        $projeto = new Projeto(1,1,null,$nome,date("Y-m-d H:i:s"),$options,null);


        $this->getProjetoService()->insert($projeto);
        
        $this->redirect(URL_BASE);
        
        // $this->view("projetos/projeto_create");
    }

    private function obrigatorios(Validador $validador, $nome,$server,$user,$pass,$banco){
        $validador->obrigatorio('nome',$nome);
        $validador->obrigatorio('server',$server);
        $validador->obrigatorio('user',$user);
        // $validador->obrigatorio('pass',$pass);
        $validador->obrigatorio('banco',$banco);

        if($validador->temErros()){
            $data['erros'] = $validador->getErros();
            $data['nome'] = $nome;
            $data['server'] = $server;
            $data['user'] = $user;
            $data['pass'] = $pass;
            $data['banco'] = $banco;
            $this->view('/projetos/projeto_create',$data);
            die;
        }
    }

}