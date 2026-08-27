function alternarTema() {
  const html = document.documentElement;

  const current = html.getAttribute("modo-claro");

  if (current === "true") {
    html.setAttribute("modo-claro", "false");
    localStorage.setItem("theme", "dark");
  } else {
    html.setAttribute("modo-claro", "true");
    localStorage.setItem("theme", "light");
  }
}

/* carregar mudança */
(function () {
  const saved = localStorage.getItem("theme");

  if (saved === "light") {
    document.documentElement.setAttribute("modo-claro", "true");
  }
})();