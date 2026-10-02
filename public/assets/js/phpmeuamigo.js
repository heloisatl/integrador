/**
 * PHPmeuamigo - Frontend UI Interactive Script (DevStudio Design System)
 * Pure client-side state management for Banco, Tabela, and Atributo
 */

(function () {
    'use strict';

    // Mock initial state based on schema script.sql
    let state = {
        bancos: [
            {
                id_banco: 1,
                nome_banco: 'mvc_creator',
                usuario_banco: 'root',
                senha_banco: '',
                host: 'localhost',
                porta: '3306'
            }
        ],
        activeBancoId: 1,
        tabelas: [
            {
                id_tabela: 1,
                fk_banco: 1,
                nome_tabela: 'usuario',
                atributos: [
                    { id_atributo: 1, fk_atributo: null, nome_atributo: 'id_usuario', tipo: 'INT', PK: 1, NN: 1, AI: 1, UQ: 0 },
                    { id_atributo: 2, fk_atributo: null, nome_atributo: 'nome', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 3, fk_atributo: null, nome_atributo: 'email', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 4, fk_atributo: null, nome_atributo: 'senha_usuario', tipo: 'VARCHAR(255)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 5, fk_atributo: null, nome_atributo: 'tipo_perfil', tipo: 'ENUM', PK: 0, NN: 1, AI: 0, UQ: 0 }
                ]
            },
            {
                id_tabela: 2,
                fk_banco: 1,
                nome_tabela: 'banco',
                atributos: [
                    { id_atributo: 6, fk_atributo: 1, nome_atributo: 'id_banco', tipo: 'INT', PK: 1, NN: 1, AI: 1, UQ: 0 },
                    { id_atributo: 7, fk_atributo: 1, nome_atributo: 'fk_usuario', tipo: 'INT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 8, fk_atributo: null, nome_atributo: 'nome_banco', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 9, fk_atributo: null, nome_atributo: 'usuario_banco', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 10, fk_atributo: null, nome_atributo: 'senha_banco', tipo: 'VARCHAR(255)', PK: 0, NN: 0, AI: 0, UQ: 0 },
                    { id_atributo: 11, fk_atributo: null, nome_atributo: 'host', tipo: 'VARCHAR(20)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 12, fk_atributo: null, nome_atributo: 'porta', tipo: 'VARCHAR(10)', PK: 0, NN: 1, AI: 0, UQ: 0 }
                ]
            },
            {
                id_tabela: 3,
                fk_banco: 1,
                nome_tabela: 'tabela',
                atributos: [
                    { id_atributo: 13, fk_atributo: null, nome_atributo: 'id_tabela', tipo: 'INT', PK: 1, NN: 1, AI: 1, UQ: 0 },
                    { id_atributo: 14, fk_atributo: 6, nome_atributo: 'fk_banco', tipo: 'INT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 15, fk_atributo: null, nome_atributo: 'nome_tabela', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 }
                ]
            },
            {
                id_tabela: 4,
                fk_banco: 1,
                nome_tabela: 'atributo',
                atributos: [
                    { id_atributo: 16, fk_atributo: null, nome_atributo: 'id_atributo', tipo: 'INT', PK: 1, NN: 1, AI: 1, UQ: 0 },
                    { id_atributo: 17, fk_atributo: 13, nome_atributo: 'fk_tabela', tipo: 'INT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 18, fk_atributo: 16, nome_atributo: 'fk_atributo', tipo: 'INT', PK: 0, NN: 0, AI: 0, UQ: 0 },
                    { id_atributo: 19, fk_atributo: null, nome_atributo: 'nome_atributo', tipo: 'VARCHAR(60)', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 20, fk_atributo: null, nome_atributo: 'tipo', tipo: 'TINYTEXT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 21, fk_atributo: null, nome_atributo: 'PK', tipo: 'TINYINT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 22, fk_atributo: null, nome_atributo: 'NN', tipo: 'TINYINT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 23, fk_atributo: null, nome_atributo: 'AI', tipo: 'TINYINT', PK: 0, NN: 1, AI: 0, UQ: 0 },
                    { id_atributo: 24, fk_atributo: null, nome_atributo: 'UQ', tipo: 'TINYINT', PK: 0, NN: 1, AI: 0, UQ: 0 }
                ]
            }
        ],
        activeTabelaId: 1
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

    function initUI() {
        renderBancoSelect();
        renderTabelasSidebar();
        renderActiveTabela();
        bindEvents();
    }

    function bindEvents() {
        // Banco selection
        const selectBanco = document.getElementById("phpma-select-banco");
        if (selectBanco) {
            selectBanco.addEventListener("change", function (e) {
                state.activeBancoId = parseInt(e.target.value);
                const firstTab = state.tabelas.find(t => t.fk_banco === state.activeBancoId);
                state.activeTabelaId = firstTab ? firstTab.id_tabela : null;
                renderTabelasSidebar();
                renderActiveTabela();
            });
        }

        // Table search input
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

        // Table name change
        const tableNameInput = document.getElementById("phpma-input-tabela-nome");
        if (tableNameInput) {
            tableNameInput.addEventListener("input", function (e) {
                const activeTab = getActiveTabela();
                if (activeTab) {
                    activeTab.nome_tabela = e.target.value;
                    renderTabelasSidebar();
                }
            });
        }

        // Buttons
        const btnNewBanco = document.getElementById("phpma-btn-novo-banco");
        if (btnNewBanco) btnNewBanco.addEventListener("click", openBancoModal);

        const btnConfigBanco = document.getElementById("phpma-btn-config-banco");
        if (btnConfigBanco) btnConfigBanco.addEventListener("click", openConfigModal);

        const btnNewTabela = document.getElementById("phpma-btn-nova-tabela");
        if (btnNewTabela) btnNewTabela.addEventListener("click", addNovaTabela);

        const btnAddAtributo = document.getElementById("phpma-btn-add-atributo");
        if (btnAddAtributo) btnAddAtributo.addEventListener("click", addNovoAtributo);
    }

    function getActiveTabela() {
        return state.tabelas.find(t => t.id_tabela === state.activeTabelaId);
    }

    function renderBancoSelect() {
        const selectBanco = document.getElementById("phpma-select-banco");
        if (!selectBanco) return;

        selectBanco.innerHTML = state.bancos.map(b =>
            `<option value="${b.id_banco}" ${b.id_banco === state.activeBancoId ? 'selected' : ''}>${escapeHtml(b.nome_banco)} (${escapeHtml(b.host)}:${escapeHtml(b.porta)})</option>`
        ).join('');
    }

    function renderTabelasSidebar() {
        const container = document.getElementById("phpma-list-tabelas");
        if (!container) return;

        const currentTabelas = state.tabelas.filter(t => t.fk_banco === state.activeBancoId);

        if (currentTabelas.length === 0) {
            container.innerHTML = `<div style="text-align:center; color:var(--muted); padding: 16px 0; font-size:12px;">Nenhuma tabela cadastrada.</div>`;
            return;
        }

        container.innerHTML = currentTabelas.map(t => {
            const isActive = t.id_tabela === state.activeTabelaId;
            return `
                <div class="phpma-tabela-btn ${isActive ? 'active' : ''}" data-id="${t.id_tabela}" data-nome="${escapeHtml(t.nome_tabela)}" onclick="window.phpmaSelectTabela(${t.id_tabela})">
                    <span class="tabela-nome"><i class="bi bi-table"></i> ${escapeHtml(t.nome_tabela)}</span>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="tabela-badge">${t.atributos.length}</span>
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

        renderAtributosGrid(activeTab);
    }

    function renderAtributosGrid(tabela) {
        const tbody = document.getElementById("phpma-tbody-atributos");
        if (!tbody) return;

        if (tabela.atributos.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 24px; color:var(--muted);">Nenhum atributo cadastrado nesta tabela. Clique em "+ Adicionar Atributo".</td></tr>`;
            return;
        }

        const allAtributos = getAllAtributosList();

        tbody.innerHTML = tabela.atributos.map((attr, idx) => {
            const typesOptions = TIPOS_DADOS.map(td =>
                `<option value="${td}" ${attr.tipo === td ? 'selected' : ''}>${td}</option>`
            ).join('');

            const fkOptions = `<option value="">Nenhum (Sem FK)</option>` + allAtributos.map(a =>
                `<option value="${a.id_atributo}" ${attr.fk_atributo === a.id_atributo ? 'selected' : ''}>${escapeHtml(a.label)}</option>`
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
                        <select class="phpma-field-select" style="color:var(--accent);" onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'fk_atributo', this.value ? parseInt(this.value) : null)">
                            ${fkOptions}
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${attr.PK ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'PK', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-pk">PK</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${attr.NN ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'NN', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-nn">NN</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${attr.AI ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'AI', this.checked ? 1 : 0)">
                            <span class="phpma-flag-badge badge-ai">AI</span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <label class="phpma-flag-toggle">
                            <input type="checkbox" ${attr.UQ ? 'checked' : ''} onchange="window.phpmaUpdateAttr(${attr.id_atributo}, 'UQ', this.checked ? 1 : 0)">
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

    function getAllAtributosList() {
        const list = [];
        state.tabelas.forEach(t => {
            t.atributos.forEach(a => {
                list.push({
                    id_atributo: a.id_atributo,
                    label: `${t.nome_tabela}.${a.nome_atributo}`
                });
            });
        });
        return list;
    }

    // Global Functions for inline event bindings
    window.phpmaSelectTabela = function (idTabela) {
        state.activeTabelaId = idTabela;
        renderTabelasSidebar();
        renderActiveTabela();
    };

    window.phpmaDeleteTabela = function (idTabela) {
        if (!confirm("Deseja realmente remover esta tabela do modelo visual?")) return;
        state.tabelas = state.tabelas.filter(t => t.id_tabela !== idTabela);
        if (state.activeTabelaId === idTabela) {
            const remaining = state.tabelas.filter(t => t.fk_banco === state.activeBancoId);
            state.activeTabelaId = remaining.length > 0 ? remaining[0].id_tabela : null;
        }
        renderTabelasSidebar();
        renderActiveTabela();
    };

    window.phpmaUpdateAttr = function (idAttr, field, value) {
        const activeTab = getActiveTabela();
        if (!activeTab) return;
        const attr = activeTab.atributos.find(a => a.id_atributo === idAttr);
        if (attr) {
            attr[field] = value;
            if (field === 'nome_atributo') {
                renderAtributosGrid(activeTab);
            }
        }
    };

    window.phpmaDeleteAttr = function (idAttr) {
        const activeTab = getActiveTabela();
        if (!activeTab) return;
        activeTab.atributos = activeTab.atributos.filter(a => a.id_atributo !== idAttr);
        renderAtributosGrid(activeTab);
        renderTabelasSidebar();
    };

    function addNovaTabela() {
        const nome = prompt("Informe o nome da nova tabela:", "nova_tabela");
        if (!nome || !nome.trim()) return;

        const newId = Date.now();
        const newTab = {
            id_tabela: newId,
            fk_banco: state.activeBancoId,
            nome_tabela: nome.trim(),
            atributos: [
                { id_atributo: Date.now() + 1, fk_atributo: null, nome_atributo: 'id_' + nome.trim(), tipo: 'INT', PK: 1, NN: 1, AI: 1, UQ: 0 }
            ]
        };

        state.tabelas.push(newTab);
        state.activeTabelaId = newId;
        renderTabelasSidebar();
        renderActiveTabela();
    }

    function addNovoAtributo() {
        const activeTab = getActiveTabela();
        if (!activeTab) {
            alert("Selecione uma tabela primeiro!");
            return;
        }

        const newId = Date.now();
        activeTab.atributos.push({
            id_atributo: newId,
            fk_atributo: null,
            nome_atributo: 'novo_campo',
            tipo: 'VARCHAR(60)',
            PK: 0,
            NN: 0,
            AI: 0,
            UQ: 0
        });

        renderAtributosGrid(activeTab);
        renderTabelasSidebar();
    }

    // Modal Helpers
    function openBancoModal() {
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.add("open");
    }

    function openConfigModal() {
        const activeBanco = state.bancos.find(b => b.id_banco === state.activeBancoId);
        if (activeBanco) {
            document.getElementById("modal-input-nome-banco").value = activeBanco.nome_banco;
            document.getElementById("modal-input-usr-banco").value = activeBanco.usuario_banco;
            document.getElementById("modal-input-pass-banco").value = activeBanco.senha_banco;
            document.getElementById("modal-input-host-banco").value = activeBanco.host;
            document.getElementById("modal-input-porta-banco").value = activeBanco.porta;
        }
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.add("open");
    }

    window.phpmaCloseModal = function () {
        const backdrop = document.getElementById("phpma-modal-banco");
        if (backdrop) backdrop.classList.remove("open");
    };

    window.phpmaSaveBancoModal = function () {
        const nome = document.getElementById("modal-input-nome-banco").value.trim();
        const usr = document.getElementById("modal-input-usr-banco").value.trim();
        const pass = document.getElementById("modal-input-pass-banco").value;
        const host = document.getElementById("modal-input-host-banco").value.trim() || 'localhost';
        const porta = document.getElementById("modal-input-porta-banco").value.trim() || '3306';

        if (!nome) {
            alert("O nome do banco de dados é obrigatório!");
            return;
        }

        let activeBanco = state.bancos.find(b => b.id_banco === state.activeBancoId);
        if (!activeBanco) {
            activeBanco = { id_banco: Date.now() };
            state.bancos.push(activeBanco);
            state.activeBancoId = activeBanco.id_banco;
        }

        activeBanco.nome_banco = nome;
        activeBanco.usuario_banco = usr;
        activeBanco.senha_banco = pass;
        activeBanco.host = host;
        activeBanco.porta = porta;

        renderBancoSelect();
        phpmaCloseModal();
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
