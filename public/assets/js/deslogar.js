

globalThis.URL_BASE = new URL("../..", document.currentScript.src).href.replace(/\/$/, "");



function desfazSessionStorage(){
    sessionStorage.removeItem("mvc_nomeProjeto");
    sessionStorage.removeItem("mvc_servidor");
    sessionStorage.removeItem("mvc_usuario");
    sessionStorage.removeItem("mvc_senha");
    sessionStorage.removeItem("mvc_banco");
}
