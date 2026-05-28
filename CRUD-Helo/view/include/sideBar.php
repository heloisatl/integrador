<?php include_once(__DIR__ . "/../include/head.php"); ?>

<div class="sidebar">

  <div class="sb-section">
    <div class="sb-label">Menu</div>

    <button class="sb-item active" onclick="gotoPage('home.php')">
      🏠 Visão Geral
    </button>

    <button class="sb-item" onclick="gotoPage('configGlobal.php')">
      ⚙ Config. Globais
    </button>

    <button class="sb-item" onclick="gotoPage('guia.php')">
      ❓ Guia Rápido
    </button>
  </div>


    <button class="sb-item" onclick="gotoPage('mvcCreator.php')">
      ⚡ MVC Creator
    </button>

    <button class="sb-item" onclick="gotoPage('pageMaker.php')">
      🎨 Page Maker
    </button>
  </div>

</div>