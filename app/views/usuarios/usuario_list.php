<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<?php
$usuarioLogadoId = isset($_SESSION['usuario_logado']) ? (int) $_SESSION['usuario_logado']->getIdUsuario() : 0;
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-people"></i> Usuários
    </h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Usuário
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['tipo'] === 'sucesso' ? 'sucesso' : 'danger' ?>" role="alert">
        <div class="alert-icon">
            <i class="bi <?= $flash['tipo'] === 'sucesso' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
        </div>
        <div class="alert-body">
            <strong><?= $flash['tipo'] === 'sucesso' ? 'Sucesso' : 'Erro na Operação' ?></strong>
            <div><?= htmlspecialchars($flash['mensagem']) ?></div>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.devstudioAlert === 'function') {
                window.devstudioAlert(<?= json_encode($flash['mensagem']) ?>, <?= json_encode($flash['tipo']) ?>);
            }
        });
    </script>
<?php endif; ?>

<?php if (empty($usuarios)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">👤</div>
        <div class="empty-state-text">Nenhum usuário cadastrado</div>
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            Criar Primeiro Usuário
        </a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo de Perfil</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                    $isSelf = ($usuarioLogadoId > 0 && (int) $usuario['id_usuario'] === $usuarioLogadoId);
                    $tipoPerfilLower = strtolower(trim((string) ($usuario['tipo_perfil'] ?? 'usuario')));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                            <?php if ($isSelf): ?>
                                <span class="badge-voce" title="Sua conta conectada">Você</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td>
                            <span class="badge-perfil badge-perfil-<?= htmlspecialchars($tipoPerfilLower) ?>">
                                <?= htmlspecialchars(strtoupper($usuario['tipo_perfil'])) ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= URL_BASE ?>/usuarios/editar?id=<?= $usuario['id_usuario'] ?>" class="btn btn-edit btn-sm" title="Editar Usuário">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <button type="button"
                                        class="btn btn-danger btn-sm btn-abrir-modal-exclusao"
                                        data-id="<?= htmlspecialchars($usuario['id_usuario']) ?>"
                                        data-nome="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-perfil="<?= htmlspecialchars($usuario['tipo_perfil'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-self="<?= $isSelf ? '1' : '0' ?>"
                                        title="Excluir Usuário">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal de Exclusão de Usuário DevStudio -->
<div id="modalExcluirUsuario" class="usuario-modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalExcluirTitulo">
    <div class="usuario-modal-dialog">
        <div class="usuario-modal-header">
            <h3 id="modalExcluirTitulo" class="usuario-modal-title">
                <i class="bi bi-person-x-fill"></i> Confirmar Exclusão
            </h3>
            <button type="button" class="usuario-modal-close" id="btnFecharModalExclusao" aria-label="Fechar modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="usuario-modal-body">
            <!-- Bloqueio para tentativa de auto-exclusão -->
            <div id="modalAvisoAutoExclusao" class="usuario-modal-alert-bloqueado" style="display: none;">
                <i class="bi bi-shield-lock-fill"></i>
                <div>
                    <strong>Ação Bloqueada:</strong> Você está conectado nesta conta de administrador e não pode excluir o seu próprio usuário.
                </div>
            </div>

            <!-- Card com os dados do usuário a ser excluído -->
            <div class="usuario-modal-card">
                <div class="usuario-modal-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="usuario-modal-info">
                    <div class="usuario-modal-nome" id="modalUsuarioNome">--</div>
                    <div class="usuario-modal-email" id="modalUsuarioEmail">--</div>
                    <div class="usuario-modal-meta">
                        <span class="usuario-badge-id">ID: #<span id="modalUsuarioId">--</span></span>
                        <span class="badge-perfil" id="modalUsuarioPerfil">--</span>
                    </div>
                </div>
            </div>

            <!-- Aviso de confirmação de exclusão -->
            <div id="modalAvisoPerigo" class="usuario-modal-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Atenção:</strong> Tem certeza que deseja excluir permanentemente este usuário da plataforma? Esta ação não pode ser desfeita.
                </div>
            </div>
        </div>

        <div class="usuario-modal-footer">
            <button type="button" class="btn btn-secondary" id="btnCancelarExclusao">
                Cancelar
            </button>
            <form id="formConfirmarExclusao" method="POST" action="<?= URL_BASE ?>/usuarios/excluir" style="display: inline-flex; margin: 0;">
                <input type="hidden" name="id" id="inputExcluirUsuarioId" value="">
                <button type="submit" class="btn btn-danger" id="btnConfirmarExclusao">
                    <i class="bi bi-trash3"></i> Sim, Excluir Usuário
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalExcluirUsuario');
    if (!modal) return;

    const modalNome = document.getElementById('modalUsuarioNome');
    const modalEmail = document.getElementById('modalUsuarioEmail');
    const modalId = document.getElementById('modalUsuarioId');
    const modalPerfil = document.getElementById('modalUsuarioPerfil');
    const inputId = document.getElementById('inputExcluirUsuarioId');
    const btnConfirmar = document.getElementById('btnConfirmarExclusao');
    const avisoAuto = document.getElementById('modalAvisoAutoExclusao');
    const avisoPerigo = document.getElementById('modalAvisoPerigo');
    const btnFechar = document.getElementById('btnFecharModalExclusao');
    const btnCancelar = document.getElementById('btnCancelarExclusao');

    function abrirModal(dados) {
        modalNome.textContent = dados.nome || 'Usuário';
        modalEmail.textContent = dados.email || '';
        modalId.textContent = dados.id || '';
        
        const perfilStr = (dados.perfil || 'usuario').toUpperCase();
        modalPerfil.textContent = perfilStr;
        modalPerfil.className = 'badge-perfil badge-perfil-' + (dados.perfil || 'usuario').toLowerCase();
        
        inputId.value = dados.id || '';

        if (dados.isSelf) {
            avisoAuto.style.display = 'flex';
            avisoPerigo.style.display = 'none';
            btnConfirmar.disabled = true;
            btnConfirmar.style.opacity = '0.5';
            btnConfirmar.style.cursor = 'not-allowed';
            btnConfirmar.title = 'Não é permitido excluir o usuário conectado.';
        } else {
            avisoAuto.style.display = 'none';
            avisoPerigo.style.display = 'flex';
            btnConfirmar.disabled = false;
            btnConfirmar.style.opacity = '1';
            btnConfirmar.style.cursor = 'pointer';
            btnConfirmar.removeAttribute('title');
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function fecharModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-abrir-modal-exclusao').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            abrirModal({
                id: this.dataset.id,
                nome: this.dataset.nome,
                email: this.dataset.email,
                perfil: this.dataset.perfil,
                isSelf: this.dataset.self === '1'
            });
        });
    });

    if (btnFechar) btnFechar.addEventListener('click', fecharModal);
    if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            fecharModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            fecharModal();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>

</body>

</html>
