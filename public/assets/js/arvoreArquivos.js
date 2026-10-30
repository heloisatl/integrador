// Utilitários puros para desenhar a árvore de arquivos (usados na pré-visualização e na aba Saída).
// Recebem a lista de caminhos relativos devolvida pelo back-end (ex.: "app/models/Aluno.php").

// Converte ["app/a.php", "app/b/c.php"] em { app: { "a.php": null, b: { "c.php": null } } }.
// Valor `null` = arquivo; objeto = pasta.
function construirArvoreDeCaminhos(caminhos) {
    const raiz = {};
    caminhos.forEach(caminho => {
        const partes = String(caminho).split('/');
        let no = raiz;
        partes.forEach((parte, i) => {
            if (i === partes.length - 1) {
                no[parte] = null;
            } else {
                if (!no[parte]) no[parte] = {};
                no = no[parte];
            }
        });
    });
    return raiz;
}

// Pastas primeiro, depois arquivos; ambos em ordem alfabética.
function ordenarNos(nos) {
    return Object.keys(nos).sort((a, b) => {
        const pastaA = nos[a] !== null, pastaB = nos[b] !== null;
        if (pastaA !== pastaB) return pastaA ? -1 : 1;
        return a.localeCompare(b);
    });
}

function desenharArvore(nos, prefixo, linhas, raiz = false) {
    const nomes = ordenarNos(nos);
    nomes.forEach((nome, i) => {
        const ultimo = i === nomes.length - 1;
        const filho = nos[nome];
        const conector = raiz ? '' : (ultimo ? '└── ' : '├── ');
        linhas.push(prefixo + conector + (filho ? '📂 ' + nome + '/' : '📄 ' + nome));
        if (filho) {
            desenharArvore(filho, raiz ? '' : prefixo + (ultimo ? '    ' : '│   '), linhas);
        }
    });
}

// Retorna o texto da árvore pronto para ser colocado com textContent (evita interpretar nomes como HTML).
function arvoreComoTexto(caminhos) {
    const linhas = [];
    desenharArvore(construirArvoreDeCaminhos(caminhos), '', linhas, true);
    return linhas.join('\n');
}
