<?php
require_once __DIR__ . '/../include/head.php';
require_once __DIR__ . '/../include/navigation.php';

function formatarTempoRelativo(string $dataIso): string {
    $timestamp = strtotime($dataIso);
    if (!$timestamp) return '';

    $diff = time() - $timestamp;
    if ($diff < 60) return 'agora há pouco';
    if ($diff < 3600) return 'há ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'há ' . floor($diff / 3600) . ' h';
    if ($diff < 172800) return 'ontem';
    return 'há ' . floor($diff / 86400) . ' dias';
}

function obterIconeAcao(string $acao): string {
    $acaoMin = mb_strtolower($acao, 'UTF-8');
    if (str_contains($acaoMin, 'login')) return 'bi-box-arrow-in-right';
    if (str_contains($acaoMin, 'logout')) return 'bi-box-arrow-right';
    if (str_contains($acaoMin, 'mvc')) return 'bi-braces';
    if (str_contains($acaoMin, 'pagemaker') || str_contains($acaoMin, 'html')) return 'bi-file-earmark-code';
    if (str_contains($acaoMin, 'banco') || str_contains($acaoMin, 'tabela') || str_contains($acaoMin, 'sql')) return 'bi-database';
    if (str_contains($acaoMin, 'usuário') || str_contains($acaoMin, 'perfil') || str_contains($acaoMin, 'senha')) return 'bi-person-gear';
    if (str_contains($acaoMin, 'exclu') || str_contains($acaoMin, 'limp') || str_contains($acaoMin, 'apag')) return 'bi-trash';
    return 'bi-activity';
}
?>

<div class="logs-shell">
    <header class="logs-header page-header">
        <div>
            <p class="etiqueta-inicial">Auditoria & Segurança (RF 15 / UC 15)</p>
            <h1 class="page-title">Logs do Sistema</h1>
            <p class="logs-subtitulo">Acompanhamento cronológico de todas as ações e eventos operacionais da plataforma.</p>
        </div>
        <div class="logs-actions">
            <span class="logs-badge-total" title="Total de registros de auditoria">
                <i class="bi bi-clock-history"></i> <?= (int) ($totalLogs ?? 0) ?> eventos
            </span>
            <a href="<?= URL_BASE ?>/logs/exportar?<?= http_build_query($filtros ?? []) ?>" class="btn btn-secondary" title="Exportar dados filtrados para planilha">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
            <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                <i class="bi bi-people"></i> Gerenciar Usuários
            </a>
            <?php if (!empty($logs)): ?>
                <button type="button" class="btn btn-primary" onclick="if (confirm('Atenção: deseja limpar permanentemente todo o histórico de logs do sistema? Esta ação é irreversível.')) { window.location.href = '<?= URL_BASE ?>/logs/limpar'; }">
                    <i class="bi bi-trash"></i> Limpar Logs
                </button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Cards de Métricas / KPIs -->
    <section class="logs-kpi-grid">
        <article class="logs-kpi-card">
            <div class="logs-kpi-card-head">
                <span>Total de Logs</span>
                <i class="bi bi-database-check logs-kpi-card-icon"></i>
            </div>
            <div class="logs-kpi-card-value"><?= number_format($estatisticas['total'] ?? 0, 0, ',', '.') ?></div>
            <div class="logs-kpi-card-desc">Histórico completo armazenado</div>
        </article>

        <article class="logs-kpi-card">
            <div class="logs-kpi-card-head">
                <span>Registros Hoje</span>
                <i class="bi bi-calendar-day logs-kpi-card-icon"></i>
            </div>
            <div class="logs-kpi-card-value"><?= (int) ($estatisticas['hoje'] ?? 0) ?></div>
            <div class="logs-kpi-card-desc">Ações realizadas no dia atual</div>
        </article>

        <article class="logs-kpi-card">
            <div class="logs-kpi-card-head">
                <span>Usuários Ativos</span>
                <i class="bi bi-person-check logs-kpi-card-icon"></i>
            </div>
            <div class="logs-kpi-card-value"><?= (int) ($estatisticas['usuarios_distintos'] ?? 0) ?></div>
            <div class="logs-kpi-card-desc">Contas com ações auditadas</div>
        </article>

        <article class="logs-kpi-card">
            <div class="logs-kpi-card-head">
                <span>Último Evento</span>
                <i class="bi bi-bell logs-kpi-card-icon"></i>
            </div>
            <div class="logs-kpi-card-value" style="font-size: 15px; font-weight: 600; margin-top: 4px;">
                <?= htmlspecialchars(mb_strimwidth($estatisticas['ultimo_evento_texto'] ?? '-', 0, 32, '...')) ?>
            </div>
            <div class="logs-kpi-card-desc"><?= htmlspecialchars($estatisticas['ultimo_evento_data'] ?? '-') ?></div>
        </article>
    </section>

    <!-- Filtros Dinâmicos -->
    <section class="logs-filtros-panel">
        <form class="logs-filtros-form" method="get" action="<?= URL_BASE ?>/logs">
            <div class="logs-campo">
                <label for="campoBusca">Buscar por termo</label>
                <input type="text" id="campoBusca" name="q" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Ex: login, projeto, nome...">
            </div>

            <div class="logs-campo">
                <label for="campoUsuario">Usuário</label>
                <select id="campoUsuario" name="usuario_id">
                    <option value="">Todos os usuários</option>
                    <?php foreach (($usuariosFiltro ?? []) as $u): ?>
                        <option value="<?= (int) $u['id_usuario'] ?>" <?= (($filtros['usuario_id'] ?? '') == $u['id_usuario']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="logs-campo">
                <label for="campoPerfil">Perfil</label>
                <select id="campoPerfil" name="tipo_perfil">
                    <option value="">Todos os perfis</option>
                    <option value="admin" <?= (($filtros['tipo_perfil'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    <option value="usuario" <?= (($filtros['tipo_perfil'] ?? '') === 'usuario') ? 'selected' : '' ?>>Usuário comum</option>
                </select>
            </div>

            <div class="logs-campo">
                <label for="campoDataInicio">Data Inicial</label>
                <input type="date" id="campoDataInicio" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>">
            </div>

            <div class="logs-campo">
                <label for="campoDataFim">Data Final</label>
                <input type="date" id="campoDataFim" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>">
            </div>

            <div class="logs-filtros-acoes">
                <button type="submit" class="btn btn-primary" title="Aplicar filtros">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= URL_BASE ?>/logs" class="btn btn-secondary" title="Limpar todos os filtros">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
            </div>
        </form>
    </section>

    <!-- Tabela Cronológica de Logs -->
    <section class="logs-table-panel">
        <?php if (empty($logs)): ?>
            <div class="logs-empty">
                <i class="bi bi-search logs-empty-icon"></i>
                <h3 class="logs-empty-title">Nenhum evento localizado</h3>
                <p class="logs-empty-desc">Não foram encontrados registros de log para os critérios e filtros selecionados. Tente ajustar os termos ou o período.</p>
                <a href="<?= URL_BASE ?>/logs" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Redefinir Filtros
                </a>
            </div>
        <?php else: ?>
            <div class="logs-table-wrapper">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th style="width: 170px;">Data / Hora</th>
                            <th style="width: 250px;">Usuário Responsável</th>
                            <th>Ação / Evento Registrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $ehAdmin = $log->ehAdmin();
                            $iconeAcao = obterIconeAcao($log->getAcao());
                            $tempoRelativo = formatarTempoRelativo($log->getData());
                            ?>
                            <tr>
                                <td class="logs-id-cell">#<?= $log->getIdLog() ?></td>
                                <td>
                                    <div class="logs-data-cell">
                                        <span class="logs-data-hora"><?= $log->getDataFormatada() ?></span>
                                        <?php if ($tempoRelativo !== ''): ?>
                                            <span class="logs-data-relativa"><?= htmlspecialchars($tempoRelativo) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="logs-user-cell">
                                        <div class="logs-user-avatar" title="<?= htmlspecialchars($log->getNomeUsuario()) ?>">
                                            <i class="bi <?= $ehAdmin ? 'bi-shield-lock-fill' : 'bi-person-fill' ?>"></i>
                                        </div>
                                        <div class="logs-user-meta">
                                            <div class="logs-user-nome">
                                                <span><?= htmlspecialchars($log->getNomeUsuario()) ?></span>
                                                <span class="logs-badge-perfil <?= $ehAdmin ? 'is-admin' : 'is-user' ?>">
                                                    <?= $ehAdmin ? 'Admin' : 'Usuário' ?>
                                                </span>
                                            </div>
                                            <span class="logs-user-email"><?= htmlspecialchars($log->getEmailUsuario()) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="logs-acao-cell">
                                        <i class="bi <?= $iconeAcao ?> logs-acao-icon" aria-hidden="true"></i>
                                        <span class="logs-acao-texto"><?= htmlspecialchars($log->getAcao()) ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>
</body>
</html>
