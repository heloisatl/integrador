<?php

namespace app\tools\gerador;
use app\models\Tabela;
use app\models\Atributo;

class GeradorView {
    /**
     * Gera a view de Listagem (index.php)
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarIndexView(Tabela $tabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = $tabela->getNome_tabelaUC();
        $varPlural = strtolower($tabela->getNome_tabela()) . 's';
        $varSingular = strtolower($tabela->getNome_tabela());

        $ths = "";
        $tds = "";
        foreach ($atributos as $atributo) {
            foreach($atributo as $campo){
                $ths .= "                    <th>" . ucfirst($campo->getNome_atributo()) . "</th>\n";
                $tds .= "                        <td><?= htmlspecialchars(\${$varSingular}['{$campo->getNome_atributo()}'] ?? '') ?></td>\n";
            }
        }

        return <<<PHP
<?php require_once __DIR__ . '/../include/head.php'; ?>
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Listagem de {$nomeClasse}</h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/{$varPlural}/cadastrar" class="btn btn-primary">
            Novo {$nomeClasse}
        </a>
    </div>
</div>

<?php if (empty(\${$varPlural})): ?>
    <div class="empty-state">
        <div class="empty-state-icon">📂</div>
        <div class="empty-state-text">Nenhum registro encontrado</div>
        <a href="<?= URL_BASE ?>/{$varPlural}/cadastrar" class="btn btn-primary">
            Criar Primeiro {$nomeClasse}
        </a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
{$ths}                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (\${$varPlural} as \${$varSingular}): ?>
                    <tr>
{$tds}                        <td>
                            <a href="<?= URL_BASE ?>/{$varPlural}/editar?id=<?= \${$varSingular}['{$chavePrimaria}'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                            <a href="<?= URL_BASE ?>/{$varPlural}/excluir?id=<?= \${$varSingular}['{$chavePrimaria}'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja excluir?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
PHP;
    }

    /**
     * Gera a view de Cadastro (create.php)
     *
     * @param Tabela $tabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarCreateView(Tabela $tabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = $tabela->getNome_tabelaUC();
        $varPlural = strtolower($tabela->getNome_tabela()) . 's';

        $camposForm = "";
        foreach($atributos as $atributo){
            foreach($atributo as $campo){
                if ($campo->getPk()) continue;
                // $label = ucfirst($campo->getNome_atributo());
                $camposForm .= $campo->getInput();

            }
        }

        return <<<PHP
<?php require_once __DIR__ . '/../include/head.php'; ?>
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Cadastrar {$nomeClasse}</h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/{$varPlural}" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="form-container">
    <form action="<?= URL_BASE ?>/{$varPlural}/salvar" method="POST">
{$camposForm}
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>
PHP;
    }

    /**
     * Gera a view de Edição (edit.php)
     *
     * @param Tabela $tabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarEditView(Tabela $tabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = $tabela->getNome_tabelaUC();
        $varPlural = strtolower($tabela->getNome_tabela()) . 's';
        $varSingular = strtolower($tabela->getNome_tabela());

        $camposForm = "";
        foreach ($atributos as $atributo){
            foreach($atributo as $campo){
                if ($campo->getPk()) continue;
                $label = $campo->getNome_atributo();
                $camposForm .= <<<HTML
                    <div class="form-group">
                        <label for="{$campo->getNome_atributo()}">{$label}</label>
                        <input type="text" name="{$campo->getNome_atributo()}" id="{$campo->getNome_atributo()}" value="<?= htmlspecialchars(\${$varSingular}['{$campo->getNome_atributo()}'] ?? '') ?>" class="form-control" required>
                    </div>

                HTML;

            }
        }

        return <<<PHP
<?php require_once __DIR__ . '/../include/head.php'; ?>
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Editar {$nomeClasse}</h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/{$varPlural}" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="form-container">
    <form action="<?= URL_BASE ?>/{$varPlural}/atualizar" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars(\${$varSingular}['{$chavePrimaria}'] ?? '') ?>">
{$camposForm}
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>
PHP;
    }

    /**
     * Salva todas as views na pasta app/views/{varPlural}/
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @param string $caminhoBase
     * @return array
     */
    public function salvarViews(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../views'): array {
        $varPlural = strtolower($tabela->getNome_tabela()) . 's';
        $pastaDestino = "{$caminhoBase}/{$varPlural}";

        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $arquivosSalvos = [];

        $indexContent = $this->gerarIndexView($tabela, $atributos, $chavePrimaria);
        file_put_contents("{$pastaDestino}/index.php", $indexContent);
        $arquivosSalvos[] = "{$pastaDestino}/index.php";

        $createContent = $this->gerarCreateView($tabela, $atributos, $chavePrimaria);
        file_put_contents("{$pastaDestino}/create.php", $createContent);
        $arquivosSalvos[] = "{$pastaDestino}/create.php";

        $editContent = $this->gerarEditView($tabela, $atributos, $chavePrimaria);
        file_put_contents("{$pastaDestino}/edit.php", $editContent);
        $arquivosSalvos[] = "{$pastaDestino}/edit.php";

        return $arquivosSalvos;
    }
}
