<?php include_once(__DIR__ . "/../include/head.php"); ?>

<div class="topbar">
  <div class="topbar-left">
    <span class="logo">DevStudio</span>
    <span class="logo-version">v2</span>
  </div>

  <div class="topbar-center">
    <!-- gotoPage é do JS, achei mais personalizável doq pegar direto do bootstrap -->
    <button class="tab active" onclick="gotoPage('home.php')">🏠 Início</button>
    <button class="tab" onclick="gotoPage('configGlobal.php')">⚙ Config Global</button>
    <button class="tab" onclick="gotoPage('mvcCreator.php')">⚡ MVC Creator</button>
    <button class="tab" onclick="gotoPage('pageMaker.php')">🎨 Page Maker</button>
    <button class="tab" onclick="gotoPage('historico.php')">📁 Histórico</button>
  </div>

  <div class="topbar-right">
    <button onclick="alternarTema()">🌓</button>
  </div>
</div>