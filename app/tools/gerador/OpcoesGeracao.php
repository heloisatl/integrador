<?php

namespace app\tools\gerador;


 //Opções de geração escolhidas na etapa "Opções de Geração".
 

class OpcoesGeracao {
    // Valores aceitos (os mesmos `value` dos checkboxes .mvc-option do front-end)
    public const CAMADAS = ['model', 'controller', 'repository', 'service', 'views', 'config', 'index', 'database'];

    // Artefatos que a RN 06 obriga a gerar quando não há banco local ativo 
    private const FORCADAS_SEM_BANCO_LOCAL = ['config', 'database'];

 
    private array $desativadas = [];
    private bool $semBancoLocal;
    private bool $comentarios;

    public function __construct(array $camadasDesativadas = [], bool $semBancoLocal = false, bool $comentarios = true) {
        $nomes = array_filter($camadasDesativadas, 'is_scalar');
        $this->desativadas = array_values(array_intersect(self::CAMADAS, array_map('strval', $nomes)));
        $this->semBancoLocal = $semBancoLocal;
        $this->comentarios = $comentarios;
    }

    
    public static function fromRequest(array $post, array $banco = []): self {
        $bruto = $post['camadasDesativadas'] ?? [];
        if (is_string($bruto)) {
            $bruto = trim($bruto) === '' ? [] : json_decode($bruto, true);
        }

        $nomes = [];
        if (is_array($bruto)) {
            foreach ($bruto as $item) {
                $nomes[] = is_array($item) ? ($item['gerador'] ?? '') : $item;
            }
        }

        $semBanco = filter_var($post['semBancoLocal'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!empty($banco) && (empty($banco['host']) || empty($banco['usuario_banco']))) {
            $semBanco = true;
        }

        $comentarios = true;
        if (array_key_exists('comentarios', $post)) {
            $val = $post['comentarios'];
            if (is_bool($val)) {
                $comentarios = $val;
            } else {
                $str = strtolower(trim((string)$val));
                if ($str === '0' || $str === 'false' || $str === 'nao' || $str === 'off') {
                    $comentarios = false;
                } else {
                    $comentarios = true;
                }
            }
        }

        return new self($nomes, $semBanco, $comentarios);
    }

    public function ativa(string $camada): bool {
        if ($this->semBancoLocal && in_array($camada, self::FORCADAS_SEM_BANCO_LOCAL, true)) {
            return true;
        }
        return in_array($camada, self::CAMADAS, true) && !in_array($camada, $this->desativadas, true);
    }

   
    public function camadasAtivas(): array {
        return array_values(array_filter(self::CAMADAS, fn(string $c) => $this->ativa($c)));
    }

    public function isSemBancoLocal(): bool {
        return $this->semBancoLocal;
    }

    public function comentariosAtivos(): bool {
        return $this->comentarios;
    }

    //estes avisos só indicam que o código gerado ficará incompleto.


    public function avisos(): array {
        $avisos = [];

        if ($this->ativa('controller') && !$this->ativa('repository') && !$this->ativa('service')) {
            $avisos[] = 'O Controller usa Repositório (DAO) ou Service para acessar dados, e nenhum dos dois foi marcado.';
        }
        if ($this->ativa('controller') && !$this->ativa('model')) {
            $avisos[] = 'O Controller instancia as classes Model, mas a camada Model foi desmarcada.';
        }
        if ($this->ativa('service') && !$this->ativa('repository')) {
            $avisos[] = 'O Service delega ao Repositório (DAO), mas a camada Repositório foi desmarcada.';
        }
        if ($this->ativa('repository') && !$this->ativa('model')) {
            $avisos[] = 'O Repositório (DAO) lê os dados dos Models, mas a camada Model foi desmarcada.';
        }
        if ($this->ativa('repository') && !$this->ativa('config')) {
            $avisos[] = 'O Repositório (DAO) usa as constantes de conexão do config.php, que não será gerado.';
        }
        if ($this->ativa('index') && !$this->ativa('controller')) {
            $avisos[] = 'O index.php será gerado sem rotas de tabelas, pois a camada Controller foi desmarcada.';
        }
        if ($this->ativa('index') && !$this->ativa('config')) {
            $avisos[] = 'O index.php não carregará o config.php, pois ele não será gerado.';
        }

        return $avisos;
    }
}
