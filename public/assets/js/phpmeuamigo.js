/**
 * PHPmeuamigo - Frontend UI Interactive Script (DevStudio Design System)
 * Real-time AJAX persistence layer connecting UI to DevStudio MVC Backend API
 */

(function () {
    'use strict';

    let state = {
        bancos: [],
        activeBancoId: null,
        tabelas: [],
        activeTabelaId: null,
        atributos: []
    };

    const TIPOS_DADOS = [
        'INT',
        'VARCHAR(60)',
        'VARCHAR(255)',
        'TEXT',
        'MEDIUMTEXT',
        'DATETIME',
        'TINYINT',
        'ENUM',
        'DECIMAL(10,2)',
        'BIGINT'
    ];

    document.addEventListener("DOMContentLoaded", function () {
        initUI();
    });

    async function initUI() {
        bindEvents();
        await carregarBancosBackend();
    }

    // --- CARREGAMENTO DE DADOS DO BACKEND ---

    async function carregarBancosBackend(targetBancoId = null) {
        try {
            const resp = await fetch('/phpmeuamigo/bancos');
            const data = await resp.json();

            if (data.sucesso) {
                state.bancos = data.bancos || [];
                if (state.bancos.length > 0) {
                    if (targetBancoId && state.bancos.some(b => b.id_banco == targetBancoId)) {
                        state.activeBancoId = parseInt(targetBancoId);
                    } else if (!state.activeBancoId || !state.bancos.some(b => b.id_banco == state.activeBancoId)) {
                        state.activeBancoId = parseInt(state.bancos[0].id_banco);
                    }
                } else {
                    state.activeBancoId = null;
                }

                renderBancoSelect();
                await carregarTabelasBackend();
            } else {
                console.error("Erro ao carregar bancos:", data.mensagem);
            }
        } catch (err) {
            console.error("Erro na requisição dos bancos:", err);
        }
    }

    async function carregarTabelasBackend(targetTabelaId = null) {
        if (!state.activeBancoId) {
            state.tabelas = [];
            state.activeTabelaId = null;
            renderTabelasSidebar();
            renderActiveTabela();
            return;
        }

        try {
            const resp = await fetch(`/phpmeuamigo/tabelas?id_banco=${state.activeBancoId}`);
            const data = await resp.json();

            if (data.sucesso) {
                state.tabelas = data.tabelas || [];
                if (state.tabelas.length > 0) {
                    if (targetTabelaId && state.tabelas.some(t => t.id_tabela == targetTabelaId)) {
                        state.activeTabelaId = parseInt(targetTabelaId);
                    } else if (!state.activeTabelaId || !state.tabelas.some(t => t.id_tabela == state.activeTabelaId)) {
                        state.activeTabelaId = parseInt(state.tabelas[0].id_tabela);
                    }
                } else {
                    state.activeTabelaId = null;
                }

                renderTabelasSidebar();
                await carregarAtributosBackend();
            } else {
                console.error("Erro ao carregar tabelas:", data.mensagem);
            }
        } catch (err) {
            console.error("Erro na requisição das tabelas:", err);
        }
    }

    async function carregarAtributosBackend() {
        if (!state.activeTabelaId) {
            state.atributos = [];
            renderActiveTabela();
            return;
        }

        try {
            const resp = await fetch(`/phpmeuamigo/atributos?id_tabela=${state.activeTabelaId}`);
            const data = await resp.json();

            if (data.sucesso) {
                state.atributos = data.atributos || [];
                renderActiveTabela();
            } else {
                console.error("Erro ao carregar atributos:", data.mensagem);
            }
        } catch (err) {
            console.error("Erro na requisição dos atributos:", err);
        }
    }

    // --- BIND DE EVENTOS DA INTERFACE ---

    function bindEvents() {
        // Seleção de Banco Ativo
        const selectBanco = document.getElementById("phpma-select-banco");
        if (selectBanco) {
            selectBanco.addEventListener("change", async function (e) {
                state.activeBancoId = parseInt(e.target.value);
                state.activeTabelaId = null;
                await carregarTabelasBackend();
            });
        }

        // Filtro de Busca de Tabelas na Sidebar
        const searchInput = document.getElementById("phpma-search-table");
        if (searchInput) {
            searchInput.addEventListener("input", function (e) {
                const term = e.target.value.toLowerCase();
                const items = document.querySelectorAll(".phpma-tabela-btn");
                items.forEach(item => {
                    const name = item.getAttribute("data-nome").toLowerCase();
                    item.style.display = name.includes(term) ? "flex" : "none";
                });
            });
        }

        // Alteração do Nome da Tabela no Editor
        const tableNameInput = document.getElementById("phpma-input-tabela-nome");
        if (tableNameInput) {
            tableNameInput.addEventListener("change", async function (e) {
                const novoNome = e.target.value.trim();
                if (!novoNome || !state.activeTabelaId) return;

                const formData = new FormData();
                formData.append('id_tabela', state.activeTabelaId);
                formData.append('id_banco', state.activeBancoId);
                formData.append('nome_tabela', novoNome);

                try {
                    const resp = await fetch('/phpmeuamigo/tabelas/salvar', { method: 'POST', body: formData });
                    const res = await resp.json();
                    if (res.sucesso) {
                        await carregarTabelasBackend(state.activeTabelaId);
                    } else {
                        alert(res.mensagem || "Erro ao salvar tabela.");
                    }
                } catch (err) {
                    alert("Erro ao salvar nome da tabela.");
                }
            });
        }

        // Botões da Barra Superior e Ações
        const btnNewBanco = document.getElementById("phpma-btn-novo-banco");
        if (btnNewBanco) btnNewBanco.addEventListener("click", openBancoModalNovo);

        const btnConfigBanco = document.getElementById("phpma-btn-config-banco");
        if (btnConfigBanco) btnConfigBanco.addEventListener("click", openConfigModal);

        const btnNewTabela = document.getElementById("phpma-btn-nova-tabela");
        if (btnNewTabela) btnNewTabela.addEventListener("click", addNovaTabela);

        const btnAddAtributo = document.getElementById("phpma-btn-add-atributo");
        if (btnAddAtributo) btnAddAtributo.addEventListener("click", addNovoAtributo);

        // Botão para Conectar e Listar Bancos da Máquina Local
        const btnConectarLocal = document.getElementById("phpma-btn-conectar-local");
        if (btnConectarLocal) {
            btnConectarLocal.addEventListener("click", async function () {
                const host = document.getElementById("modal-input-import-host").value.trim() || 'localhost';
                const porta = document.getElementById("modal-input-import-porta").value.trim() || '3306';
                const usr = document.getElementById("modal-input-import-usr").value.trim() || 'root';
                const pass = document.getElementById("modal-input-import-pass").value;

                btnConectarLocal.disabled = true;
                btnConectarLocal.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Conectando ao MySQL...`;

                const formData = new FormData();
                formData.append('host', host);
                formData.append('porta', porta);
                formData.append('usuario', usr);
                formData.append('senha', pass);

                try {
                    const resp = await fetch('/phpmeuamigo/conectar-mysql-local', { method: 'POST', body: formData });
                    const res = await resp.json();

                    if (res.sucesso && res.bancos && res.bancos.length > 0) {
                        const selectLocal = document.getElementById("modal-select-banco-local");
                        selectLocal.innerHTML = res.bancos.map(b => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`).join('');
                        
                        document.getElementById("phpma-wrapper-select-bancos-locais").style.display = 'block';
                        document.getElementById("phpma-btn-submit-import-local").disabled = false;
                    } else {
                        alert(res.mensagem || "Nenhum banco de dados disponível encontrado no MySQL local.");
                        document.getElementById("phpma-wrapper-select-bancos-locais").style.display = 'none';
                        document.getElementById("phpma-btn-submit-import-local").disabled = true;
                    }
                } catch (err) {
                    alert("Falha na conexão com a base MySQL local: " + err.message);
                } finally {
                    btnConectarLocal.disabled = false;
                    btnConectarLocal.innerHTML = `<i class="bi bi-arrow-repeat"></i> Conectar e Listar Bancos da Máquina`;
                }
            });
        }
    }

    // --- HELPERS DE RENDERIZAÇÃO DA UI ---

    function getActiveTabela() {
        return state.tabelas.find(t => t.id_tabela === state.activeTabelaId);
    }

    function renderBancoSelect() {
        const selectBanco = document.getElementById("phpma-select-banco");
        if (!selectBanco) return;

        if (state.bancos.length === 0) {
            selectBanco.innerHTML = `<option value="">Nenhum banco cadastrado</option>`;
            return;
        }

        selectBanco.innerHTML = state.bancos.map(b =>
            `<option value="${b.id_banco}" ${b.id_banco === state.activeBancoId ? 'selected' : ''}>${escapeHtml(b.nome_banco)} (${escapeHtml(b.host)}:${escapeHtml(b.porta)})</option>`
        ).join('');
    }

    function renderTabelasSidebar() {
        const container = document.getElementById("phpma-list-tabelas");
        if (!container) return;

        if (state.tabelas.length === 0) {
            container.innerHTML = `<div style="text-align:center; color:var(--muted); padding: 16px 0; font-size:12px;">Nenhuma tabela cadastrada.</div>`;
            return;
        }

        container.innerHTML = state.tabelas.map(t => {
            const isActive = t.id_tabela === state.activeTabelaId;
            return `
                <div class="phpma-tabela-btn ${isActive ? 'active' : ''}" data-id="${t.id_tabela}" data-nome="${escapeHtml(t.nome_tabela)}" onclick="window.phpmaSelectTabela(${t.id_tabela})">
                    <span class="tabela-nome"><i class="bi bi-table"></i> ${escapeHtml(t.nome_tabela)}</span>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" class="btn-icon-danger" onclick="event.stopPropagation(); window.phpmaDeleteTabela(${t.id_tabela})" title="Excluir Tabela">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderActiveTabela() {
        const activeTab = getActiveTabela();
        const inputNome = document.getElementById("phpma-input-tabela-nome");
        const tbody = document.getElementById("phpma-tbody-atributos");

        if (!activeTab) {
            if (inputNome) inputNome.value = "";
            if (tbody) tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 24px; color:var(--muted);">Selecione ou crie uma tabela para editar seus atributos.</td></tr>`;
            return;
        }

        if (inputNome) inputNome.value = activeTab.nome_tabela;
        renderAtributosGrid();
    }

    function renderAtributosGrid() {
        const tbody = document.getElementById("phpma-tbody-atributos");
        if (!tbody) return;

        if (!state.atributos || state.atributos.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 24px; color:var(--muted);">Nenhum atributo cadastrado nesta tabela. Clique em "+ Adicionar Atributo".</td></tr>`;
            return;
        }

        tbody.innerHTML = state.atributos.map((attr, idx) => {
            const typesOptions = TIPOS_DADOS.map(td =>
                `<option value="${td}" ${attr.tipo === td ? 'selected' : ''}>${td}</option>`
            ).join('');

            return `
                <tr data-attr-id="${attr.id_atributo}">
                    <td style="color:var(--muted); font-family:'DM Mono', monospace; font-size:12px; text-align:center;">${idx + 1}</td>
                    <td>
                        <input type="text" class="phpma-field-text" value="${escapeHtml(attr.nome_atributo)}" onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'nome_atributo', this.value)" placeholder="nome_campo">
                    </td>
                    <td>
                        <select class="phpma-field-select" onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'tipo', this.value)">
                            ${typesOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="phpma-field-text" style="color:var(--accent);" value="${attr.fk_atributo || ''}" placeholder="ID FK" onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'fk_atributo', this.value)">
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${parseInt(attr.PK) === 1 ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'PK', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-pk">PK</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${parseInt(attr.NN) === 1 ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'NN', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-nn">NN</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${parseInt(attr.AI) === 1 ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'AI', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-ai">AI</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${parseInt(attr.UQ) === 1 ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'UQ', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-uq">UQ</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn-icon-danger" onclick="window.phpmaDeleteAttr(${attr.id_atributo})" title="Excluir Atributo">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // --- FUNÇÕES GLOBAIS PARA INLINE EVENT BINDINGS ---

    window.phpmaSelectTabela = async function (idTabela) {
        state.activeTabelaId = idTabela;
        renderTabelasSidebar();
        await carregarAtributosBackend();
    };

    window.phpmaDeleteTabela = async function (idTabela) {
        if (!confirm("Deseja realmente remover esta tabela e seus atributos?")) return;

        const formData = new FormData();
        formData.append('id_tabela', idTabela);

        try {
            const resp = await fetch('/phpmeuamigo/tabelas/excluir', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.sucesso) {
                if (state.activeTabelaId === idTabela) state.activeTabelaId = null;
                await carregarTabelasBackend();
            } else {
                alert(res.mensagem || "Erro ao excluir tabela.");
            }
        } catch (err) {
            alert("Erro ao excluir tabela.");
        }
    };

    window.phpmaUpdateAttr = async function (idAttr, field, value) {
        const attr = state.atributos.find(a => a.id_atributo == idAttr);
        if (!attr) return;

        attr[field] = value;

        const formData = new FormData();
        formData.append('id_atributo', idAttr);
        formData.append('id_tabela', state.activeTabelaId);
        formData.append('nome_atributo', attr.nome_atributo);
        formData.append('tipo', attr.tipo);
        formData.append('fk_atributo', attr.fk_atributo || '');
        formData.append('PK', attr.PK ? 1 : 0);
        formData.append('NN', attr.NN ? 1 : 0);
        formData.append('AI', attr.AI ? 1 : 0);
        formData.append('UQ', attr.UQ ? 1 : 0);

        try {
            const resp = await fetch('/phpmeuamigo/atributos/salvar', { method: 'POST', body: formData });
            const res = await resp.json();
            if (!res.sucesso) alert(res.mensagem || "Erro ao atualizar atributo.");
        } catch (err) {
            console.error("Erro ao salvar atributo:", err);
        }
    };

    window.phpmaDeleteAttr = async function (idAttr) {
        if (!confirm("Deseja remover este atributo?")) return;

        const formData = new FormData();
        formData.append('id_atributo', idAttr);

        try {
            const resp = await fetch('/phpmeuamigo/atributos/excluir', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.sucesso) {
                await carregarAtributosBackend();
            } else {
                alert(res.mensagem || "Erro ao excluir atributo.");
            }
        } catch (err) {
            alert("Erro ao excluir atributo.");
        }
    };

    async function addNovaTabela() {
        if (!state.activeBancoId) {
            alert("Por favor, crie ou selecione um Banco de Dados antes de criar uma tabela!");
            return;
        }

        const nome = prompt("Informe o nome da nova tabela:", "nova_tabela");
        if (!nome || !nome.trim()) return;

        const formData = new FormData();
        formData.append('id_banco', state.activeBancoId);
        formData.append('nome_tabela', nome.trim());

        try {
            const resp = await fetch('/phpmeuamigo/tabelas/salvar', { method: 'POST', body: formData });
            const text = await resp.text();
            let res;
            try {
                res = JSON.parse(text);
            } catch (parseErr) {
                alert("Erro no servidor: " + text);
                return;
            }

            if (res.sucesso) {
                await carregarTabelasBackend(res.id_tabela);
            } else {
                alert(res.mensagem || "Erro ao criar tabela.");
            }
        } catch (err) {
            alert("Erro ao enviar dados da tabela: " + err.message);
        }
    }

    async function addNovoAtributo() {
        if (!state.activeTabelaId) {
            alert("Selecione uma tabela primeiro!");
            return;
        }

        const formData = new FormData();
        formData.append('id_tabela', state.activeTabelaId);
        formData.append('nome_atributo', 'novo_campo');
        formData.append('tipo', 'VARCHAR(60)');
        formData.append('PK', 0);
        formData.append('NN', 0);
        formData.append('AI', 0);
        formData.append('UQ', 0);

        try {
            const resp = await fetch('/phpmeuamigo/atributos/salvar', { method: 'POST', body: formData });
            const res = await resp.json();

            if (res.sucesso) {
                await carregarAtributosBackend();
            } else {
                alert(res.mensagem || "Erro ao criar atributo.");
            }
        } catch (err) {
            alert("Erro ao criar atributo.");
        }
    }

    // --- MODAIS ---

    function openBancoModalNovo() {
        document.getElementById("modal-input-nome-banco").value = "";
        document.getElementById("modal-input-usr-banco").value = "root";
        document.getElementById("modal-input-pass-banco").value = "";
        document.getElementById("modal-input-host-banco").value = "localhost";
        document.getElementById("modal-input-porta-banco").value = "3306";
        document.getElementById("phpma-modal-banco").dataset.idBanco = "";
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.add("open");
    }

    function openConfigModal() {
        const activeBanco = state.bancos.find(b => b.id_banco == state.activeBancoId);
        if (activeBanco) {
            document.getElementById("modal-input-nome-banco").value = activeBanco.nome_banco;
            document.getElementById("modal-input-usr-banco").value = activeBanco.usuario_banco;
            document.getElementById("modal-input-pass-banco").value = activeBanco.senha_banco || "";
            document.getElementById("modal-input-host-banco").value = activeBanco.host;
            document.getElementById("modal-input-porta-banco").value = activeBanco.porta;
            document.getElementById("phpma-modal-banco").dataset.idBanco = activeBanco.id_banco;
        }
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.add("open");
    }

    window.phpmaCloseModal = function () {
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.remove("open");
    };

    window.phpmaOpenImportModal = function () {
        const modal = document.getElementById("phpma-modal-import-sql");
        if (modal) modal.classList.add("open");
    };

    window.phpmaCloseImportModal = function () {
        const modal = document.getElementById("phpma-modal-import-sql");
        if (modal) modal.classList.remove("open");
    };

    window.phpmaSaveBancoModal = async function () {
        const idBanco = document.getElementById("phpma-modal-banco").dataset.idBanco || '';
        const nome = document.getElementById("modal-input-nome-banco").value.trim();
        const usr = document.getElementById("modal-input-usr-banco").value.trim();
        const pass = document.getElementById("modal-input-pass-banco").value;
        const host = document.getElementById("modal-input-host-banco").value.trim() || 'localhost';
        const porta = document.getElementById("modal-input-porta-banco").value.trim() || '3306';

        if (!nome) {
            alert("O nome do banco de dados é obrigatório!");
            return;
        }

        const formData = new FormData();
        if (idBanco) formData.append('id_banco', idBanco);
        formData.append('nome_banco', nome);
        formData.append('usuario_banco', usr);
        formData.append('senha_banco', pass);
        formData.append('host', host);
        formData.append('porta', porta);

        try {
            const resp = await fetch('/phpmeuamigo/bancos/salvar', { method: 'POST', body: formData });
            const res = await resp.json();

            if (res.sucesso) {
                phpmaCloseModal();
                await carregarBancosBackend(res.id_banco);
            } else {
                alert(res.mensagem || "Erro ao salvar banco de dados.");
            }
        } catch (err) {
            alert("Erro ao salvar banco de dados.");
        }
    };

    window.phpmaExecutarImportacaoLocal = async function () {
        const selectLocal = document.getElementById("modal-select-banco-local");
        const bancoSelecionado = selectLocal ? selectLocal.value : '';

        if (!bancoSelecionado) {
            alert("Selecione um banco de dados na lista para importar!");
            return;
        }

        const host = document.getElementById("modal-input-import-host").value.trim() || 'localhost';
        const porta = document.getElementById("modal-input-import-porta").value.trim() || '3306';
        const usr = document.getElementById("modal-input-import-usr").value.trim() || 'root';
        const pass = document.getElementById("modal-input-import-pass").value;

        const btnSubmit = document.getElementById("phpma-btn-submit-import-local");
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Importando estrutura...`;
        }

        const formData = new FormData();
        formData.append('host', host);
        formData.append('porta', porta);
        formData.append('usuario', usr);
        formData.append('senha', pass);
        formData.append('banco_selecionado', bancoSelecionado);

        try {
            const resp = await fetch('/phpmeuamigo/importar-banco-local', { method: 'POST', body: formData });
            const res = await resp.json();

            if (res.sucesso) {
                alert(res.mensagem);
                window.phpmaCloseImportModal();
                await carregarBancosBackend(res.id_banco);
            } else {
                alert("Erro ao importar banco: " + res.mensagem);
            }
        } catch (err) {
            alert("Erro de conexão durante a importação.");
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i class="bi bi-download"></i> Importar Banco Selecionado`;
            }
        }
    };

    window.phpmaDeleteBancoActive = async function () {
        if (!state.activeBancoId) {
            alert("Selecione um banco de dados primeiro!");
            return;
        }

        const activeBanco = state.bancos.find(b => b.id_banco == state.activeBancoId);
        const nomeBanco = activeBanco ? activeBanco.nome_banco : 'este banco';

        if (!confirm(`Tem certeza que deseja excluir o banco "${nomeBanco}" do DevStudio?\n\nTodas as tabelas e atributos associados a ele neste projeto serão excluídos.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('id_banco', state.activeBancoId);

        try {
            const resp = await fetch('/phpmeuamigo/bancos/excluir', { method: 'POST', body: formData });
            const res = await resp.json();

            if (res.sucesso) {
                alert(res.mensagem);
                state.activeBancoId = null;
                await carregarBancosBackend();
            } else {
                alert("Erro ao excluir banco: " + res.mensagem);
            }
        } catch (err) {
            alert("Erro de conexão ao tentar excluir banco de dados.");
        }
    };

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

})();
