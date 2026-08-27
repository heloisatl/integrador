document.addEventListener("DOMContentLoaded", function () {
    restaurarValoresFormulario();
    renderizarTabelasSeExistirem();
    
    // Controla a liberação/bloqueio do campo de Banco de Dados
    validarCampoProjeto();

    let inputProjeto = document.getElementById("nomeProjeto");
    if (inputProjeto) {
        inputProjeto.addEventListener("input", function () {
            sessionStorage.setItem("mvc_nomeProjeto", this.value);
            validarCampoProjeto(); // Reavalia toda vez que o usuário digita
        });
    }
});

// Função responsável por liberar ou bloquear a área do Banco
function validarCampoProjeto() {
    let inputProjeto = document.getElementById("nomeProjeto");
    let selectBanco = document.getElementById("banco");
    let btnCriar = document.getElementById("btnCriarBanco");
    let btnRefresh = document.getElementById("btnRefreshBanco");

    if (!inputProjeto || !selectBanco) return;

    let temNome = inputProjeto.value.trim().length > 0;

    if (temNome) {
        // Se tem nome, libera os campos
        selectBanco.disabled = false;
        if (btnCriar) btnCriar.disabled = false;
        if (btnRefresh) btnRefresh.disabled = false;
        
        // Se o select ainda está com a mensagem padrão de bloqueio, carrega os bancos
        if (selectBanco.options[0] && selectBanco.options[0].value === "") {
            carregarBanco();
        }
    } else {
        // Se não tem nome, bloqueia tudo
        selectBanco.disabled = true;
        selectBanco.innerHTML = '<option value="">Informe o nome do projeto para selecionar o banco</option>';
        if (btnCriar) btnCriar.disabled = true;
        if (btnRefresh) btnRefresh.disabled = true;
    }
}

// Sua função carregarBanco() com a trava de segurança adicional
function carregarBanco() {
    let inputProjeto = document.getElementById("nomeProjeto");
    let nomeProjeto = inputProjeto ? inputProjeto.value.trim() : "";

    // Trava de segurança extra caso tentem disparar a função manualmente
    if (!nomeProjeto) {
        alert("Por favor, informe o nome do projeto antes de carregar ou selecionar o banco.");
        if (inputProjeto) inputProjeto.focus();
        return;
    }

    const URL_BASE = "http://localhost:8081";
    salvarConfiguracoesSession();

    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";

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
                if (sessionStorage.getItem("mvc_banco")) {
                    elBanco.value = sessionStorage.getItem("mvc_banco");
                }
            }
        }
    };
    xhr.send(data);
}