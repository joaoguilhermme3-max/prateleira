<?php
function topo(string $titulo, ?array $u = null): void { ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> | Prateleira</title>
<link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
</head>
<body>
<header><div class="topo">
  <a class="logo" href="<?= e(url()) ?>">Prate<b>leira</b></a>
  <nav>
  <?php if ($u): ?>
    <a href="<?= e(url('painel/')) ?>">Painel</a>
    <form method="post" action="<?= e(url('sair.php')) ?>"><?= csrf_campo() ?><button class="link">Sair</button></form>
  <?php else: ?>
    <a href="<?= e(url('login.php')) ?>">Entrar</a>
    <a class="btn" href="<?= e(url('cadastro.php')) ?>">Criar minha loja</a>
  <?php endif; ?>
  </nav>
</div></header>
<main>
<?php if ($m = flash()): ?><p class="aviso" role="status"><?= e($m) ?></p><?php endif; ?>
<?php }

function rodape(): void { ?>
</main>
<footer>Prateleira · loja online, estoque e vendas num só lugar</footer>
</body>
</html>
<?php }
