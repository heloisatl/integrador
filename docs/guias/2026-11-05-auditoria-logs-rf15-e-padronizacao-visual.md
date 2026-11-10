# Guia Técnico de Arquitetura e Implementação - Auditoria & Logs do Sistema (RF 15 / UC 15) e Padronização Visual DevStudio

**Data da Alteração**: `2026-11-05`  
**Título do Guia**: `2026-11-05-auditoria-logs-rf15-e-padronizacao-visual.md`  
**Módulos**: Auditoria & Segurança / Gestão de Usuários / Interface e Notificações Globais  
**Público-Alvo**: Desenvolvedores de Software, Engenheiros de QA, Arquitetos de Soluções e Mantenedores do Código  

---

## 1. Contexto e Motivação da Implementação

Este documento formaliza as alterações arquiteturais, regras de negócio e novas funcionalidades implementadas no projeto **DevStudio**, com ênfase no atendimento aos requisitos funcionais mapeados na modelagem de casos de uso e banco de dados:

1. **Atendimento ao RF 15 / UC 15 (Consultar Logs)**:
   - Disponibilização de uma tela restrita que permite o acompanhamento cronológico de todas as ações e eventos operacionais do sistema.
   - Controle estrito de acesso: permissão concedida **exclusivamente a usuários com perfil de Administrador** (`adminRequired()`).
   - Mapeamento e persistência baseados na tabela `log` definida no modelo relacional (`app/database/scripts/script.sql`).
2. **Substituição Global de `alert()` Nativos por Notificações Estilizadas**:
   - Eliminação de caixas de diálogo nativas e bloqueantes do navegador.
   - Implementação de um subsistema de Toasts/Notificações flutuantes com a identidade visual do DevStudio (`DM Sans`, `Syne`, bordas temáticas, ícones contextuais e suporte aos modos claro e escuro).
   - Sobrescrita graciosa de `window.alert` no navegador como salvaguarda arquitetural.
3. **Padronização Visual entre Perfis (Administrador vs. Usuário Comum)**:
   - Correção de anomalias no botão de perfil da barra de navegação (eliminação de duplicidade de ícones quando há foto de avatar).
   - Eliminação de cores destoantes (remoção de botões vermelhos no Histórico, adotando o azul característico `var(--accent)` = `#5b6af0`).
   - Correção de quebras verticais de texto em botões (`white-space: nowrap !important; word-break: keep-all !important;`).
   - Padronização do grid de cards do Histórico (`repeat(auto-fill, minmax(320px, 380px))`), impedindo distorções em telas com poucos projetos salvos.
   - Preservação integral do contador e aviso didático do requisito funcional de histórico (`.historico-aviso-limite`).
4. **Refinamento de Mensagens de Segurança**:
   - Ajuste na mensagem de validação do formulário de usuário/perfil para evitar vazamento de jargões técnicos de infraestrutura/segurança (ex.: "SQL Injection"), substituindo-a por uma diretriz clara e amigável: *"Caracteres especiais não são permitidos."*

---

## 2. Visão Geral da Arquitetura do Módulo de Logs (RF 15)

O módulo de auditoria foi construído respeitando integralmente o padrão **MVC (Model-View-Repository-Service-Controller)** do ecossistema DevStudio:

```
┌────────────────────────────────────────────────────────┐
│                   Navegador / Cliente                   │
└───────────────────────────┬────────────────────────────┘
                            │ HTTP GET /logs
                            ▼
┌────────────────────────────────────────────────────────┐
│             public/index.php (Front Controller)        │
└───────────────────────────┬────────────────────────────┘
                            │ Roteamento
                            ▼
┌────────────────────────────────────────────────────────┐
│      app/controllers/LogController.php                 │
│      - Validação de privilégios: adminRequired()       │
└───────────────────────────┬────────────────────────────┘
                            │ Invocação de Regras de Negócio
                            ▼
┌────────────────────────────────────────────────────────┐
│      app/services/LogService.php                       │
│      - Formatação de filtros, estatísticas e CSV       │
│      - Método estático: LogService::registrar()        │
└───────────────────────────┬────────────────────────────┘
                            │ Persistência e Consultas SQL
                            ▼
┌────────────────────────────────────────────────────────┐
│      app/repositories/LogRepository.php                │
│      - Operações CRUD na tabela `log` via PDO          │
└───────────────────────────┬────────────────────────────┘
                            │ Entidades
                            ▼
┌────────────────────────────────────────────────────────┐
│      app/models/Log.php (Representação de Objeto)      │
└────────────────────────────────────────────────────────┘
```

---

## 3. Modelagem de Dados e Integração SQL

Conforme estabelecido no diagrama de caso de uso geral e no diagrama entidade-relacionamento físico do projeto, a tabela `log` possui a seguinte estrutura e vínculos:

```sql
CREATE TABLE `log` (
  `id_log` INT NOT NULL AUTO_INCREMENT,
  `fk_usuario` INT NOT NULL,
  `acao` VARCHAR(255) NOT NULL,
  `data` DATETIME NOT NULL,

  PRIMARY KEY (`id_log`),
  INDEX `idx_log_usuario` (`fk_usuario`),

  CONSTRAINT `fk_log_usuario`
    FOREIGN KEY (`fk_usuario`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
```

### Relacionamento e Consultas Otimizadas
As consultas de listagem realizam um `LEFT JOIN` com a tabela `usuario`, permitindo extrair e exibir:
- Nome completo do usuário (`usuario.nome`);
- E-mail institucional/pessoal (`usuario.email`);
- Papel no sistema (`usuario.tipo_perfil` -> `admin` ou `usuario`).

---

## 4. Detalhamento Técnico dos Componentes Criados e Alterados

### 4.1 Entidade de Domínio (`app/models/Log.php`)
Encapsula os atributos de cada evento de auditoria:
- **`getIdLog(): int`**: Identificador único sequencial.
- **`getFkUsuario(): int`**: Chave estrangeira do autor da ação.
- **`getAcao(): string`**: Descrição textual da operação realizada.
- **`getData(): string`**: Timestamp ISO (`YYYY-MM-DD HH:MM:SS`).
- **`getDataFormatada(): string`**: Timestamp no formato brasileiro amigável (`DD/MM/YYYY HH:MM:SS`).
- **`getNomeUsuario(): string`**: Nome do usuário ou fallback (`Usuário #ID`).
- **`getEmailUsuario(): string`**: E-mail do usuário.
- **`getTipoPerfil(): string`**: Papel (`admin` ou `usuario`).
- **`ehAdmin(): bool`**: Helper booleano para validações rápidas.
- **`toArray(): array`**: Serialização para exportações e JSON.

### 4.2 Camada de Dados (`app/repositories/LogRepository.php`)
- **`create(int $fkUsuario, string $acao, ?string $data)`**: Insere um novo evento com tratamento automático de data corrente e limite de 255 caracteres (`mb_substr`).
- **`getAll(array $filtros, int $limite, int $offset)`**: Monta queries dinâmicas preparadas (`PDO::prepare`) suportando filtros por busca textual (`q`), busca de usuário por nome/e-mail (`usuario`), ID do usuário (`usuario_id`), intervalo de datas (`data_inicio` a `data_fim`) e perfil (`tipo_perfil`), ordenando sempre cronologicamente de forma decrescente (`ORDER BY l.data DESC, l.id_log DESC`).
- **`contarTotal(array $filtros)`**: Retorna o quantitativo total de registros sob os mesmos critérios de filtro.
- **`garantirTabela()`**: Mecanismo de auto-cura (`CREATE TABLE IF NOT EXISTS`) para garantir que o sistema não falhe mesmo se o banco for recriado em novo ambiente.
- **Imutabilidade e Segurança de Auditoria**: Métodos de limpeza ou truncamento foram deliberadamente retirados por conformidade com segurança da informação (ISO 27001, LGPD e princípio de não-repúdio). Audit logs devem ser estritamente *append-only*.

### 4.3 Camada de Serviço (`app/services/LogService.php`)
- **`LogService::registrar(string $acao, ?int $fkUsuario = null): bool`**: Método estático e desacoplado que pode ser chamado de qualquer controller, service ou ferramenta do sistema. Se o ID do usuário não for explicitado, recupera automaticamente da sessão ativa (`$_SESSION['usuario_logado']`). Em eventos anônimos de sistema, vincula ao administrador padrão (ID 1). Falhas de log são capturadas internamente para nunca interromper a regra de negócio principal.
- **`exportarCsv(array $filtros)`**: Gera e transmite para o cliente um arquivo `.csv` codificado com **BOM UTF-8** (`\xEF\xBB\xBF`) e delimitador ponto-e-vírgula (`;`), garantindo abertura perfeita em planilhas como Microsoft Excel e LibreOffice Calc sem caracteres corrompidos.
- **`popularLogsIniciaisSeVazio()`**: Popula eventos básicos de inicialização (bootstrap) caso a tabela esteja totalmente vazia, assegurando que o administrador visualize uma interface rica desde o primeiro acesso.

### 4.4 Controlador e Rotas (`app/controllers/LogController.php` & `public/index.php`)
- Rotas registradas:
  - `GET /logs`: Executa `LogController@index`, validando `adminRequired()` e renderizando a interface de auditoria.
  - `GET /logs/exportar`: Executa `LogController@exportar`, disparando o download do arquivo CSV com os registros filtrados.
  - *(Nota de Segurança)*: A rota de limpeza foi intencionalmente eliminada para manter a trilha de auditoria append-only.

### 4.5 Acoplamento Automático dos Eventos no Sistema
O método `LogService::registrar()` foi integrado aos seguintes eventos em tempo real:
- **`AutenticacaoController.php`**:
  - `logar()`: *"Login efetuado com sucesso."*
  - `logout()`: *"Logout do sistema efetuado."*
  - `salvarCadastro()`: *"Novo cadastro de usuário realizado: {nome}"*
- **`UsuarioController.php`**:
  - `salvar()`: *"Novo usuário cadastrado pelo Administrador: {nome}"*
  - `atualizar()`: *"Dados de usuário atualizados pelo Administrador (ID: {id})"*
  - `excluir()`: *"Usuário excluído pelo Administrador (ID: {id})"*
  - `atualizarPerfil()`: *"Perfil de usuário atualizado com sucesso."*
- **`ProjetoController.php` (MVC Creator & PageMaker)**:
  - `gerarMvc()`: *"Geração de sistema MVC Creator: {nomeProjeto}"*
  - `registrarProjetoNoHistorico()`: *"Projeto salvo no histórico ({modulo}): {nome}"*
  - `baixarProjetoHistorico()`: *"Download de projeto do histórico ({modulo}): {nome}"*
  - `apagarProjetoHistorico()`: *"Projeto removido do histórico (ID: {id})"*
  - `limparHistorico()`: *"Histórico de projetos foi limpo pelo usuário."*
- **`PhpMeuAmigoController.php` (Modelagem de Banco de Dados)**:
  - `salvarBanco()`: *"Novo banco de dados criado no modelador: {nome_banco}"* / *"Configurações do banco de dados atualizadas: {nome_banco}"*
  - `excluirBanco()`: *"Banco de dados excluído no modelador: {nome_banco}"*
  - `salvarTabela()`: *"Nova tabela criada no modelador: {nome_tabela}"* / *"Tabela renomeada no modelador: {nome_tabela}"*
  - `excluirTabela()`: *"Tabela excluída no modelador: {nome_tabela}"*
  - `salvarAtributo()`: *"Novo atributo adicionado no modelador: {nome_atributo} ({tipo})"* / *"Atributo atualizado no modelador: {nome_atributo} ({tipo})"*
  - `excluirAtributo()`: *"Atributo excluído no modelador: {nome_atributo}"*
  - `importarBancoLocal()`: *"Banco MySQL local importado no modelador: {banco_selecionado}"*
  - `exportarSql()`: *"Exportação de script SQL do banco: {nomeArquivo}"*

---

## 5. Interface Visual e Estilização (`app/views/logs/index.php` & `logs.css`)

A interface foi projetada com foco em usabilidade, escalabilidade e conformidade com o design system do DevStudio:
1. **Cabeçalho & Ações Limpas**:
   - Tag de identificação: `AUDITORIA & SEGURANÇA (RF 15 / UC 15)`.
   - Badge com totalizador cronológico de eventos (`x eventos`).
   - Botão de exportação para CSV.
   - Remoção de ações redundantes ou perigosas (como "Gerenciar Usuários" já existente na topbar e botão de limpeza de logs).
2. **Barra de Filtros Escalável**:
   - Busca instantânea por termo geral (ação, nome ou e-mail).
   - **Campo de busca textual por Usuário**: substituído o `<select>` por um `<input type="text">` pesquisável por nome ou e-mail, permitindo localizar usuários rapidamente mesmo em bases com centenas de contas.
   - Filtro por Perfil (`Administrador` / `Usuário Comum`).
   - Intervalo de datas com inputs nativos tipo `date`.
   - Botões `Filtrar` e `Limpar Filtros`.
3. **Tabela Cronológica**:
   - Colunas: ID, Data/Hora (com tempo relativo: *"há 10 min"*, *"ontem"*), Usuário (com badge de perfil e avatar) e Ação Realizada (com ícone temático conforme a categoria do evento: chave, banco, engrenagem, código, lixeira, etc.).

---

## 6. Sistema Global de Toasts e Notificações (Substituição de `alert()`)

### 6.1 Implementação em `app/views/include/head.php` e `public/assets/css/style.css`
Foi criado um motor JavaScript e CSS que substitui os alertas nativos por cards flutuantes responsivos:
- **Função**: `window.devstudioAlert(mensagem, tipo = 'info', titulo = null)`.
- **Tipos Suportados**:
  - `sucesso` (`#22c55e` - ícone `bi-check-circle-fill`)
  - `erro` (`#ef4444` - ícone `bi-exclamation-triangle-fill`)
  - `aviso` (`#f59e0b` - ícone `bi-exclamation-circle-fill`)
  - `info` (`var(--accent)` `#5b6af0` - ícone `bi-info-circle-fill`)
- **Comportamento**:
  - Posição fixa no topo direito (`top: 24px; right: 24px; z-index: 999999;`).
  - Animação de entrada e saída com blur de fundo (`backdrop-filter: blur(8px)`).
  - Botão manual de fechar e temporizador automático de 5 segundos.
  - Sobrescrita de `window.alert` que analisa o texto da mensagem e seleciona automaticamente o tipo de alerta adequado.

### 6.2 Chamadas Substituídas nos Scripts
- **`public/assets/js/mvcLoad.js`**:
  - Avisos de seleção de tabelas para gerar projeto.
  - Sucesso de empacotamento ZIP e avisos do gerador.
  - Erros de requisição e respostas inesperadas do servidor.
- **`app/views/usuarios/perfil.php`**:
  - Validação de limite de tamanho de imagem (máximo 2 MB).
  - Validação de formato de avatar (JPG, PNG, WEBP).

---

## 7. Padronizações no Perfil e Histórico

1. **Botão de Perfil na Topbar (`app/views/include/navigation.php`)**:
   - Corrigida a renderização condicional: exibe estritamente **um** elemento visual circular (ou a tag `<img>` do avatar carregado ou o ícone padrão `<i class="bi bi-person-fill">`), eliminando a exibição duplicada que ocorria para administradores.
2. **Remoção de Vermelho no Histórico (`app/views/projetos/historico.php`)**:
   - Os botões "Limpar Histórico" e "Excluir do Histórico" foram alterados de `.btn-danger` para `.btn-primary`, adotando o tom azul oficial `var(--accent)` (`#5b6af0` e hover `#4a59df`).
   - Adicionada regra CSS forçando que qualquer botão no contêiner do histórico utilize a paleta azul e preserve o padrão visual do sistema.
3. **Correção de Deformação de Botões (`responsivo.css` e `style.css`)**:
   - Removida a quebra indesejada `overflow-wrap: anywhere;`.
   - Aplicado `white-space: nowrap !important; word-break: keep-all !important;` em `.btn` e na barra de ações, impedindo que textos como *"Novo MVC Creator"* quebrem em duas linhas e desalinhem o cabeçalho.
4. **Remoção de Redundância no Histórico**:
   - Removida a caixa intermediária repetitiva *"Projetos salvos 2 de 10"*, mantendo exclusivamente o contador oficial da barra superior (`2 / 10 projetos`), preservando o aviso funcional de retenção (`.historico-aviso-limite`).
5. **Padronização Global de Cabeçalhos de Página (Header com Ícone Temático)**:
   - Inspirado no cabeçalho do **PHPMeuAmigo** (`<i class="bi bi-database"></i> PHPMeuAmigo`), todas as funções e telas do sistema foram unificadas no mesmo padrão visual (`.page-header` com `.page-title` em `Syne` 32px, negrito 700, borda inferior sutil e ícone em `var(--accent)`):
     - **MVC Creator**: `<i class="bi bi-braces"></i> MVC Creator`
     - **PageMaker**: `<i class="bi bi-file-earmark-code"></i> PageMaker`
     - **Histórico**: `<i class="bi bi-clock-history"></i> Histórico`
     - **PHPMeuAmigo**: `<i class="bi bi-database"></i> PHPMeuAmigo`
     - **Logs do Sistema**: `<i class="bi bi-shield-check"></i> Logs do Sistema`
     - **Usuários**: `<i class="bi bi-people"></i> Usuários`
     - **Criar Novo Usuário**: `<i class="bi bi-person-plus"></i> Criar Novo Usuário`
     - **Editar Usuário**: `<i class="bi bi-person-gear"></i> Editar Usuário`
     - **Perfil**: `<i class="bi bi-person-circle"></i> [Nome do Usuário]`
     - **Guia Rápido**: `<i class="bi bi-compass"></i> Guia Rápido`

### 7.6 Modal de Exclusão de Usuários e Tratamento de Erros (DevStudio Identity)
O diálogo nativo `confirm()` do navegador foi substituído por um modal exclusivo desenvolvido no padrão visual DevStudio:
1. **Estrutura do Modal (`#modalExcluirUsuario`)**:
   - Overlay translúcido com `backdrop-filter: blur(6px)`.
   - Card em `var(--panel)` com cantos arredondados (16px), sombra profunda e animação suave de abertura.
   - Cabeçalho com ícone temático de exclusão (`bi-person-x-fill`) em vermelho (`#ef4444`).
   - Card de identificação resumindo nome, e-mail, ID e perfil (`admin` ou `usuario`) da conta selecionada.
   - Bloco de aviso de perigo irreversível.
2. **Proteção Contra Auto-Exclusão**:
   - Identificação automática do usuário conectado através da sessão (`$usuarioLogadoId`).
   - Apresentação de badge `"Você"` ao lado do nome do usuário conectado na tabela.
   - Se o usuário tentar abrir a exclusão da sua própria conta, o modal exibe alerta amarelo bloqueando a ação e desativa o botão de confirmação.
   - Validação defensiva no backend (`UsuarioController::excluir`): bloqueia a tentativa e redireciona com mensagem de erro.
3. **Tratamento de Exceções e Integridade Referencial**:
   - Em caso de restrição de integridade referencial (`SQLSTATE[23000]` / `1451`) por existência de projetos gerados ou logs de auditoria vinculados, o erro SQL é tratado amigavelmente: `"Não é possível excluir o usuário '[Nome]' pois existem dados vinculados a ele no sistema"`.
   - Limpeza automática de arquivos de foto de perfil antigos através de `removerAvataresAntigos()`.
   - Registro de auditoria em `log` registrando a exclusão de usuários pelo Administrador.
   - Feedback visual via banner na página (`alert-danger` ou `alert-sucesso`) e disparo simultâneo de notificação flutuante via `window.devstudioAlert()`.

### 7.7 Modal de Confirmação de Ações Sensíveis no Perfil
O diálogo nativo `window.confirm()` exibido ao alterar a senha ou remover a foto de perfil foi substituído pelo modal `#modalConfirmarPerfil`:
1. **Identificação Dinâmica de Ações**:
   - Se o usuário preencher novos dados de senha: exibe item com ícone `bi-key-fill` alertando sobre a nova credencial.
   - Se o usuário solicitar remoção de foto: exibe item com ícone `bi-image` alertando sobre a exclusão da foto.
2. **Design e Acessibilidade**:
   - Overlay escuro translúcido com `backdrop-filter: blur(6px)`.
   - Card padronizado em `var(--panel)` com cabeçalho em *Syne* e ícone de aviso âmbar (`bi-shield-exclamation`).
   - Fechamento com `Esc`, clique no overlay ou no botão **Cancelar**; confirmação fluida com **Sim, Continuar** disparando a submissão do formulário.
3. **Alternadores de Visibilidade de Senha (`.perfil-btn-toggle-senha`)**:
   - Os 3 campos da seção de alteração de senha (*Senha atual*, *Nova senha* e *Confirmar nova senha*) receberam botões integrados para alternar a visibilidade entre `password` e `text`.
   - Utilizam os mesmos ícones (`bi-eye` e `bi-eye-slash`) e padrão de interação já adotados na tela de login e cadastro.

---

## 8. Segurança de Dados no PHPMeuAmigo e Preservação de Projetos

### 8.1 Exportação Segura de Scripts SQL (`nomeBanco_devstudio`)
A exportação de script SQL no PHPMeuAmigo foi totalmente reformulada para eliminar riscos de perda acidental de dados no ambiente do usuário:
1. **Remoção de `DROP TABLE`**: Foram eliminadas todas as cláusulas `DROP TABLE IF EXISTS`.
2. **Uso de `CREATE TABLE IF NOT EXISTS`**: A criação de tabelas é segura e não sobrescreve nem apaga dados já existentes.
3. **Nomenclatura Isolada `nomeBanco_devstudio`**: O script cria e utiliza exclusivamente o schema `nomeBanco_devstudio` (`CREATE SCHEMA IF NOT EXISTS nomeBanco_devstudio`), mantendo o banco de dados original do usuário 100% intocado.

### 8.2 Desvinculação Segura na Exclusão de Bancos
1. A coluna `fk_banco` na tabela `projeto` foi reconfigurada para aceitar `NULL` (`INT NULL DEFAULT NULL`).
2. A chave estrangeira `fk_projeto_banco` foi redefinida com `ON DELETE SET NULL ON UPDATE CASCADE`.
3. Ao excluir um banco de dados no PHPMeuAmigo, os projetos gerados **NÃO são mais excluídos**. Seus registros no banco e os arquivos `.zip` para download no Histórico permanecem permanentemente preservados.

---

## 9. Diagrama de Casos de Uso com a Nova Funcionalidade

```mermaid
flowchart LR
    subgraph Atores
        Adm["👤 Administrador"]
        User["👤 Usuário Comum"]
    end

    subgraph Modulo_Logs["Módulo de Auditoria e Logs (RF 15)"]
        UC15["UC 15 - Consultar Logs do Sistema"]
        UC15_Filtro["Filtrar Eventos por Data / Usuário / Termo"]
        UC15_Export["Exportar Histórico em CSV"]
    end

    subgraph Modulo_Usuarios["Módulo de Usuários (UC 16)"]
        UC16["UC 16 - Gerenciar Usuários"]
    end

    subgraph Eventos_Auditados["Eventos do Sistema"]
        EvLogin["Login / Logout"]
        EvCad["Cadastro de Usuários"]
        EvMVC["Geração MVC Creator"]
        EvPage["PageMaker (Salvar / Baixar)"]
        EvBanco["PHPMeuAmigo (Bancos / Tabelas / FKs)"]
        EvHist["Exclusões do Histórico"]
    end

    Adm --> UC15
    Adm --> UC16
    UC15 -.-> UC15_Filtro
    UC15 -.-> UC15_Export

    User -.->|Gera eventos| Eventos_Auditados
    Adm -.->|Gera eventos| Eventos_Auditados
    Eventos_Auditados -->|LogService::registrar()| UC15
```

---

## 10. Relação de Arquivos Criados e Modificados

| Arquivo | Status | Descrição |
| :--- | :--- | :--- |
| `app/models/Log.php` | **Criado** | Modelo da entidade Log com getters, setters e helpers de formatação. |
| `app/repositories/LogRepository.php` | **Criado** | Repositório PDO para consulta, filtros por busca textual e usuário, e inserção append-only. |
| `app/services/LogService.php` | **Criado** | Serviço de auditoria com método estático de registro e exportação CSV. |
| `app/controllers/LogController.php` | **Criado** | Controller administrativo restrito com ações de listagem e exportação CSV. |
| `app/views/logs/index.php` | **Criado** | View da tela de logs com layout limpo, campo de busca de usuário por texto e tabela cronológica. |
| `public/assets/css/logs.css` | **Criado** | Estilos dedicados da tela de logs, badges, avatares e estados vazios. |
| `public/index.php` | **Modificado** | Registro das rotas `/logs` e `/logs/exportar`. |
| `app/views/include/navigation.php` | **Modificado** | Inclusão do link de Logs na topbar e sidebar para admins; correção do avatar único. |
| `app/views/include/head.php` | **Modificado** | Inclusão do motor global de toasts `devstudioAlert`, sobrescrita de `alert()`, `logs.css` e ID no body. |
| `public/assets/css/style.css` | **Modificado** | Regras CSS dos toasts flutuantes, destaque da sidebar de logs e `white-space: nowrap` para botões. |
| `public/assets/css/responsivo.css` | **Modificado** | Correção de quebra de texto em botões da barra de ações. |
| `app/controllers/AutenticacaoController.php` | **Modificado** | Acoplamento de logs para login, logout e cadastro de novos usuários. |
| `app/controllers/UsuarioController.php` | **Modificado** | Acoplamento de logs para criação, atualização e exclusão de contas e perfil. |
| `app/controllers/ProjetoController.php` | **Modificado** | Acoplamento de logs para geração MVC Creator, PageMaker e operações de histórico. |
| `app/controllers/PhpMeuAmigoController.php` | **Modificado** | Acoplamento de logs para criação/edição/exclusão de bancos, tabelas, atributos, importações e exportações SQL. |
| `app/services/SqlExporterService.php` | **Modificado** | Exportação segura sem DROP, criação com IF NOT EXISTS e schema isolado `nomeBanco_devstudio`. |
| `app/repositories/BancoRepository.php` | **Modificado** | Desvinculação dos projetos (`fk_banco = NULL`) ao excluir banco, preservando projetos e arquivos para download. |
| `app/database/scripts/script.sql` | **Modificado** | Atualização do schema da tabela `projeto` com `fk_banco INT NULL` e `ON DELETE SET NULL`. |
| `app/services/UsuarioService.php` | **Modificado** | Ajuste da mensagem de validação de caracteres especiais sem termos de SQL Injection. |
| `app/views/projetos/historico.php` | **Modificado** | Substituição de botões vermelhos por azul DevStudio e remoção de contador duplicado. |
| `public/assets/js/mvcLoad.js` | **Modificado** | Substituição dos `alert()` nativos por `devstudioAlert(...)`. |
| `app/views/usuarios/perfil.php` | **Modificado** | Substituição dos `alert()` de imagem por `devstudioAlert(...)`. |
| `docs/guias/2026-11-05-auditoria-logs-rf15-e-padronizacao-visual.md` | **Criado** | Este guia técnico detalhado da implementação. |

---

## 11. Procedimentos de Verificação e Testes

1. **Acesso Administrativo ao Módulo**:
   - Realizar login com usuário administrador (`admin@devstudio.com` / `admin123`).
   - Confirmar a presença do item **Logs** na barra superior e **Consultar Logs** na barra lateral.
   - Navegar até `http://localhost:8081/logs` e verificar o carregamento dos cards de KPI e da tabela cronológica.
2. **Controle de Acesso (Segurança)**:
   - Acessar o sistema com usuário comum ou sem autenticação e tentar abrir `/logs`.
   - Confirmar o bloqueio com redirecionamento para o login (`adminRequired()`).
3. **Auditoria de Ações em Tempo Real**:
   - Gerar um projeto no MVC Creator, cadastrar um novo usuário ou atualizar o perfil.
   - Recarregar a tela `/logs` e constatar o novo evento registrado no topo da lista.
4. **Filtros e Exportação**:
   - Aplicar filtros por termo, por usuário ou intervalo de datas e clicar em **Filtrar**.
   - Clicar em **Exportar CSV** e verificar o download do arquivo com os registros correspondentes.
5. **Notificações**:
   - Tentar selecionar um arquivo maior que 2 MB no perfil e confirmar a exibição do toast customizado DevStudio no canto superior direito.
