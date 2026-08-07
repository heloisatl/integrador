# Guia Técnico de Arquitetura e Implementação - Gerador MVC & Exporter ZIP
**Data da Alteração**: `2026-08-07`  
**Título do Guia**: `2026-08-07-integracao-gerador-mvc.md`  
**Módulo**: Ferramentas de Desenvolvimento / Gerador de Projetos MVC  
**Público-Alvo**: Desenvolvedores de Software, Arquitetos de Soluções e Mantenedores do Código  

---

## 1. Contexto e Motivação da Refatoração

Anteriormente, o gerador de código MVC residia de forma isolada na pasta `projeto itamar/`, estruturado em um único arquivo monolítico de 531 linhas (`creator.php`). Essa abordagem apresentava diversos problemas técnicos:
- **Acoplamento Extremo**: Conexão de banco, inspeção de tabelas, templates de código e saída HTML em um único arquivo procedural.
- **Incompatibilidade de Nomenclatura**: Gerava arquivos em `sistema/model`, `sistema/control`, `sistema/dao` sem namespaces PHP, incompatível com o padrão PSR-4 do projeto principal (`app/models`, `app/controllers`, `app/repositories`).
- **Segurança e Estilização Legada**: Estilos CSS isolados e sem integração com as folhas de estilo globais do sistema (`head.php`, `pagina-mvc.css`).

A refatoração transformou o módulo em um conjunto de **classes especialistas e modulares em português**, totalmente integradas à arquitetura MVC do projeto `integrador`.

---

## 2. Visão Geral da Arquitetura

O sistema do Gerador MVC é composto por 3 camadas principais:

1. **Camada de Apresentação (Interface & AJAX)**:
   - Form em 5 passos: `app/views/projetos/mvcCreator.php`
   - Scripts de manipulação e requisições AJAX: `public/assets/js/mvcLoad.js`
   - Estilização unificada: `public/assets/css/pagina-mvc.css` e `head.php`

2. **Camada de Controle e Endpoints (`app/controllers/ProjetoController.php`)**:
   - Rotas registradas em `public/index.php`:
     - `POST /projetos/getDatabases`: Lista os bancos do servidor MySQL.
     - `POST /projetos/getTabelas`: Retorna a lista de tabelas de um banco selecionado.
     - `POST /projetos/gerarMvc`: Dispara a geração dos arquivos e cria a estrutura `.zip`.
     - `GET /projetos/downloadZip`: Transmite o arquivo `.zip` gerado para o navegador do cliente.

3. **Camada de Geradores Especialistas (`app/tools/gerador/`)**:
   - `GeradorModel.php`: Responsável por compilar as classes de entidade.
   - `GeradorRepositorio.php`: Responsável por compilar o acesso a dados PDO.
   - `GeradorController.php`: Responsável por compilar os Controllers RESTful.
   - `GeradorView.php`: Responsável por compilar as telas de listagem, cadastro e edição.
   - `GerenciadorGerador.php`: Orquestrador central (Facade).
   - `GeradorZip.php`: Módulo de empacotamento recursivo em `.zip` e transmissão HTTP.

---

## 3. Detalhamento Técnico das Classes Criadas

### 3.1 `app\tools\gerador\GeradorModel`
- **Função**: Compilar o código PHP da classe de modelo da entidade com propriedades privadas, getters e setters.
- **Namespace Gerado**: `namespace app\models;`
- **Assinatura dos Métodos**:
  ```php
  public function gerarModel(string $nomeTabela, array $atributos): string
  public function salvarModel(string $nomeTabela, array $atributos, string $caminhoBase = __DIR__ . '/../../models'): string
  ```
- **Padrão de Saída**: Arquivo `app/models/{NomeTabelaUCFirst}.php`.

### 3.2 `app\tools\gerador\GeradorRepositorio`
- **Função**: Compilar a camada de persistência de dados baseada em PDO usando `app\database\ConnectionFactory::getConnection()`.
- **Namespace Gerado**: `namespace app\repositories;`
- **Métodos Gerados na Classe Destino**:
  - `inserir($obj): bool`
  - `listarTodos(): array`
  - `buscarPorId(int $id): ?array`
  - `alterar($obj, int $id): bool`
  - `excluir(int $id): bool`
- **Assinatura dos Métodos**:
  ```php
  public function gerarRepositorio(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string
  public function salvarRepositorio(string $nomeTabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../repositories'): string
  ```

### 3.3 `app\tools\gerador\GeradorController`
- **Função**: Compilar a camada de controlador do MVC estendendo a classe base `app\core\Controller`.
- **Namespace Gerado**: `namespace app\controllers;`
- **Métodos Gerados na Classe Destino**:
  - `index()`: Carrega a view de listagem com os registros.
  - `cadastrar()`: Renderiza o formulário de cadastro.
  - `salvar()`: Processa os campos recebidos via `$_POST` e insere no repositório.
  - `editar()`: Carrega os dados da entidade e renderiza a view de edição.
  - `atualizar()`: Processa a edição via `$_POST`.
  - `excluir()`: Remove o registro informando o ID via `$_GET['id']`.
- **Assinatura dos Métodos**:
  ```php
  public function gerarController(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string
  public function salvarController(string $nomeTabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../controllers'): string
  ```

### 3.4 `app\tools\gerador\GeradorView`
- **Função**: Compilar os arquivos de interface de usuário em HTML/PHP.
- **Estrutura Gerada**:
  - `app/views/{nometabelas}/index.php` (Tabela responsiva de listagem com botões Editar/Excluir).
  - `app/views/{nometabelas}/create.php` (Formulário de inclusão).
  - `app/views/{nometabelas}/edit.php` (Formulário de alteração preenchido).
- **Assinatura dos Métodos**:
  ```php
  public function gerarIndexView(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string
  public function gerarCreateView(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string
  public function gerarEditView(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string
  public function salvarViews(string $nomeTabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../views'): array
  ```

### 3.5 `app\tools\gerador\GerenciadorGerador`
- **Função**: Atuar como fachada para invocar a geração completa em uma única chamada.
- **Assinatura do Método Principal**:
  ```php
  public function gerarTudo(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): array
  ```

### 3.6 `app\tools\gerador\GeradorZip`
- **Função**: Tratar do empacotamento em formato `.zip` e transmissão de dados HTTP ao cliente.
- **Assinatura dos Métodos**:
  ```php
  public function compactarPasta(string $pastaOrigem, string $arquivoZipDestino): bool
  public function enviarDownload(string $caminhoZip, string $nomeArquivoDownload = 'projeto_mvc.zip'): void
  ```
- **Cabeçalhos HTTP Utilizados**:
  ```php
  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="..."');
  header('Content-Length: ' . filesize($caminhoZip));
  ```

---

## 4. Diagrama do Fluxo de Execução End-to-End

```
[ Usuário na View mvcCreator.php ]
             │
             │ 1. Preenche senha e desfoca o campo
             ▼
[ AJAX: /projetos/getDatabases ] ──► [ ProjetoController::getDatabases() ] ──► Retorna <option> dos bancos
             │
             │ 2. Seleciona banco e clica em "Detectar Tabelas"
             ▼
[ AJAX: /projetos/getTabelas ]   ──► [ ProjetoController::getTabelas() ]   ──► Retorna JSON com nomes das tabelas
             │
             │ 3. Avança pelos passos de seleção de opções e visualização da estrutura
             ▼
[ AJAX: /projetos/gerarMvc ]     ──► [ ProjetoController::gerarMvc() ]
                                               │
                                               ├──► [ GerenciadorGerador::gerarTudo() ]
                                               │        ├── GeradorModel
                                               │        ├── GeradorRepositorio
                                               │        ├── GeradorController
                                               │        └── GeradorView
                                               │
                                               └──► [ GeradorZip::compactarPasta() ]
                                                        │
                                                        ▼
[ Download Automático .ZIP ]     ◄── [ ProjetoController::downloadZip() ]
```

---

## 5. Instruções para Mantenedores e Futuras Extensões

1. **Alterar Templates de Código Gerados**:
   - Se for necessário adicionar novos métodos padrão em todos os controllers gerados, edite a string heredoc em `app/tools/gerador/GeradorController.php`.
   - Se o projeto passar a usar um ORM ou Query Builder, ajuste a geração SQL dentro de `app/tools/gerador/GeradorRepositorio.php`.

2. **Diretórios de Download e Limpeza de Arquivos Temporários**:
   - Os arquivos compactados são armazenados em `public/downloads/` e os fontes compilados temporários em `public/temp/`.
   - Recomenda-se configurar uma rotina de cron/script para deletar arquivos em `public/downloads/` com mais de 24 horas de criação para liberar espaço em disco.

3. **Verificação de Segurança**:
   - A chamada `downloadZip` utiliza `basename()` na variável `$_GET['file']` para evitar ataques de *Directory Traversal* (`../`). Mantenha essa higienização em qualquer novo endpoint de download.
