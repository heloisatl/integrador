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
            padding: 28px 0 36px;
        }

        .historico-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .historico-shell .btn-primary,
        .historico-shell .btn-danger {
            background-color: var(--accent);
            color: #ffffff;
            border: 1px solid var(--accent);
        }

        .historico-shell .btn-primary:hover,
        .historico-shell .btn-danger:hover {
            background-color: var(--accent-h);
            border-color: var(--accent-h);
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
            }

            .historico-toolbar .page-actions {
                width: 100%;
            }

            .historico-toolbar .page-actions .btn {
                flex: 1 1 auto;
                justify-content: center;
            }

            .historico-meta-list li {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
        }

    </style>

    <section class="historico-shell">
            <header class="historico-toolbar page-header">
                <div>
                    <p class="etiqueta-inicial">Projetos gerados</p>
                    <h1 class="page-title">Histórico</h1>
                </div>
                <div class="page-actions">
                    <span class="historico-contagem" aria-label="<?= $totalHistorico ?> de <?= $limiteHistorico ?> projetos salvos"><?= $totalHistorico ?> / <?= $limiteHistorico ?> projetos</span>
                    <a class="btn btn-secondary" href="<?= URL_BASE ?>/projetos/mvc-creator"><i class="bi bi-braces"></i> Novo MVC Creator</a>
                    <a class="btn btn-secondary" href="<?= URL_BASE ?>/projetos/pagemaker"><i class="bi bi-file-earmark-code"></i> Novo PageMaker</a>
                    <?php if (!empty($historico)): ?>
                        <button class="btn btn-primary" type="button" onclick="if (confirm('Tem certeza que deseja excluir permanentemente todo o histórico?')) { window.location.href = '<?= URL_BASE ?>/projetos/historico/limpar'; }"><i class="bi bi-trash"></i> Limpar Histórico</button>
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
                                                <button class="btn btn-primary btn-sm" type="button" onclick="if (confirm('Tem certeza que deseja excluir permanentemente este projeto do histórico?')) { window.location.href = '<?= URL_BASE ?>/projetos/historico/apagar?id=<?= urlencode((string)($item['id'] ?? '')) ?>'; }"><i class="bi bi-trash"></i> Excluir do Histórico</button>
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

    <?php require_once __DIR__ . '/../include/footer.php'; ?>
    </main>
</div>
</body>

</html>