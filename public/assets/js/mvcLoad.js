function carregarBanco() {
    const URL_BASE = "http://localhost:8081";
    let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
    let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
    let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";

    const data = new FormData();
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getDatabases', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let elBanco = document.getElementById("banco");
            if (elBanco) {
                elBanco.innerHTML = xhr.responseText;
            }
        }
    };
    xhr.send(data);
}

function carregarTabelas() {
    const URL_BASE = "http://localhost:8081";
    let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
    let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
    let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";
    let banco = document.getElementById("banco") ? document.getElementById("banco").value : "";

    if (!banco) {
        alert("Por favor, selecione um banco de dados antes de continuar.");
        return;
    }

    const data = new FormData();
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);
    data.append('banco', banco);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getTabelas', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    let container = document.getElementById("container-tabelas");
                    if (container) {
                        let html = '<div style="display: flex; flex-direction: column; gap: 8px; margin: 15px 0;">';
                        res.tabelas.forEach(t => {
                            html += `<label style="display: flex; align-items: center; gap: 10px; font-size: 15px;">
                                <input type="checkbox" name="tabelas[]" value="${t}" checked> <span>📊 ${t}</span>
                            </label>`;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    }
                    window.location.href = '?step=tabelas';
                } else {
                    alert("Erro ao buscar tabelas: " + res.mensagem);
                }
            } catch (e) {
                console.error("Erro no parse JSON", e);
            }
        }
    };
    xhr.send(data);
}

function executarGeracaoMvc() {
    const URL_BASE = "http://localhost:8081";
    let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
    let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
    let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";
    let banco = document.getElementById("banco") ? document.getElementById("banco").value : "";
    let nomeProjeto = document.getElementById("nomeProjeto") ? document.getElementById("nomeProjeto").value : "meu_projeto";

    let checkboxes = document.querySelectorAll('input[name="tabelas[]"]:checked');
    let tabelas = Array.from(checkboxes).map(cb => cb.value);

    if (tabelas.length === 0) {
        alert("Selecione ao menos uma tabela para gerar o projeto.");
        return;
    }

    const data = new FormData();
    data.append('nomeProjeto', nomeProjeto);
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);
    data.append('banco', banco);
    tabelas.forEach(t => data.append('tabelas[]', t));

    let btn = document.getElementById("btn-gerar-final");
    if (btn) btn.innerText = "⏳ Gerando projeto...";

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/gerarMvc', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    alert("✅ " + res.mensagem);
                    if (res.downloadUrl) {
                        window.location.href = res.downloadUrl;
                    }
                } else {
                    alert("❌ Erro ao gerar: " + res.mensagem);
                }
            } catch (e) {
                alert("Resposta inesperada do servidor.");
            }
            if (btn) btn.innerText = "🚀 Gerar Todo o Sistema (.ZIP)";
        }
    };
    xhr.send(data);
}