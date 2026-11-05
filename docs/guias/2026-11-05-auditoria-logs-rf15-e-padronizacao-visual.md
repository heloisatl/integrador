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
- **`getAll(array $filtros, int $limite, int $offset)`**: Monta queries dinâmicas preparadas (`PDO::prepare`) suportando filtros por busca textual (`q`), ID do usuário (`usuario_id`), intervalo de datas (`data_inicio` a `data_fim`) e perfil (`tipo_perfil`), ordenando sempre cronologicamente de forma decrescente (`ORDER BY l.data DESC, l.id_log DESC`).
- **`contarTotal(array $filtros)`**: Retorna o quantitativo total de registros sob os mesmos critérios de filtro.
- **`getEstatisticas()`**: Agrega métricas para os cartões de indicadores (total de eventos, eventos de hoje, usuários distintos com ações e última ação registrada).
- **`getUsuariosParaFiltro()`**: Recupera a lista de usuários com atividade para popular dinamicamente o campo `<select>` de filtro.
- **`limparHistoricoLogs()`**: Trunca a tabela `log` para fins de manutenção por administradores.
- **`garantirTabela()`**: Mecanismo de auto-cura (`CREATE TABLE IF NOT EXISTS`) para garantir que o sistema não falhe mesmo se o banco for recriado em novo ambiente.

### 4.3 Camada de Serviço (`app/services/LogService.php`)
- **`LogService::registrar(string $acao, ?int $fkUsuario = null): bool`**: Método estático e desacoplado que pode ser chamado de qualquer controller, service ou ferramenta do sistema. Se o ID do usuário não for explicitado, recupera automaticamente da sessão ativa (`$_SESSION['usuario_logado']`). Em eventos anônimos de sistema, vincula ao administrador padrão (ID 1). Falhas de log são capturadas internamente para nunca interromper a regra de negócio principal.
- **`exportarCsv(array $filtros)`**: Gera e transmite para o cliente um arquivo `.csv` codificado com **BOM UTF-8** (`\xEF\xBB\xBF`) e delimitador ponto-e-vírgula (`;`), garantindo abertura perfeita em planilhas como Microsoft Excel e LibreOffice Calc sem caracteres corrompidos.
- **`popularLogsIniciaisSeVazio()`**: Popula eventos básicos de inicialização (bootstrap) caso a tabela esteja totalmente vazia, assegurando que o administrador visualize uma interface rica desde o primeiro acesso.

### 4.4 Controlador e Rotas (`app/controllers/LogController.php` & `public/index.php`)
- Rotas registradas:
  - `GET /logs`: Executa `LogController@index`, validando `adminRequired()` e renderizando a interface.
  - `GET /logs/exportar`: Executa `LogController@exportar`, disparando o download do arquivo CSV.
  - `GET /logs/limpar`: Executa `LogController@limpar`, esvaziando o histórico após confirmação.

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
- **`ProjetoController.php`**:
  - `gerarMvc()`: *"Geração de sistema MVC Creator: {nomeProjeto}"*
  - `apagarProjetoHistorico()`: *"Projeto removido do histórico (ID: {id})"*
  - `limparHistorico()`: *"Histórico de projetos foi limpo pelo usuário."*

---

## 5. Interface Visual e Estilização (`app/views/logs/index.php` & `logs.css`)

A interface foi projetada seguindo rigorosamente o design system do DevStudio:
1. **Cabeçalho & Ações**:
   - Tag de identificação: `AUDITORIA & SEGURANÇA (RF 15 / UC 15)`.
   - Botões de exportação CSV, gerenciamento de usuários e limpeza rápida.
2. **Dashboard de KPIs**:
   - **Total de Logs**: volume total registrado.
   - **Registros Hoje**: ações contabilizadas no dia corrente.
   - **Usuários Ativos**: número de contas distintas com ações auditadas.
   - **Último Evento**: prévia da última operação e seu horário.
3. **Barra de Filtros**:
   - Busca instantânea por termo/ação/nome/e-mail.
   - Filtro por Usuário e Perfil (`Administrador` / `Usuário Comum`).
   - Intervalo de datas com inputs tipo `date`.
   - Botões `Filtrar` e `Limpar`.
4. **Tabela Cronológica**:
   - Colunas: ID, Data/Hora (com tempo relativo: *"há 10 min"*, *"ontem"*), Usuário (com badge de perfil e avatar) e Ação Realizada (com ícone temático conforme a categoria do evento: chave, banco, engrenagem, lixeira, etc.).

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

---

## 8. Diagrama de Casos de Uso com a Nova Funcionalidade

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
        UC15_Limpar["Limpar Registros Antigos"]
    end

    subgraph Modulo_Usuarios["Módulo de Usuários (UC 16)"]
        UC16["UC 16 - Gerenciar Usuários"]
    end

    subgraph Eventos_Auditados["Eventos do Sistema"]
        EvLogin["Login / Logout"]
        EvCad["Cadastro de Usuários"]
        EvMVC["Geração MVC Creator"]
        EvHist["Exclusões do Histórico"]
    end

    Adm --> UC15
    Adm --> UC16
    UC15 -.-> UC15_Filtro
    UC15 -.-> UC15_Export
    UC15 -.-> UC15_Limpar

    User -.->|Gera eventos| Eventos_Auditados
    Adm -.->|Gera eventos| Eventos_Auditados
    Eventos_Auditados -->|LogService::registrar()| UC15
```

---

## 9. Relação de Arquivos Criados e Modificados

| Arquivo | Status | Descrição |
| :--- | :--- | :--- |
| `app/models/Log.php` | **Criado** | Modelo da entidade Log com getters, setters e helpers de formatação. |
| `app/repositories/LogRepository.php` | **Criado** | Repositório PDO para consulta, filtros, inserção e agregação de logs. |
| `app/services/LogService.php` | **Criado** | Serviço de auditoria com método estático de registro e exportação CSV. |
| `app/controllers/LogController.php` | **Criado** | Controller administrativo restrito com ações de listagem, exportação e limpeza. |
| `app/views/logs/index.php` | **Criado** | View da tela de logs com cards de KPI, formulário de filtros e tabela cronológica. |
| `public/assets/css/logs.css` | **Criado** | Estilos dedicados da tela de logs, badges, avatares e estados vazios. |
| `public/index.php` | **Modificado** | Registro das rotas `/logs`, `/logs/exportar` e `/logs/limpar`. |
| `app/views/include/navigation.php` | **Modificado** | Inclusão do link de Logs na topbar e sidebar para admins; correção do avatar único. |
| `app/views/include/head.php` | **Modificado** | Inclusão do motor global de toasts `devstudioAlert`, sobrescrita de `alert()`, `logs.css` e ID no body. |
| `public/assets/css/style.css` | **Modificado** | Regras CSS dos toasts flutuantes, destaque da sidebar de logs e `white-space: nowrap` para botões. |
| `public/assets/css/responsivo.css` | **Modificado** | Correção de quebra de texto em botões da barra de ações. |
| `app/controllers/AutenticacaoController.php` | **Modificado** | Acoplamento de logs para login, logout e cadastro de novos usuários. |
| `app/controllers/UsuarioController.php` | **Modificado** | Acoplamento de logs para criação, atualização e exclusão de contas e perfil. |
| `app/controllers/ProjetoController.php` | **Modificado** | Acoplamento de logs para geração MVC Creator e operações de histórico. |
| `app/services/UsuarioService.php` | **Modificado** | Ajuste da mensagem de validação de caracteres especiais sem termos de SQL Injection. |
| `app/views/projetos/historico.php` | **Modificado** | Substituição de botões vermelhos por azul DevStudio e remoção de contador duplicado. |
| `public/assets/js/mvcLoad.js` | **Modificado** | Substituição dos `alert()` nativos por `devstudioAlert(...)`. |
| `app/views/usuarios/perfil.php` | **Modificado** | Substituição dos `alert()` de imagem por `devstudioAlert(...)`. |
| `docs/guias/2026-11-05-auditoria-logs-rf15-e-padronizacao-visual.md` | **Criado** | Este guia técnico detalhado da implementação. |

---

## 10. Procedimentos de Verificação e Testes

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
