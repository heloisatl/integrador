/**
 * PHPMeuAmigo - Script de interatividade da interface do modelador de banco
 * Camada AJAX que conecta os elementos visuais aos endpoints da API do DevStudio
 */

const API_BASE = (function () {
    if (typeof window.URL_BASE === 'string' && window.URL_BASE) {
        return window.URL_BASE.replace(/\/$/, "");
    }
    return "";
})();

function apiEndpoint(path) {
    const cleanPath = path.startsWith('/') ? path : '/' + path;
    return API_BASE ? `${API_BASE}${cleanPath}` : cleanPath;
}

let state = {
    bancos: [],
    activeBancoId: null,
    tabelas: [],
    activeTabelaId: null,
    atributos: [],
    atributosBanco: []
};

const TIPOS_DADOS = [
    'int', 'varchar(60)', 'varchar(255)', 'text',
    'mediumtext', 'datetime', 'tinyint', 'enum',
    'decimal(10,2)', 'bigint'
];

// --- SISTEMA DE ALERTA INLINE VISUAL (DevStudio) ---

let alertTimer = null;

function phpmaShowAlert(message, type = 'error', title = null) {
    // Verifica se algum modal está aberto para exibir o alerta dentro do modal ativo
    const modalBanco = document.getElementById("phpma-modal-banco");
    const modalImport = document.getElementById("phpma-modal-import-sql");

    let alertEl = document.getElementById("phpma-inline-alert");
    if (modalBanco && modalBanco.classList.contains("open")) {
        alertEl = document.getElementById("phpma-modal-banco-alert") || alertEl;
    } else if (modalImport && modalImport.classList.contains("open")) {
        alertEl = document.getElementById("phpma-modal-import-alert") || alertEl;
    }

    if (!alertEl) return;

    alertEl.className = 'phpma-inline-alert';

    alertEl.innerHTML = `
        <div class="phpma-inline-alert-body">
            <div class="phpma-inline-alert-msg">${escapeHtml(message)}</div>
        </div>
    `;

    alertEl.style.display = 'flex';
    alertEl.onclick = () => window.phpmaDismissAlert(alertEl);

    if (alertTimer) clearTimeout(alertTimer);

    // Auto-dispensa após 6.5 segundos
    alertTimer = setTimeout(() => {
        window.phpmaDismissAlert(alertEl);
    }, 6500);

    // Rola suavemente até o alerta caso esteja fora da visão
    if ((!modalBanco || !modalBanco.classList.contains("open")) && 
        (!modalImport || !modalImport.classList.contains("open"))) {
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

window.phpmaDismissAlert = function (target = null) {
    if (alertTimer) clearTimeout(alertTimer);

    if (target && target instanceof HTMLElement) {
        const box = target.closest('.phpma-inline-alert');
        if (box) {
            box.style.display = 'none';
            return;
        }
    }

    document.querySelectorAll('.phpma-inline-alert').forEach(el => {
        el.style.display = 'none';
    });
};

window.phpmaAlert = phpmaShowAlert;
window.phpmaToast = phpmaShowAlert; // Roteamento transparente de compatibilidade

document.addEventListener("DOMContentLoaded", function () {
    bindEvents();
    carregarBancosBackend();
});

// --- CARREGAMENTO DE DADOS ---

async function carregarBancosBackend(targetBancoId = null) {
    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/bancos'));
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
        console.error("Erro na requisicao dos bancos:", err);
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
        const resp = await fetch(apiEndpoint(`/phpmeuamigo/tabelas?id_banco=${state.activeBancoId}`));
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
            await carregarCatalogoFkBackend();
            await carregarAtributosBackend();
        } else {
            console.error("Erro ao carregar tabelas:", data.mensagem);
        }
    } catch (err) {
        console.error("Erro na requisicao das tabelas:", err);
    }
}

async function carregarCatalogoFkBackend() {
    if (!state.activeBancoId) {
        state.atributosBanco = [];
        return;
    }

    try {
        const resp = await fetch(apiEndpoint(`/phpmeuamigo/atributos?id_banco=${state.activeBancoId}`));
        const data = await resp.json();
        if (data.sucesso) {
            state.atributosBanco = data.atributos_banco || [];
        } else {
            state.atributosBanco = [];
        }
    } catch (err) {
        console.error("Erro ao carregar catalogo de FKs:", err);
        state.atributosBanco = [];
    }
}

async function carregarAtributosBackend() {
    if (!state.activeTabelaId) {
        state.atributos = [];
        renderActiveTabela();
        return;
    }

    try {
        const resp = await fetch(apiEndpoint(`/phpmeuamigo/atributos?id_tabela=${state.activeTabelaId}`));
        const data = await resp.json();

        if (data.sucesso) {
            state.atributos = data.atributos || [];
            renderActiveTabela();
        } else {
            console.error("Erro ao carregar atributos:", data.mensagem);
        }
    } catch (err) {
        console.error("Erro na requisicao dos atributos:", err);
    }
}

// --- BIND DE EVENTOS ---

function bindEvents() {
    const selectBanco = document.getElementById("phpma-select-banco");
    if (selectBanco) {
        selectBanco.addEventListener("change", async function (e) {
            state.activeBancoId = parseInt(e.target.value);
            state.activeTabelaId = null;
            await carregarTabelasBackend();
        });
    }

    const searchInput = document.getElementById("phpma-search-table");
    if (searchInput) {
        searchInput.addEventListener("input", function (e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll(".phpma-tabela-btn").forEach(item => {
                const name = item.getAttribute("data-nome").toLowerCase();
                item.style.display = name.includes(term) ? "flex" : "none";
            });
        });
    }

    const tableNameInput = document.getElementById("phpma-input-tabela-nome");
    if (tableNameInput) {
        tableNameInput.addEventListener("change", async function (e) {
            const novoNome = e.target.value.trim();
            const activeTab = getActiveTabela();

            if (!novoNome) {
                phpmaToast("O nome da tabela é obrigatório e não pode ficar vazio.", "warning");
                if (activeTab) e.target.value = activeTab.nome_tabela;
                return;
            }

            if (!state.activeTabelaId) return;

            const formData = new FormData();
            formData.append('id_tabela', state.activeTabelaId);
            formData.append('id_banco', state.activeBancoId);
            formData.append('nome_tabela', novoNome);

            try {
                const resp = await fetch(apiEndpoint('/phpmeuamigo/tabelas/salvar'), { method: 'POST', body: formData });
                const res = await resp.json();
                if (res.sucesso) {
                    await carregarTabelasBackend(state.activeTabelaId);
                } else {
                    phpmaToast(res.mensagem || "Erro ao salvar tabela.", "error");
                    if (activeTab) e.target.value = activeTab.nome_tabela;
                }
            } catch (err) {
                phpmaToast("Erro ao salvar nome da tabela.", "error");
                if (activeTab) e.target.value = activeTab.nome_tabela;
            }
        });
    }

    const btnNovoBanco = document.getElementById("phpma-btn-novo-banco");
    const btnConfigBanco = document.getElementById("phpma-btn-config-banco");
    const btnNovaTabela  = document.getElementById("phpma-btn-nova-tabela");
    const btnAddAtributo = document.getElementById("phpma-btn-add-atributo");
    const wrapperNovaTab = document.getElementById("phpma-wrapper-nova-tabela");
    const inputNovaTab   = document.getElementById("phpma-input-nova-tabela");
    const btnConfirmTab  = document.getElementById("phpma-btn-confirm-nova-tabela");
    const btnCancelTab   = document.getElementById("phpma-btn-cancel-nova-tabela");

    if (btnNovoBanco) btnNovoBanco.addEventListener("click", abrirModalNovoBanco);
    if (btnConfigBanco) btnConfigBanco.addEventListener("click", abrirModalConfigBanco);

    // Controle do formulário inline padronizado de criação de tabelas (sem prompt nativo)
    if (btnNovaTabela && wrapperNovaTab) {
        btnNovaTabela.addEventListener("click", function () {
            if (!state.activeBancoId) {
                phpmaToast("Crie ou selecione um Banco de Dados antes de criar uma tabela!", "warning");
                return;
            }
            const estaAberto = wrapperNovaTab.style.display !== 'none';
            wrapperNovaTab.style.display = estaAberto ? 'none' : 'block';
            if (!estaAberto && inputNovaTab) {
                inputNovaTab.value = '';
                inputNovaTab.focus();
            }
        });
    }

    if (btnCancelTab && wrapperNovaTab) {
        btnCancelTab.addEventListener("click", function () {
            wrapperNovaTab.style.display = 'none';
            if (inputNovaTab) inputNovaTab.value = '';
        });
    }

    if (btnConfirmTab) {
        btnConfirmTab.addEventListener("click", addNovaTabela);
    }

    if (inputNovaTab) {
        inputNovaTab.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                addNovaTabela();
            } else if (e.key === "Escape") {
                if (wrapperNovaTab) wrapperNovaTab.style.display = 'none';
            }
        });
    }

    if (btnAddAtributo) btnAddAtributo.addEventListener("click", addNovoAtributo);

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
                const resp = await fetch(apiEndpoint('/phpmeuamigo/conectar-mysql-local'), { method: 'POST', body: formData });
                const res = await resp.json();

                if (res.sucesso && res.bancos && res.bancos.length > 0) {
                    const selectLocal = document.getElementById("modal-select-banco-local");
                    selectLocal.innerHTML = res.bancos.map(b => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`).join('');
                    document.getElementById("phpma-wrapper-select-bancos-locais").style.display = 'block';
                    document.getElementById("phpma-btn-submit-import-local").disabled = false;
                } else {
                    phpmaToast(res.mensagem || "Nenhum banco disponivel encontrado no MySQL local.", "warning");
                    document.getElementById("phpma-wrapper-select-bancos-locais").style.display = 'none';
                    document.getElementById("phpma-btn-submit-import-local").disabled = true;
                }
            } catch (err) {
                phpmaToast("Falha na conexao com o MySQL local: " + err.message, "error");
            } finally {
                btnConectarLocal.disabled = false;
                btnConectarLocal.innerHTML = `<i class="bi bi-arrow-repeat"></i> Conectar e Listar Bancos da Maquina`;
            }
        });
    }
}

// --- RENDERIZACAO ---

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
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 24px; color:var(--muted);">Nenhum atributo cadastrado. Clique em "+ Adicionar Atributo".</td></tr>`;
        return;
    }

    tbody.innerHTML = state.atributos.map((attr, idx) => {
        const currentTipo = (attr.tipo || '').toLowerCase();
        const typesOptions = TIPOS_DADOS.map(td =>
            `<option value="${td}" ${currentTipo === td.toLowerCase() ? 'selected' : ''}>${td}</option>`
        ).join('');

        // Monta o <select> didático com os atributos exclusivamente do banco ativo
        const fkOptions = [
            `<option value="">-- Nenhuma (NULL) --</option>`
        ];

        (state.atributosBanco || []).forEach(item => {
            // Não permite que o atributo aponte para si mesmo
            if (item.id_atributo === attr.id_atributo) return;

            const isSelected = parseInt(attr.fk_atributo) === item.id_atributo;
            const pkTag = item.PK === 1 ? ' [PK]' : '';
            fkOptions.push(
                `<option value="${item.id_atributo}" ${isSelected ? 'selected' : ''}>${escapeHtml(item.nome_tabela)} &rarr; ${escapeHtml(item.nome_atributo)}${pkTag}</option>`
            );
        });

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
                    <select class="phpma-field-select" style="color:var(--accent); font-size:12px; max-width: 180px;" onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'fk_atributo', this.value)">
                        ${fkOptions.join('')}
                    </select>
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

// --- ACOES DE TABELA E ATRIBUTO ---

window.phpmaSelectTabela = async function (idTabela) {
    window.phpmaDismissAlert();
    state.activeTabelaId = idTabela;
    renderTabelasSidebar();
    await carregarAtributosBackend();
};

window.phpmaDeleteTabela = async function (idTabela) {
    if (!confirm("Deseja realmente remover esta tabela e seus atributos?")) return;

    const formData = new FormData();
    formData.append('id_tabela', idTabela);

    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/tabelas/excluir'), { method: 'POST', body: formData });
        const res = await resp.json();
        if (res.sucesso) {
            if (state.activeTabelaId === idTabela) state.activeTabelaId = null;
            await carregarTabelasBackend();
        } else {
            phpmaToast(res.mensagem || "Erro ao excluir tabela.", "error");
        }
    } catch (err) {
        phpmaToast("Erro ao excluir tabela.", "error");
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
        const resp = await fetch(apiEndpoint('/phpmeuamigo/atributos/salvar'), { method: 'POST', body: formData });
        const res = await resp.json();

        console.log("[Backend -> Frontend] Resposta salvar atributo:", res);
        if (!res.sucesso) phpmaToast(res.mensagem || "Erro ao atualizar atributo.", "error");
    } catch (err) {
        console.error("Erro ao salvar atributo:", err);
    }
};

window.phpmaDeleteAttr = async function (idAttr) {
    if (!confirm("Deseja remover este atributo?")) return;

    const formData = new FormData();
    formData.append('id_atributo', idAttr);

    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/atributos/excluir'), { method: 'POST', body: formData });
        const res = await resp.json();
        if (res.sucesso) {
            await carregarCatalogoFkBackend();
            await carregarAtributosBackend();
        } else {
            phpmaToast(res.mensagem || "Erro ao excluir atributo.", "error");
        }
    } catch (err) {
        phpmaToast("Erro ao excluir atributo.", "error");
    }
};

async function addNovaTabela() {
    if (!state.activeBancoId) {
        phpmaToast("Crie ou selecione um Banco de Dados antes de criar uma tabela!", "warning");
        return;
    }

    const inputNovaTab   = document.getElementById("phpma-input-nova-tabela");
    const wrapperNovaTab = document.getElementById("phpma-wrapper-nova-tabela");
    const nome           = inputNovaTab ? inputNovaTab.value.trim() : '';

    if (!nome) {
        phpmaToast("Informe o nome da nova tabela!", "warning");
        if (inputNovaTab) inputNovaTab.focus();
        return;
    }

    const formData = new FormData();
    formData.append('id_banco', state.activeBancoId);
    formData.append('nome_tabela', nome);

    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/tabelas/salvar'), { method: 'POST', body: formData });
        const text = await resp.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (parseErr) {
            phpmaToast("Erro no servidor: " + text, "error");
            return;
        }

        if (res.sucesso) {
            if (inputNovaTab) inputNovaTab.value = '';
            if (wrapperNovaTab) wrapperNovaTab.style.display = 'none';
            await carregarTabelasBackend(res.id_tabela);
        } else {
            phpmaToast(res.mensagem || "Erro ao criar tabela.", "error");
        }
    } catch (err) {
        phpmaToast("Erro ao criar tabela: " + err.message, "error");
    }
}

async function addNovoAtributo() {
    if (!state.activeTabelaId) {
        phpmaToast("Selecione uma tabela primeiro!", "warning");
        return;
    }

    // Identifica todos os nomes já usados para sugerir o próximo sequencialmente: novo_atributo01, novo_atributo02, etc.
    const nomesExistentes = new Set([
        ...(state.atributos || []).map(a => (a.nome_atributo || '').toLowerCase()),
        ...(state.atributosBanco || []).map(a => (a.nome_atributo || '').toLowerCase())
    ]);

    let num = 1;
    let nomeSugerido = `novo_atributo${String(num).padStart(2, '0')}`;
    while (nomesExistentes.has(nomeSugerido.toLowerCase())) {
        num++;
        nomeSugerido = `novo_atributo${String(num).padStart(2, '0')}`;
    }

    const formData = new FormData();
    formData.append('id_tabela', state.activeTabelaId);
    formData.append('nome_atributo', nomeSugerido);
    formData.append('tipo', 'varchar(60)');
    formData.append('PK', 0);
    formData.append('NN', 0);
    formData.append('AI', 0);
    formData.append('UQ', 0);

    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/atributos/salvar'), { method: 'POST', body: formData });
        const res = await resp.json();

        if (res.sucesso) {
            await carregarCatalogoFkBackend();
            await carregarAtributosBackend();
        } else {
            phpmaToast(res.mensagem || "Erro ao criar atributo.", "error");
        }
    } catch (err) {
        phpmaToast("Erro ao criar atributo.", "error");
    }
}

// --- MODAIS ---

function abrirModalNovoBanco() {
    window.phpmaDismissAlert();
    document.getElementById("modal-input-nome-banco").value = "";
    document.getElementById("modal-input-usr-banco").value = "root";
    document.getElementById("modal-input-pass-banco").value = "";
    document.getElementById("modal-input-host-banco").value = "localhost";
    document.getElementById("modal-input-porta-banco").value = "3306";
    document.getElementById("phpma-modal-banco").dataset.idBanco = "";
    document.getElementById("phpma-modal-banco").classList.add("open");
}

function abrirModalConfigBanco() {
    window.phpmaDismissAlert();
    const activeBanco = state.bancos.find(b => b.id_banco == state.activeBancoId);
    if (activeBanco) {
        document.getElementById("modal-input-nome-banco").value = activeBanco.nome_banco;
        document.getElementById("modal-input-usr-banco").value = activeBanco.usuario_banco;
        document.getElementById("modal-input-pass-banco").value = activeBanco.senha_banco || "";
        document.getElementById("modal-input-host-banco").value = activeBanco.host;
        document.getElementById("modal-input-porta-banco").value = activeBanco.porta;
        document.getElementById("phpma-modal-banco").dataset.idBanco = activeBanco.id_banco;
    }
    document.getElementById("phpma-modal-banco").classList.add("open");
}

window.phpmaCloseModal = function () {
    window.phpmaDismissAlert();
    document.getElementById("phpma-modal-banco").classList.remove("open");
};

window.phpmaOpenImportModal = function () {
    window.phpmaDismissAlert();
    document.getElementById("phpma-modal-import-sql").classList.add("open");
};

window.phpmaCloseImportModal = function () {
    window.phpmaDismissAlert();
    document.getElementById("phpma-modal-import-sql").classList.remove("open");
};

window.phpmaSaveBancoModal = async function () {
    const idBanco = document.getElementById("phpma-modal-banco").dataset.idBanco || '';
    const nome = document.getElementById("modal-input-nome-banco").value.trim();
    const usr = document.getElementById("modal-input-usr-banco").value.trim();
    const pass = document.getElementById("modal-input-pass-banco").value;
    const host = document.getElementById("modal-input-host-banco").value.trim() || 'localhost';
    const porta = document.getElementById("modal-input-porta-banco").value.trim() || '3306';

    if (!nome) {
        phpmaToast("O nome do banco é obrigatório!", "warning");
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
        const resp = await fetch(apiEndpoint('/phpmeuamigo/bancos/salvar'), { method: 'POST', body: formData });
        const res = await resp.json();

        if (res.sucesso) {
            phpmaCloseModal();
            phpmaToast(res.mensagem || "Banco salvo com sucesso!", "success");
            await carregarBancosBackend(res.id_banco);
        } else {
            phpmaToast(res.mensagem || "Erro ao salvar banco de dados.", "error");
        }
    } catch (err) {
        phpmaToast("Erro ao salvar banco de dados.", "error");
    }
};

window.phpmaExecutarImportacaoLocal = async function () {
    const selectLocal = document.getElementById("modal-select-banco-local");
    const bancoSelecionado = selectLocal ? selectLocal.value : '';

    if (!bancoSelecionado) {
        phpmaToast("Selecione um banco de dados na lista para importar!", "warning");
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
        const resp = await fetch(apiEndpoint('/phpmeuamigo/importar-banco-local'), { method: 'POST', body: formData });
        const res = await resp.json();
        console.log("[Backend -> Frontend] Resposta importar-banco-local:", res);

        if (res.sucesso) {
            phpmaToast(res.mensagem, "success");
            window.phpmaCloseImportModal();
            await carregarBancosBackend(res.id_banco);
        } else {
            phpmaToast("Erro ao importar banco: " + res.mensagem, "error");
        }
    } catch (err) {
        phpmaToast("Erro de conexao durante a importacao.", "error");
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="bi bi-download"></i> Importar Banco Selecionado`;
        }
    }
};

window.phpmaDeleteBancoActive = function () {
    if (!state.activeBancoId) {
        phpmaToast("Selecione um banco de dados primeiro!", "warning");
        return;
    }

    const activeBanco = state.bancos.find(b => b.id_banco == state.activeBancoId);
    const nomeBanco = activeBanco ? activeBanco.nome_banco : 'este banco';

    const modalConfirm = document.getElementById("phpma-modal-confirm-delete");
    const labelNome = document.getElementById("phpma-delete-banco-nome");
    if (labelNome) {
        labelNome.textContent = `"${nomeBanco}"`;
    }
    if (modalConfirm) {
        window.phpmaDismissAlert();
        modalConfirm.classList.add("open");
    }
};

window.phpmaCloseDeleteModal = function () {
    const modalConfirm = document.getElementById("phpma-modal-confirm-delete");
    if (modalConfirm) {
        modalConfirm.classList.remove("open");
    }
};

window.phpmaConfirmarExclusaoBanco = async function () {
    if (!state.activeBancoId) return;

    window.phpmaCloseDeleteModal();

    const formData = new FormData();
    formData.append('id_banco', state.activeBancoId);

    try {
        const resp = await fetch(apiEndpoint('/phpmeuamigo/bancos/excluir'), { method: 'POST', body: formData });
        const res = await resp.json();

        if (res.sucesso) {
            phpmaToast(res.mensagem, "success");
            state.activeBancoId = null;
            await carregarBancosBackend();
        } else {
            phpmaToast("Erro ao excluir banco: " + res.mensagem, "error");
        }
    } catch (err) {
        phpmaToast("Erro de conexao ao tentar excluir banco de dados.", "error");
    }
};

// --- UTIL ---

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
