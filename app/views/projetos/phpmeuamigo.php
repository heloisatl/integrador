<div id="phpmeuamigo">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <!-- Estilos de formulários globais e do PHPmeuamigo -->
    <link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/form-styles.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/pagina-mvc.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/phpmeuamigo.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <div class="phpma-container">
        
        <!-- Banner Informativo sobre Modelagem Didática -->
        <div class="phpma-card" style="background: rgba(13, 110, 253, 0.08); border: 1px solid rgba(13, 110, 253, 0.25); margin-bottom: 20px; padding: 14px 18px; display: flex; align-items: center; gap: 14px;">
            <i class="bi bi-info-circle-fill" style="font-size: 22px; color: var(--accent, #0d6efd); flex-shrink: 0;"></i>
            <div style="font-size: 13px; color: var(--text); line-height: 1.5;">
                <strong style="color: var(--text);">Ambiente de Modelagem do DevStudio:</strong> Ao criar ou importar um banco de dados, a estrutura de tabelas e atributos é armazenada no modelo do seu projeto. Quaisquer alterações realizadas aqui (criação, edição ou exclusão de tabelas e campos) <strong style="color: var(--text);">não afetarão</strong> o banco de dados MySQL original da sua máquina local.
            </div>
        </div>

        <!-- Cabeçalho da Página (DevStudio Identity) -->
        <header class="phpma-page-header">
            <div>
                <h2 class="phpma-header-title">
                    <i class="bi bi-database" style="color: var(--accent);"></i> PHPMeuAmigo
                </h2>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="button" id="phpma-btn-import-sql" class="btn btn-secondary" onclick="window.phpmaOpenImportModal()">
                    <i class="bi bi-download"></i> Importar Banco Local
                </button>
                <button type="button" id="phpma-btn-novo-banco" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Novo Banco
                </button>
            </div>
        </header>

        <!-- Barra Superior de Controle (Entidade: BANCO) -->
        <div class="phpma-top-bar">
            <div class="phpma-banco-picker">
                <label for="phpma-select-banco">
                    <i class="bi bi-hdd-network"></i> Banco Ativo:
                </label>
                <select id="phpma-select-banco" class="phpma-select-banco">
                    <!-- Opções dinâmicas via JS -->
                </select>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="button" id="phpma-btn-config-banco" class="btn btn-secondary" title="Configurações do Banco (Host, Usuário, Senha)">
                    <i class="bi bi-gear-fill"></i> Configurações
                </button>
                <button type="button" id="phpma-btn-excluir-banco" class="btn btn-secondary" style="color:#ff6b6b; border-color:rgba(255,107,107,0.3);" onclick="window.phpmaDeleteBancoActive()" title="Excluir Banco de Dados do DevStudio">
                    <i class="bi bi-trash"></i> Excluir Banco
                </button>
            </div>
        <!-- Caixa de Alerta / Mensagens Inline do Sistema (DevStudio - Tom #5B6AF0) -->
        <div id="phpma-inline-alert" class="phpma-inline-alert" style="display: none;" role="alert">
            <div class="phpma-inline-alert-body">
                <div id="phpma-alert-msg" class="phpma-inline-alert-msg"></div>
            </div>
        </div>

        <!-- Layout em Grid (Sidebar de Tabelas + Editor de Atributos) -->
        <div class="phpma-main-layout">
            
            <!-- Painel Lateral: Tabelas (Entidade: TABELA) -->
            <aside class="phpma-card phpma-sidebar-panel">
                <div class="phpma-card-title">
                    <span><i class="bi bi-table" style="color: var(--accent);"></i> Tabelas</span>
                    <button type="button" id="phpma-btn-nova-tabela" class="btn btn-primary" style="padding: 4px 10px; font-size: 12px;" title="Criar Nova Tabela">
                        <i class="bi bi-plus-lg"></i> Tabela
                    </button>
                </div>

                <!-- Formulário inline padronizado para criação de tabela (substitui o prompt) -->
                <div id="phpma-wrapper-nova-tabela" style="display: none; padding: 4px 0 8px;">
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <input type="text" id="phpma-input-nova-tabela" class="phpma-search-input" placeholder="nome_tabela" style="font-size: 12px; padding: 6px 10px;">
                        <button type="button" id="phpma-btn-confirm-nova-tabela" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;" title="Confirmar Criação">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button type="button" id="phpma-btn-cancel-nova-tabela" class="btn btn-secondary" style="padding: 5px 8px; font-size: 12px;" title="Cancelar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div id="phpma-list-tabelas" class="phpma-tabelas-list">
                    <!-- Lista renderizada dinamicamente via JS -->
                </div>
            </aside>

            <!-- Painel Principal: Atributos (Entidade: ATRIBUTO) -->
            <main class="phpma-card">
                
                <div class="phpma-table-editor-header">
                    <div class="phpma-table-name-field">
                        <label for="phpma-input-tabela-nome">Nome da Tabela:</label>
                        <input type="text" id="phpma-input-tabela-nome" class="phpma-input-tabela-nome" placeholder="nome_tabela">
                    </div>

                    <div>
                        <button type="button" id="phpma-btn-add-atributo" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Adicionar Atributo
                        </button>
                    </div>
                </div>

                <!-- Tabela de Atributos/Campos -->
                <div class="phpma-table-responsive">
                    <table class="phpma-table">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;">#</th>
                                <th>Nome do Atributo</th>
                                <th style="width: 140px;">Tipo de Dado</th>
                                <th style="width: 160px;">FK (Chave Estrangeira)</th>
                                <th style="width: 55px; text-align: center;" title="Chave Primária">PK</th>
                                <th style="width: 55px; text-align: center;" title="Não Nulo">NN</th>
                                <th style="width: 55px; text-align: center;" title="Auto Incremento">AI</th>
                                <th style="width: 55px; text-align: center;" title="Único">UQ</th>
                                <th style="width: 50px; text-align: center;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="phpma-tbody-atributos">
                            <!-- Atributos renderizados via JS -->
                        </tbody>
                    </table>
                </div>

            </main>
        </div>

    </div>

    <!-- Modal de Configuração do Banco de Dados (`banco`) -->
    <div id="phpma-modal-banco" class="phpma-modal-overlay">
        <div class="phpma-modal-content">
            <div class="phpma-modal-head">
                <h3><i class="bi bi-hdd-stack" style="color: var(--accent);"></i> Configurações do Banco</h3>
                <button type="button" class="btn-icon-danger" onclick="window.phpmaCloseModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Alerta inline interno do modal -->
            <div id="phpma-modal-banco-alert" class="phpma-inline-alert" style="display: none; margin-bottom: 16px;">
                <div class="phpma-inline-alert-body">
                    <div class="phpma-inline-alert-msg"></div>
                </div>
            </div>

            <div class="phpma-grid-fields">
                <div class="form-group field-full">
                    <label for="modal-input-nome-banco">Nome do Banco (`nome_banco`)</label>
                    <input type="text" id="modal-input-nome-banco" placeholder="ex: mvc_creator">
                </div>

                <div class="form-group">
                    <label for="modal-input-usr-banco">Usuário (`usuario_banco`)</label>
                    <input type="text" id="modal-input-usr-banco" placeholder="root">
                </div>

                <div class="form-group">
                    <label for="modal-input-pass-banco">Senha (`senha_banco`)</label>
                    <input type="password" id="modal-input-pass-banco" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label for="modal-input-host-banco">Host (`host`)</label>
                    <input type="text" id="modal-input-host-banco" value="localhost" placeholder="localhost">
                </div>

                <div class="form-group">
                    <label for="modal-input-porta-banco">Porta (`porta`)</label>
                    <input type="text" id="modal-input-porta-banco" value="3306" placeholder="3306">
                </div>
            </div>

            <div class="phpma-modal-foot">
                <button type="button" class="btn btn-secondary" onclick="window.phpmaCloseModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="window.phpmaSaveBancoModal()">Salvar Alterações</button>
            </div>
        </div>
    </div>

    <!-- Modal de Importação de Banco de Dados Local (MySQL Local) -->
    <div id="phpma-modal-import-sql" class="phpma-modal-overlay">
        <div class="phpma-modal-content">
            <div class="phpma-modal-head">
                <h3><i class="bi bi-hdd-network" style="color: var(--accent);"></i> Importar Banco de Dados Local</h3>
                <button type="button" class="btn-icon-danger" onclick="window.phpmaCloseImportModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Alerta inline interno do modal de importação -->
            <div id="phpma-modal-import-alert" class="phpma-inline-alert" style="display: none; margin-bottom: 16px;">
                <div class="phpma-inline-alert-body">
                    <div class="phpma-inline-alert-msg"></div>
                </div>
            </div>

            <div class="phpma-grid-fields">
                <div class="form-group">
                    <label for="modal-input-import-host">Host</label>
                    <input type="text" id="modal-input-import-host" value="localhost" placeholder="localhost">
                </div>

                <div class="form-group">
                    <label for="modal-input-import-porta">Porta</label>
                    <input type="text" id="modal-input-import-porta" value="3306" placeholder="3306">
                </div>

                <div class="form-group">
                    <label for="modal-input-import-usr">Usuário</label>
                    <input type="text" id="modal-input-import-usr" value="root" placeholder="root">
                </div>

                <div class="form-group">
                    <label for="modal-input-import-pass">Senha</label>
                    <input type="password" id="modal-input-import-pass" placeholder="••••••••">
                </div>

                <div class="form-group field-full">
                    <button type="button" id="phpma-btn-conectar-local" class="btn btn-secondary" style="width:100%;">
                        <i class="bi bi-arrow-repeat"></i> Conectar e Listar Bancos da Máquina
                    </button>
                </div>

                <div id="phpma-wrapper-select-bancos-locais" class="form-group field-full" style="display: none;">
                    <label for="modal-select-banco-local"><i class="bi bi-database-check" style="color: var(--accent);"></i> Selecione o Banco de Dados para Importar:</label>
                    <select id="modal-select-banco-local" class="phpma-select-banco" style="width:100%; font-size:14px; padding:8px;">
                        <!-- Preenchido dinamicamente via JS -->
                    </select>
                </div>
            </div>

            <div class="phpma-modal-foot">
                <button type="button" class="btn btn-secondary" onclick="window.phpmaCloseImportModal()">Cancelar</button>
                <button type="button" id="phpma-btn-submit-import-local" class="btn btn-primary" disabled onclick="window.phpmaExecutarImportacaoLocal()">
                    <i class="bi bi-download"></i> Importar Banco Selecionado
                </button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../include/footer.php'; ?>
    </main>
</div>
</div>

<!-- Script de interatividade visual -->
<script src="<?= URL_BASE ?>/assets/js/phpmeuamigo.js"></script>
</body>

</html>
