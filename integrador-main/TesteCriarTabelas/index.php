<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "controller.php";

$controller = new Controller();

$conn       = null;
$bancos     = [];
$tabelas    = [];
$colunas    = [];
$feedback   = null;   // ['type' => 'ok'|'err', 'msg' => '...']
$sqlGerado  = '';

// ── Credenciais persistentes via sessão ──────────────────────────────────────
session_start();

$host   = $_POST['host']   ?? $_SESSION['host']   ?? 'localhost';
$user   = $_POST['user']   ?? $_SESSION['user']   ?? 'root';
$pass   = $_POST['pass']   ?? $_SESSION['pass']   ?? '';
$dbname = $_POST['dbname'] ?? $_SESSION['dbname'] ?? '';

// Ação vinda do formulário
$acao = $_POST['acao'] ?? '';

// ── 1. Conectar / trocar banco ───────────────────────────────────────────────
if ($acao === 'conectar' || $acao === 'selecionar_banco' || $acao === 'criar_tabela') {

    $_SESSION['host']   = $host;
    $_SESSION['user']   = $user;
    $_SESSION['pass']   = $pass;
    $_SESSION['dbname'] = $dbname;

    $db   = new Database($host, $user, $pass, $dbname ?: null);
    $conn = $db->connect();

    if (!$conn) {
        $feedback = ['type' => 'err', 'msg' => '❌ Falha ao conectar. Verifique host, usuário e senha.'];
    } else {
        $bancos = $controller->listarBancos($conn);

        if ($dbname) {
            $tabelas = $controller->listarTabelas($conn);

            // Ver colunas de tabela específica
            $tabelaVer = $_POST['ver_tabela'] ?? '';
            if ($tabelaVer) {
                $colunas = $controller->descreverTabela($conn, $tabelaVer);
            }

            // ── 2. Criar tabela ──────────────────────────────────────────
            if ($acao === 'criar_tabela') {
                $nomeTabela = trim($_POST['nome_tabela'] ?? '');
                $campos     = $_POST['campos'] ?? [];

                if ($nomeTabela === '') {
                    $feedback = ['type' => 'err', 'msg' => '❌ Informe o nome da tabela.'];
                } else {
                    try {
                        $sqlGerado = $controller->criarTabela($conn, $nomeTabela, $campos);
                        $tabelas   = $controller->listarTabelas($conn); // atualiza lista
                        $feedback  = ['type' => 'ok',
                                      'msg'  => "✅ Tabela <strong>$nomeTabela</strong> criada com sucesso!"];
                    } catch (Exception $e) {
                        $feedback = ['type' => 'err', 'msg' => '❌ Erro: ' . $e->getMessage()];
                    }
                }
            }
        }

        if (!$feedback) {
            $feedback = ['type' => 'ok', 'msg' => '✅ Conectado com sucesso!'];
        }
    }
}

// Bancos para o select dinâmico (AJAX)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'bancos') {
    header('Content-Type: application/json');
    $db   = new Database($_GET['host'], $_GET['user'], $_GET['pass']);
    $conn = $db->connect();
    if ($conn) {
        echo json_encode($controller->listarBancos($conn));
    } else {
        echo json_encode([]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DB Manager</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:       #0d0f14;
  --surface:  #151820;
  --border:   #252a38;
  --accent:   #00e5a0;
  --accent2:  #0077ff;
  --danger:   #ff4d6d;
  --text:     #e8eaf0;
  --muted:    #5a6078;
  --mono:     'JetBrains Mono', monospace;
  --sans:     'Syne', sans-serif;
  --radius:   10px;
  --glow:     0 0 20px rgba(0,229,160,.15);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--sans);
  min-height: 100vh;
  display: grid;
  grid-template-columns: 300px 1fr;
  grid-template-rows: auto 1fr;
}

/* ── Header ── */
header {
  grid-column: 1/-1;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 18px 32px;
  display: flex;
  align-items: center;
  gap: 16px;
}
header .logo {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -1px;
  color: var(--accent);
}
header .logo span { color: var(--text); }
header .sub {
  font-family: var(--mono);
  font-size: 11px;
  color: var(--muted);
  letter-spacing: 2px;
  text-transform: uppercase;
}

/* ── Sidebar ── */
aside {
  background: var(--surface);
  border-right: 1px solid var(--border);
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  overflow-y: auto;
}

.section-label {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 10px;
}

/* ── Form elements ── */
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 12px; color: var(--muted); font-family: var(--mono); }
.field input, .field select {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text);
  font-family: var(--mono);
  font-size: 13px;
  padding: 9px 12px;
  outline: none;
  transition: border .2s, box-shadow .2s;
  width: 100%;
}
.field input:focus, .field select:focus {
  border-color: var(--accent);
  box-shadow: var(--glow);
}
.field select option { background: var(--bg); }

/* ── Buttons ── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: var(--radius);
  border: none;
  cursor: pointer;
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 700;
  transition: all .2s;
}
.btn-primary {
  background: var(--accent);
  color: #0d0f14;
  width: 100%;
  justify-content: center;
}
.btn-primary:hover { filter: brightness(1.1); box-shadow: var(--glow); }

.btn-secondary {
  background: transparent;
  color: var(--accent2);
  border: 1px solid var(--accent2);
  width: 100%;
  justify-content: center;
}
.btn-secondary:hover { background: rgba(0,119,255,.1); }

.btn-danger {
  background: transparent;
  color: var(--danger);
  border: 1px solid transparent;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
}
.btn-danger:hover { border-color: var(--danger); background: rgba(255,77,109,.1); }

.btn-add {
  background: rgba(0,229,160,.08);
  color: var(--accent);
  border: 1px dashed var(--accent);
  width: 100%;
  justify-content: center;
  padding: 8px;
  font-size: 13px;
  border-radius: var(--radius);
  cursor: pointer;
  font-family: var(--sans);
  font-weight: 700;
  transition: all .2s;
}
.btn-add:hover { background: rgba(0,229,160,.15); }

/* ── Table list ── */
.table-list { display: flex; flex-direction: column; gap: 4px; }
.table-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-family: var(--mono);
  font-size: 12px;
  color: var(--muted);
  border: 1px solid transparent;
  transition: all .15s;
  background: none;
  width: 100%;
  text-align: left;
}
.table-item:hover { background: rgba(255,255,255,.04); color: var(--text); border-color: var(--border); }
.table-item.active { background: rgba(0,229,160,.08); color: var(--accent); border-color: rgba(0,229,160,.25); }
.table-item .icon { font-size: 14px; }

/* ── Main panel ── */
main {
  padding: 32px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
}
.panel-title {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.panel-title .dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}

/* ── Feedback ── */
.feedback {
  padding: 12px 16px;
  border-radius: var(--radius);
  font-size: 13px;
  font-family: var(--mono);
}
.feedback.ok  { background: rgba(0,229,160,.1);  border: 1px solid rgba(0,229,160,.3);  color: var(--accent); }
.feedback.err { background: rgba(255,77,109,.1); border: 1px solid rgba(255,77,109,.3); color: var(--danger); }

/* ── Describe table ── */
.describe-table { width: 100%; border-collapse: collapse; font-family: var(--mono); font-size: 12px; }
.describe-table th {
  background: var(--bg);
  color: var(--muted);
  padding: 8px 12px;
  text-align: left;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  font-size: 10px;
  border-bottom: 1px solid var(--border);
}
.describe-table td {
  padding: 8px 12px;
  border-bottom: 1px solid var(--border);
  color: var(--text);
}
.describe-table tr:hover td { background: rgba(255,255,255,.02); }
.tag {
  display: inline-block;
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
}
.tag-pk  { background: rgba(0,229,160,.15); color: var(--accent); }
.tag-uni { background: rgba(0,119,255,.15);  color: var(--accent2); }
.tag-mul { background: rgba(255,200,0,.12);  color: #ffc800; }

/* ── Column builder ── */
#campos-wrapper { display: flex; flex-direction: column; gap: 10px; }

.campo-row {
  display: grid;
  grid-template-columns: 1.6fr 1.2fr .7fr auto auto auto auto;
  gap: 8px;
  align-items: center;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 10px 12px;
  animation: slideIn .2s ease;
}
@keyframes slideIn {
  from { opacity:0; transform: translateY(-6px); }
  to   { opacity:1; transform: translateY(0); }
}

.campo-row input, .campo-row select {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text);
  font-family: var(--mono);
  font-size: 12px;
  padding: 7px 10px;
  outline: none;
  transition: border .2s;
  width: 100%;
}
.campo-row input:focus, .campo-row select:focus { border-color: var(--accent); }

.checkbox-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  font-size: 10px;
  color: var(--muted);
  font-family: var(--mono);
}
.checkbox-group input[type=checkbox] {
  width: 15px; height: 15px;
  accent-color: var(--accent);
  cursor: pointer;
}

/* SQL preview */
.sql-preview {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 14px 16px;
  font-family: var(--mono);
  font-size: 12px;
  color: var(--accent);
  white-space: pre-wrap;
  word-break: break-all;
  margin-top: 8px;
}

.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.badge-count {
  background: var(--accent);
  color: #0d0f14;
  font-size: 10px;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 20px;
  font-family: var(--mono);
}

@media (max-width: 900px) {
  body { grid-template-columns: 1fr; }
  aside { border-right: none; border-bottom: 1px solid var(--border); }
  .campo-row { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

<header>
  <div>
    <div class="logo">DB<span>Manager</span></div>
    <div class="sub">MySQL · Local · PDO</div>
  </div>
</header>

<!-- ════════════════ SIDEBAR ════════════════ -->
<aside>

  <!-- Conexão -->
  <div>
    <div class="section-label">Conexão</div>
    <form method="POST" id="form-conexao">
      <input type="hidden" name="acao" value="conectar">
      <div class="field" style="margin-bottom:10px">
        <label>Host</label>
        <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" placeholder="localhost">
      </div>
      <div class="field" style="margin-bottom:10px">
        <label>Usuário</label>
        <input type="text" name="user" value="<?= htmlspecialchars($user) ?>" placeholder="root">
      </div>
      <div class="field" style="margin-bottom:10px">
        <label>Senha</label>
        <input type="password" name="pass" id="inp-pass" value="<?= htmlspecialchars($pass) ?>"
               placeholder="(vazia = Enter)" onblur="carregarBancos()">
      </div>
      <button type="submit" class="btn btn-primary">⚡ Conectar</button>
    </form>
  </div>

  <!-- Banco -->
  <?php if ($bancos): ?>
  <div>
    <div class="section-label">Banco de Dados</div>
    <form method="POST" id="form-banco">
      <input type="hidden" name="acao" value="selecionar_banco">
      <input type="hidden" name="host" value="<?= htmlspecialchars($host) ?>">
      <input type="hidden" name="user" value="<?= htmlspecialchars($user) ?>">
      <input type="hidden" name="pass" value="<?= htmlspecialchars($pass) ?>">
      <div class="field" style="margin-bottom:10px">
        <label>Selecione o banco</label>
        <select name="dbname" id="sel-banco" onchange="this.form.submit()">
          <option value="">— escolha —</option>
          <?php foreach ($bancos as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"
              <?= $b === $dbname ? 'selected' : '' ?>>
              <?= htmlspecialchars($b) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- Tabelas -->
  <?php if ($tabelas): ?>
  <div>
    <div class="section-label" style="display:flex;justify-content:space-between;align-items:center">
      Tabelas
      <span class="badge-count"><?= count($tabelas) ?></span>
    </div>
    <div class="table-list">
      <?php foreach ($tabelas as $t): ?>
      <form method="POST" style="margin:0">
        <input type="hidden" name="acao"       value="selecionar_banco">
        <input type="hidden" name="host"       value="<?= htmlspecialchars($host) ?>">
        <input type="hidden" name="user"       value="<?= htmlspecialchars($user) ?>">
        <input type="hidden" name="pass"       value="<?= htmlspecialchars($pass) ?>">
        <input type="hidden" name="dbname"     value="<?= htmlspecialchars($dbname) ?>">
        <input type="hidden" name="ver_tabela" value="<?= htmlspecialchars($t) ?>">
        <button type="submit"
          class="table-item <?= ($t === ($_POST['ver_tabela'] ?? '')) ? 'active' : '' ?>">
          <span class="icon">🗄</span><?= htmlspecialchars($t) ?>
        </button>
      </form>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</aside>

<!-- ════════════════ MAIN ════════════════ -->
<main>

  <!-- Feedback -->
  <?php if ($feedback): ?>
  <div class="feedback <?= $feedback['type'] ?>">
    <?= $feedback['msg'] ?>
  </div>
  <?php endif; ?>

  <?php if ($sqlGerado): ?>
  <div class="panel">
    <div class="panel-title"><span class="dot"></span> SQL Executado</div>
    <div class="sql-preview"><?= htmlspecialchars($sqlGerado) ?></div>
  </div>
  <?php endif; ?>

  <!-- Descrever tabela selecionada -->
  <?php if ($colunas): ?>
  <div class="panel">
    <div class="panel-title">
      <span class="dot" style="background:var(--accent2);box-shadow:0 0 8px var(--accent2)"></span>
      Estrutura: <code style="font-family:var(--mono);font-size:14px"><?= htmlspecialchars($_POST['ver_tabela']) ?></code>
    </div>
    <table class="describe-table">
      <thead>
        <tr>
          <th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Padrão</th><th>Extra</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($colunas as $col): ?>
        <tr>
          <td><?= htmlspecialchars($col['Field']) ?></td>
          <td><code style="color:var(--accent2)"><?= htmlspecialchars($col['Type']) ?></code></td>
          <td><?= $col['Null'] ?></td>
          <td>
            <?php if ($col['Key'] === 'PRI'): ?><span class="tag tag-pk">PK</span>
            <?php elseif ($col['Key'] === 'UNI'): ?><span class="tag tag-uni">UNI</span>
            <?php elseif ($col['Key'] === 'MUL'): ?><span class="tag tag-mul">MUL</span>
            <?php else: echo '—'; endif; ?>
          </td>
          <td><?= $col['Default'] !== null ? htmlspecialchars($col['Default']) : '<span style="color:var(--muted)">NULL</span>' ?></td>
          <td><code style="color:var(--muted);font-size:11px"><?= htmlspecialchars($col['Extra']) ?: '—' ?></code></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Criar tabela -->
  <?php if ($dbname && $conn): ?>
  <div class="panel">
    <div class="panel-title"><span class="dot"></span> Nova Tabela em <em style="color:var(--accent)"><?= htmlspecialchars($dbname) ?></em></div>

    <form method="POST" id="form-criar">
      <input type="hidden" name="acao"   value="criar_tabela">
      <input type="hidden" name="host"   value="<?= htmlspecialchars($host) ?>">
      <input type="hidden" name="user"   value="<?= htmlspecialchars($user) ?>">
      <input type="hidden" name="pass"   value="<?= htmlspecialchars($pass) ?>">
      <input type="hidden" name="dbname" value="<?= htmlspecialchars($dbname) ?>">

      <div class="field" style="margin-bottom:20px;max-width:320px">
        <label>Nome da tabela</label>
        <input type="text" name="nome_tabela" id="inp-nome-tabela"
               placeholder="ex: clientes" oninput="atualizarPreview()" required>
      </div>

      <!-- Cabeçalho das colunas -->
      <div style="display:grid;grid-template-columns:1.6fr 1.2fr .7fr auto auto auto auto;gap:8px;padding:0 12px;margin-bottom:6px">
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px">NOME</span>
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px">TIPO</span>
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px">TAMANHO</span>
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px;text-align:center">PK</span>
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px;text-align:center">NN</span>
        <span style="font-size:10px;color:var(--muted);font-family:var(--mono);letter-spacing:1px;text-align:center">AI</span>
        <span></span>
      </div>

      <div id="campos-wrapper"></div>

      <button type="button" class="btn-add" style="margin-top:10px" onclick="adicionarCampo()">
        + Adicionar campo
      </button>

      <!-- Preview SQL -->
      <div style="margin-top:20px">
        <div class="section-label">Preview SQL</div>
        <div class="sql-preview" id="sql-preview">-- preencha os campos acima</div>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:20px;max-width:240px">
        🚀 Criar Tabela
      </button>
    </form>
  </div>
  <?php elseif (!$dbname && $conn): ?>
  <div class="panel" style="text-align:center;padding:48px;color:var(--muted)">
    <div style="font-size:40px;margin-bottom:12px">🗄️</div>
    <div style="font-size:14px">Selecione um banco de dados na barra lateral<br>para ver as tabelas e criar novas.</div>
  </div>
  <?php else: ?>
  <div class="panel" style="text-align:center;padding:60px;color:var(--muted)">
    <div style="font-size:48px;margin-bottom:16px">⚡</div>
    <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">Bem-vindo ao DB Manager</div>
    <div style="font-size:13px">Insira as credenciais e clique em <strong style="color:var(--accent)">Conectar</strong></div>
  </div>
  <?php endif; ?>

</main>

<script>
// ── Tipos MySQL disponíveis ──────────────────────────────────────────────────
const TIPOS = [
  'INT','TINYINT','SMALLINT','MEDIUMINT','BIGINT',
  'VARCHAR','CHAR','TEXT','MEDIUMTEXT','LONGTEXT',
  'DATE','DATETIME','TIMESTAMP',
  'FLOAT','DOUBLE','DECIMAL',
  'BOOLEAN','ENUM','JSON'
];

// Tipos que não precisam de tamanho
const SEM_TAMANHO = new Set([
  'INT','TINYINT','SMALLINT','MEDIUMINT','BIGINT',
  'TEXT','MEDIUMTEXT','LONGTEXT','DATE','DATETIME',
  'TIMESTAMP','BOOLEAN','FLOAT','DOUBLE','JSON'
]);

let contadorCampos = 0;

function adicionarCampo(nome='', tipo='VARCHAR', tamanho='', pk=false, nn=false, ai=false) {
  const idx = contadorCampos++;
  const wrapper = document.getElementById('campos-wrapper');

  const tiposOptions = TIPOS.map(t =>
    `<option value="${t}" ${t === tipo ? 'selected' : ''}>${t}</option>`
  ).join('');

  const div = document.createElement('div');
  div.className = 'campo-row';
  div.id = `campo-${idx}`;
  div.innerHTML = `
    <input type="text"   name="campos[${idx}][nome]"    placeholder="nome_coluna"
           value="${nome}" oninput="atualizarPreview()">
    <select name="campos[${idx}][tipo]" onchange="toggleTamanho(this, ${idx}); atualizarPreview()">
      ${tiposOptions}
    </select>
    <input type="text"   name="campos[${idx}][tamanho]" placeholder="255"
           value="${tamanho}" id="tam-${idx}" oninput="atualizarPreview()"
           ${SEM_TAMANHO.has(tipo) ? 'disabled style="opacity:.3"' : ''}>
    <div class="checkbox-group">
      <input type="checkbox" name="campos[${idx}][pk]" ${pk?'checked':''}
             onchange="handlePK(this,${idx}); atualizarPreview()">
      <span>PK</span>
    </div>
    <div class="checkbox-group">
      <input type="checkbox" name="campos[${idx}][nn]" ${nn?'checked':''}
             onchange="atualizarPreview()">
      <span>NN</span>
    </div>
    <div class="checkbox-group">
      <input type="checkbox" name="campos[${idx}][ai]" ${ai?'checked':''}
             onchange="atualizarPreview()">
      <span>AI</span>
    </div>
    <button type="button" class="btn btn-danger" onclick="removerCampo(${idx})">✕</button>
  `;
  wrapper.appendChild(div);
  atualizarPreview();
}

function removerCampo(idx) {
  const el = document.getElementById(`campo-${idx}`);
  if (el) { el.style.opacity = '0'; el.style.transform = 'scale(.95)'; el.style.transition = '.15s'; }
  setTimeout(() => { el?.remove(); atualizarPreview(); }, 150);
}

function toggleTamanho(sel, idx) {
  const inp = document.getElementById(`tam-${idx}`);
  if (SEM_TAMANHO.has(sel.value)) {
    inp.disabled = true; inp.style.opacity = '.3'; inp.value = '';
  } else {
    inp.disabled = false; inp.style.opacity = '1';
  }
}

function handlePK(cb, idx) {
  // Se marcar PK, marca NN automaticamente
  if (cb.checked) {
    const nn = cb.closest('.campo-row').querySelector(`[name="campos[${idx}][nn]"]`);
    if (nn) nn.checked = true;
  }
}

function atualizarPreview() {
  const tabela = document.getElementById('inp-nome-tabela')?.value || 'nova_tabela';
  const rows = document.querySelectorAll('#campos-wrapper .campo-row');
  const partes = [];

  rows.forEach(row => {
    const nome    = row.querySelector('input[name*="[nome]"]')?.value?.trim();
    const tipoEl  = row.querySelector('select[name*="[tipo]"]');
    const tipo    = tipoEl?.value || 'VARCHAR';
    const tamanho = row.querySelector('input[name*="[tamanho]"]')?.value?.trim();
    const pk      = row.querySelector('input[name*="[pk]"]')?.checked;
    const nn      = row.querySelector('input[name*="[nn]"]')?.checked;
    const ai      = row.querySelector('input[name*="[ai]"]')?.checked;

    if (!nome) return;
    let def = `\`${nome}\` ${tipo}`;
    if (tamanho && !SEM_TAMANHO.has(tipo)) def += `(${tamanho})`;
    if (nn || pk) def += ' NOT NULL';
    if (ai) def += ' AUTO_INCREMENT';
    if (pk) def += ' PRIMARY KEY';
    partes.push(def);
  });

  const sql = partes.length
    ? `CREATE TABLE \`${tabela}\` (\n  ${partes.join(',\n  ')}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`
    : '-- adicione campos acima';

  document.getElementById('sql-preview').textContent = sql;
}

// Carrega bancos via AJAX ao sair do campo senha
function carregarBancos() {
  const host = document.querySelector('[name=host]')?.value;
  const user = document.querySelector('[name=user]')?.value;
  const pass = document.getElementById('inp-pass')?.value;
  if (!host || !user) return;
  // O carregamento real acontece via submit do form; aqui apenas um placeholder
}

// Inicializa com 1 campo padrão (id PK AI INT) + 1 campo nome
<?php if ($dbname && $conn): ?>
adicionarCampo('id',   'INT',     '', true,  true,  true);
adicionarCampo('nome', 'VARCHAR', '100', false, true, false);
<?php endif; ?>
</script>

</body>
</html>