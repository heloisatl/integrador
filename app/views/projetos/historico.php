<?php
include_once(__DIR__ . "/../include/head.php");
include_once(__DIR__ . "/../include/navigation.php");

$historicoPorModulo = [
        'PageMaker' => [],
        'MVC Creator' => [],
    ];

    foreach (($historico ?? []) as $item) {
        $modulo = $item['modulo'] ?? 'MVC Creator';
        if (!isset($historicoPorModulo[$modulo])) {
            $historicoPorModulo[$modulo] = [];
        }
        $historicoPorModulo[$modulo][] = $item;
    }
    $totalHistorico = count($historico ?? []);
    $limiteHistorico = 10;
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/form-styles.css">

    <style>
        .historico-shell {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 0 0 36px;
            width: 100%;
            margin: 0;
        }

        .historico-toolbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .historico-toolbar .page-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .historico-toolbar .page-actions .btn {
            white-space: nowrap !important;
            word-break: keep-all !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 16px;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        .historico-panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
        }

        .historico-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .historico-title h3 {
            margin: 0;
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            color: var(--text);
        }

        .historico-list {
            display: grid;
            gap: 24px;
        }

        .historico-section-block {
            display: grid;
            gap: 12px;
        }

        .historico-cards-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(320px, 380px));
            justify-content: start;
        }

        .historico-card {
            background: linear-gradient(145deg, var(--surface), var(--panel));
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-width: 0;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .historico-card:hover {
            border-color: rgba(91, 106, 240, 0.55);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.1);
            transform: translateY(-2px);
        }

        .historico-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: var(--muted);
            font-size: 12px;
        }

        .historico-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            background: rgba(91, 106, 240, 0.12);
            color: var(--accent);
            border: 1px solid rgba(91, 106, 240, 0.4);
            font-weight: 600;
        }

        .historico-card h4 {
            margin: 0;
            color: var(--text);
            font-family: 'Syne', sans-serif;
            font-size: 17px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .historico-meta-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            color: var(--muted);
            font-size: 12px;
        }

        .historico-meta-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 8px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .historico-meta-list strong {
            color: var(--text);
            font-weight: 600;
        }

        .historico-meta {
            font-size: 13px;
            color: var(--muted);
            font-family: 'DM Mono', monospace;
            font-weight: 500;
        }

        .historico-card-actions,
        .historico-panel-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: stretch;
            margin-top: auto;
        }

        .historico-card-actions .btn {
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow-wrap: normal !important;
            font-size: 12px;
            padding: 8px 12px;
            min-height: 36px;
            flex: 1 1 auto;
            justify-content: center;
            box-sizing: border-box;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
        }

        .historico-shell .btn-primary {
            background-color: var(--accent);
            color: #ffffff;
            border: 1px solid var(--accent);
        }

        .historico-shell .btn-primary:hover {
            background-color: var(--accent-h);
            border-color: var(--accent-h);
            color: #ffffff;
        }

        .historico-shell .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
            border: 1px solid #ef4444;
        }

        .historico-shell .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .historico-empty {
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 28px 20px;
            color: var(--muted);
            background: rgba(148, 163, 184, 0.03);
            text-align: center;
        }

        .historico-contagem {
            align-items: center;
            background: rgba(91, 106, 240, 0.12);
            border: 1px solid rgba(91, 106, 240, 0.28);
            border-radius: 999px;
            color: var(--accent);
            display: inline-flex;
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 500;
            gap: 6px;
            padding: 7px 11px;
            white-space: nowrap;
        }

        .historico-aviso-limite {
            align-items: flex-start;
            background: rgba(91, 106, 240, 0.08);
            border: 1px solid rgba(91, 106, 240, 0.25);
            border-left: 3px solid var(--accent);
            border-radius: 12px;
            color: var(--muted);
            display: flex;
            font-size: 13px;
            gap: 12px;
            line-height: 1.6;
            padding: 14px 16px;
        }

        .historico-aviso-limite strong {
            color: var(--text);
        }

        .historico-aviso-icone {
            align-items: center;
            background: rgba(91, 106, 240, 0.14);
            border-radius: 9px;
            color: var(--accent);
            display: inline-flex;
            flex: 0 0 30px;
            height: 30px;
            justify-content: center;
            font-weight: 700;
        }

        @media (max-width: 720px) {
            .historico-panel {
                padding: 16px;
            }

            .historico-toolbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .historico-toolbar .page-actions {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .historico-toolbar .page-actions .btn,
            .historico-toolbar .page-actions .historico-contagem {
                width: 100%;
                justify-content: center;
                text-align: center;
                min-height: 44px;
            }

            .historico-meta-list li {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }

            .historico-cards-grid {
                grid-template-columns: 1fr;
            }

            .historico-card-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .historico-card-actions .btn {
                width: 100%;
                justify-content: center;
                min-height: 42px;
            }

            .historico-modal-dialog {
                max-width: calc(100vw - 24px);
                max-height: calc(100dvh - 32px);
                overflow-y: auto;
                padding: 20px 16px;
            }

            .historico-modal-footer {
                flex-direction: column-reverse;
                gap: 10px;
                width: 100%;
            }

            .historico-modal-footer .btn {
                width: 100%;
                justify-content: center;
                min-height: 44px;
            }
        }

        /* Modais Customizados do Histórico */
        .historico-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 23, 0.72);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            padding: 16px;
            box-sizing: border-box;
        }

        .historico-modal-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .historico-modal-dialog {
            background: var(--surface-card, #111726);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            padding: 24px;
            box-shadow: 0 24px 56px rgba(0, 0, 0, 0.45);
            transform: translateY(-8px) scale(0.97);
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .historico-modal-overlay.open .historico-modal-dialog {
            transform: translateY(0) scale(1);
        }

        .historico-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 14px;
        }

        .historico-modal-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .historico-modal-title i {
            color: #ef4444;
            font-size: 20px;
        }

        .historico-modal-close {
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 16px;
            line-height: 1;
            padding: 6px;
            cursor: pointer;
            border-radius: 6px;
            transition: color 0.15s ease, background-color 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .historico-modal-close:hover {
            color: var(--text);
            background: rgba(148, 163, 184, 0.12);
        }

        .historico-modal-body {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .historico-modal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .historico-modal-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(91, 106, 240, 0.12);
            border: 1px solid rgba(91, 106, 240, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--accent);
            flex-shrink: 0;
        }

        .historico-modal-info {
            flex: 1;
            min-width: 0;
        }

        .historico-modal-nome {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .historico-modal-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
        }

        .historico-modal-badge {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: var(--accent);
            background: rgba(91, 106, 240, 0.1);
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid rgba(91, 106, 240, 0.25);
            font-weight: 600;
        }

        .historico-modal-warning {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 13px 15px;
            color: var(--text);
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.5;
        }

        .historico-modal-warning i {
            color: #ef4444;
            font-size: 18px;
            line-height: 1;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .historico-modal-warning strong {
            color: #fca5a5;
        }

        .historico-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

    </style>

    <section class="historico-shell">
            <header class="historico-toolbar page-header">
                <div>
                    <h1 class="page-title">
                        <i class="bi bi-clock-history"></i> Histórico
                    </h1>
                </div>
                <div class="page-actions">
                    <span class="historico-contagem" aria-label="<?= $totalHistorico ?> de <?= $limiteHistorico ?> projetos salvos"><?= $totalHistorico ?> / <?= $limiteHistorico ?> projetos</span>
                    <a class="btn btn-secondary" href="<?= URL_BASE ?>/projetos/mvc-creator"><i class="bi bi-braces"></i> Novo MVC Creator</a>
                    <a class="btn btn-secondary" href="<?= URL_BASE ?>/projetos/pagemaker"><i class="bi bi-file-earmark-code"></i> Novo PageMaker</a>
                    <?php if (!empty($historico)): ?>
                        <button class="btn btn-danger" type="button" id="btnAbrirLimparHistorico"><i class="bi bi-trash"></i> Limpar Histórico</button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="historico-aviso-limite" role="note">
                <span class="historico-aviso-icone" aria-hidden="true">i</span>
                <span><strong>Limite do histórico:</strong> são mantidos até <?= $limiteHistorico ?> projetos no total. Ao salvar um novo projeto quando o limite estiver cheio, o mais antigo será removido automaticamente.</span>
            </div>

            <section class="historico-panel">
                    <?php if (empty($historico)): ?>
                        <div class="historico-empty">Nenhum projeto foi salvo no histórico ativo.</div>
                    <?php else: ?>
                        <div class="historico-list">
                            <?php foreach ($historicoPorModulo as $modulo => $itens): ?>
                                <?php if (empty($itens)) continue; ?>
                                <div class="historico-section-block">
                                    <div class="historico-title">
                                        <h3><?= htmlspecialchars($modulo) ?></h3>
                                    </div>
                                    <div class="historico-cards-grid">
                                    <?php foreach ($itens as $item): ?>
                                        <?php
                                        $criadoEm = $item['criado_em'] ?? date('c');
                                        $detalhe = $modulo === 'PageMaker'
                                            ? ($item['conteudo']['arquivo'] ?? 'Arquivo HTML')
                                            : ($item['conteudo']['banco'] ?? ($item['conteudo']['database'] ?? ($item['conteudo']['banco_nome'] ?? 'Banco não informado')));
                                        ?>
                                        <article class="historico-card">
                                            <div class="historico-card-head">
                                                <span class="historico-tag"><?= htmlspecialchars($modulo) ?></span>
                                            </div>
                                            <h4><?= htmlspecialchars($item['nome'] ?? 'Projeto sem nome') ?></h4>
                                            <ul class="historico-meta-list">
                                                <li><span>Criado em</span><strong><?= date('d/m/Y H:i', strtotime($criadoEm)) ?></strong></li>
                                                <li><span><?= $modulo === 'PageMaker' ? 'Arquivo' : 'Banco' ?></span><strong><?= htmlspecialchars((string) $detalhe) ?></strong></li>
                                            </ul>
                                            <div class="historico-card-actions">
                                                <?php if ($modulo === 'PageMaker'): ?>
                                                    <a class="btn btn-secondary btn-sm" href="<?= URL_BASE ?>/projetos/historico/baixar?id=<?= urlencode((string)($item['id'] ?? '')) ?>&arquivo=html"><i class="bi bi-download"></i> Baixar HTML</a>
                                                    <?php if (is_string($item['conteudo']['css'] ?? null) && $item['conteudo']['css'] !== ''): ?>
                                                        <a class="btn btn-secondary btn-sm" href="<?= URL_BASE ?>/projetos/historico/baixar?id=<?= urlencode((string)($item['id'] ?? '')) ?>&arquivo=css"><i class="bi bi-file-earmark-code"></i> Baixar CSS</a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a class="btn btn-secondary btn-sm" href="<?= URL_BASE ?>/projetos/historico/baixar?id=<?= urlencode((string)($item['id'] ?? '')) ?>"><i class="bi bi-download"></i> Baixar Projeto</a>
                                                <?php endif; ?>
                                                <button class="btn btn-danger btn-sm btn-abrir-modal-excluir" type="button" data-id="<?= htmlspecialchars((string)($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-nome="<?= htmlspecialchars($item['nome'] ?? 'Projeto sem nome', ENT_QUOTES, 'UTF-8') ?>" data-modulo="<?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8') ?>" data-detalhe="<?= htmlspecialchars((string)$detalhe, ENT_QUOTES, 'UTF-8') ?>" data-criado="<?= date('d/m/Y H:i', strtotime($criadoEm)) ?>"><i class="bi bi-trash"></i> Excluir do Histórico</button>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            </section>
        </section>

        <!-- Modal de Exclusão Individual do Histórico DevStudio -->
        <div id="modalExcluirHistoricoItem" class="historico-modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalExcluirItemTitulo">
            <div class="historico-modal-dialog">
                <div class="historico-modal-header">
                    <h3 id="modalExcluirItemTitulo" class="historico-modal-title">
                        <i class="bi bi-trash3-fill"></i> Excluir do Histórico
                    </h3>
                    <button type="button" class="historico-modal-close" id="btnFecharModalExcluirItem" aria-label="Fechar modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="historico-modal-body">
                    <div class="historico-modal-card">
                        <div class="historico-modal-avatar">
                            <i class="bi bi-file-earmark-code" id="modalItemIcone"></i>
                        </div>
                        <div class="historico-modal-info">
                            <div class="historico-modal-nome" id="modalItemNome">--</div>
                            <div class="historico-modal-meta">
                                <span class="historico-modal-badge" id="modalItemModulo">--</span>
                                <span id="modalItemDetalhe">--</span>
                                <span style="opacity: 0.6;">•</span>
                                <span id="modalItemCriado">--</span>
                            </div>
                        </div>
                    </div>

                    <div class="historico-modal-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <strong>Atenção:</strong> Tem certeza que deseja excluir permanentemente este projeto do histórico? Esta ação não pode ser desfeita.
                        </div>
                    </div>
                </div>

                <div class="historico-modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelarExcluirItem">
                        Cancelar
                    </button>
                    <a href="#" class="btn btn-danger" id="btnConfirmarExcluirItem">
                        <i class="bi bi-trash3"></i> Sim, Excluir do Histórico
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal de Limpeza Geral do Histórico DevStudio -->
        <div id="modalLimparHistoricoGeral" class="historico-modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalLimparGeralTitulo">
            <div class="historico-modal-dialog">
                <div class="historico-modal-header">
                    <h3 id="modalLimparGeralTitulo" class="historico-modal-title">
                        <i class="bi bi-trash3-fill"></i> Limpar Todo o Histórico
                    </h3>
                    <button type="button" class="historico-modal-close" id="btnFecharModalLimparGeral" aria-label="Fechar modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="historico-modal-body">
                    <div class="historico-modal-card">
                        <div class="historico-modal-avatar" style="background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.3); color: #ef4444;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="historico-modal-info">
                            <div class="historico-modal-nome"><?= $totalHistorico ?> <?= $totalHistorico === 1 ? 'projeto registrado' : 'projetos registrados' ?></div>
                            <div class="historico-modal-meta">
                                <span><?= $totalHistorico === 1 ? 'O projeto salvo no histórico será removido.' : 'Todos os projetos salvos no histórico serão removidos.' ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="historico-modal-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <strong>Atenção Irreversível:</strong> Tem certeza que deseja limpar permanentemente todo o histórico? <?= $totalHistorico === 1 ? 'O único projeto salvo será perdido e não poderá ser recuperado.' : ('Todos os ' . $totalHistorico . ' projetos salvos serão perdidos e não poderão ser recuperados.') ?>
                        </div>
                    </div>
                </div>

                <div class="historico-modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelarLimparGeral">
                        Cancelar
                    </button>
                    <a href="<?= URL_BASE ?>/projetos/historico/limpar" class="btn btn-danger" id="btnConfirmarLimparGeral">
                        <i class="bi bi-trash3"></i> Sim, Limpar Tudo
                    </a>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Modal de Exclusão Individual ---
            const modalItem = document.getElementById('modalExcluirHistoricoItem');
            const modalItemNome = document.getElementById('modalItemNome');
            const modalItemModulo = document.getElementById('modalItemModulo');
            const modalItemDetalhe = document.getElementById('modalItemDetalhe');
            const modalItemCriado = document.getElementById('modalItemCriado');
            const modalItemIcone = document.getElementById('modalItemIcone');
            const btnConfirmarItem = document.getElementById('btnConfirmarExcluirItem');
            const btnFecharItem = document.getElementById('btnFecharModalExcluirItem');
            const btnCancelarItem = document.getElementById('btnCancelarExcluirItem');

            function abrirModalItem(dados) {
                if (!modalItem) return;
                if (modalItemNome) modalItemNome.textContent = dados.nome || 'Projeto sem nome';
                if (modalItemModulo) modalItemModulo.textContent = dados.modulo || '';
                if (modalItemDetalhe) modalItemDetalhe.textContent = dados.detalhe || '';
                if (modalItemCriado) modalItemCriado.textContent = dados.criado || '';
                if (modalItemIcone) {
                    modalItemIcone.className = dados.modulo === 'PageMaker' ? 'bi bi-file-earmark-code' : 'bi bi-braces';
                }
                if (btnConfirmarItem) {
                    btnConfirmarItem.href = '<?= URL_BASE ?>/projetos/historico/apagar?id=' + encodeURIComponent(dados.id || '');
                }
                modalItem.classList.add('open');
                modalItem.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function fecharModalItem() {
                if (!modalItem) return;
                modalItem.classList.remove('open');
                modalItem.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('.btn-abrir-modal-excluir').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    abrirModalItem({
                        id: this.dataset.id,
                        nome: this.dataset.nome,
                        modulo: this.dataset.modulo,
                        detalhe: this.dataset.detalhe,
                        criado: this.dataset.criado
                    });
                });
            });

            if (btnFecharItem) btnFecharItem.addEventListener('click', fecharModalItem);
            if (btnCancelarItem) btnCancelarItem.addEventListener('click', fecharModalItem);

            if (modalItem) {
                modalItem.addEventListener('click', function(e) {
                    if (e.target === modalItem) fecharModalItem();
                });
            }

            // --- Modal de Limpeza Geral ---
            const modalGeral = document.getElementById('modalLimparHistoricoGeral');
            const btnAbrirGeral = document.getElementById('btnAbrirLimparHistorico');
            const btnFecharGeral = document.getElementById('btnFecharModalLimparGeral');
            const btnCancelarGeral = document.getElementById('btnCancelarLimparGeral');

            function abrirModalGeral() {
                if (!modalGeral) return;
                modalGeral.classList.add('open');
                modalGeral.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function fecharModalGeral() {
                if (!modalGeral) return;
                modalGeral.classList.remove('open');
                modalGeral.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            if (btnAbrirGeral) btnAbrirGeral.addEventListener('click', abrirModalGeral);
            if (btnFecharGeral) btnFecharGeral.addEventListener('click', fecharModalGeral);
            if (btnCancelarGeral) btnCancelarGeral.addEventListener('click', fecharModalGeral);

            if (modalGeral) {
                modalGeral.addEventListener('click', function(e) {
                    if (e.target === modalGeral) fecharModalGeral();
                });
            }

            // Fechamento via Tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modalItem && modalItem.classList.contains('open')) fecharModalItem();
                    if (modalGeral && modalGeral.classList.contains('open')) fecharModalGeral();
                }
            });
        });
        </script>

    <?php require_once __DIR__ . '/../include/footer.php'; ?>
    </main>
</div>
</body>

</html>