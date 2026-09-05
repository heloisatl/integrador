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
        
        <!-- Cabeçalho da Página (DevStudio Identity) -->
        <header class="phpma-page-header">
            <div>
                <h2 class="phpma-header-title">
                    <i class="bi bi-database" style="color: var(--accent);"></i> PHPMeuAmigo
                </h2>
            </div>

            <div>
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

            <div>
                <button type="button" id="phpma-btn-config-banco" class="btn btn-secondary" title="Configurações do Banco (Host, Usuário, Senha)">
                    <i class="bi bi-gear-fill"></i> Configurações do Banco
                </button>
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

                <div>
                    <input type="text" id="phpma-search-table" class="phpma-search-input" placeholder="Filtrar tabelas...">
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

    <?php require_once __DIR__ . '/../include/footer.php'; ?>
    </main>
</div>
</div>

<!-- Script de interatividade visual -->
<script src="<?= URL_BASE ?>/assets/js/phpmeuamigo.js"></script>
</body>

</html>
