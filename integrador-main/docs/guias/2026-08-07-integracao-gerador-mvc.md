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

1. **Camada de Apresentação (Interface, AJAX & SessionStorage)**:
   - Form em 5 passos: `app/views/projetos/mvcCreator.php`
   - Scripts de manipulação, chamadas AJAX e persistência de dados de sessão: `public/assets/js/mvcLoad.js`
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

## 3. Detalhamento Técnico das Classes e Isolação de Ambientes

### 3.1 Isolação e Limpeza Automática (`public/temp/` vs `public/downloads/`)
- A compilação dos arquivos ocorre em uma pasta temporária isolada: `public/temp/{nomeProjeto}/app/`.
- O empacotador `GeradorZip` gera o arquivo `.ZIP` final na pasta **`public/downloads/{nomeProjeto}.zip`**.
- **Limpeza Automática de Disco**: Imediatamente após a conclusão da criação do `.ZIP`, o método privado `excluirDiretorioRecursivo($pastaOutput)` em `ProjetoController.php` **apaga automaticamente** a pasta `public/temp/{nomeProjeto}/`.
- Desta forma, o servidor não acumula arquivos temporários soltos e armazena exclusivamente os arquivos `.ZIP` na pasta `public/downloads/`.

### 3.2 Persistência via `sessionStorage` no Cliente (`public/assets/js/mvcLoad.js`)
Para evitar perda de dados durante a navegação nos 5 passos do formulário com redirecionamentos `?step=...`, os dados do formulário e o resultado de `SHOW TABLES` são mantidos em `sessionStorage` (`mvc_tabelas`, `mvc_banco`, etc.).

### 3.3 Módulos Geradores em `app/tools/gerador/`
- **`GeradorModel`**: Gera `namespace app\models;` com getters/setters.
- **`GeradorRepositorio`**: Gera repositórios PDO apontando para `ConnectionFactory`.
- **`GeradorController`**: Gera Controllers RESTful estendendo `app\core\Controller`.
- **`GeradorView`**: Gera views `index.php`, `create.php` e `edit.php` em subpastas em `app/views/{tabela}s/`.
- **`GeradorZip`**: Empacota o projeto compilado em `.zip` para envio via HTTP `readfile()`.

---

## 4. Diagrama do Fluxo de Execução End-to-End

```
[ Passo 1: mvcCreator.php ]
             │
             │ 1. Seleciona banco e clica "Conectar e Detectar Tabelas"
             ▼
[ AJAX: /projetos/getTabelas ] ──► Salva res.tabelas no sessionStorage ──► Redireciona ?step=tabelas
                                                                                   │
[ Passo 2: Tabelas Detectadas ] ◄── DOMContentLoaded lê sessionStorage ────────────┘
             │
             │ 2. Seleciona tabelas e avança até Passo 5
             ▼
[ Passo 5: Gerar Sistema ]     ──► [ AJAX: /projetos/gerarMvc ]
                                               │
                                               ├──► Compila em public/temp/{nomeProjeto}/
                                               ├──► [ GeradorZip::compactarPasta() ] ──► Salva .ZIP em public/downloads/
                                               └──► Apaga public/temp/{nomeProjeto}/
                                                        │
                                                        ▼
[ Download Automático .ZIP ]     ◄── [ ProjetoController::downloadZip() ]
```
