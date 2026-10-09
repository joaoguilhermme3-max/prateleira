<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/visao.php';
$planos = db()->query('SELECT id, nome, preco_centavos, limite_produtos FROM planos WHERE ativo = 1 ORDER BY preco_centavos')->fetchAll();
topo('Sua loja online', usuario());
?>
<section class="hero">
  <h1>Sua loja online, seu estoque e suas vendas num só lugar.</h1>
  <p>Crie a conta, escolha um plano e comece a vender. Controle de estoque, pedidos e faturamento sem planilha.</p>
  <a class="btn" href="<?= e(url('cadastro.php')) ?>">Criar minha loja</a>
</section>
<h2 class="titulo">Planos</h2>
<div class="planos">
<?php foreach ($planos as $p): ?>
  <article class="plano">
    <h3><?= e($p['nome']) ?></h3>
    <p class="preco"><?= e(brl((int)$p['preco_centavos'])) ?><small>/mês</small></p>
    <p>Até <?= (int)$p['limite_produtos'] ?> produtos</p>
    <a class="btn" href="<?= e(url('cadastro.php?plano=' . (int)$p['id'])) ?>">Escolher</a>
  </article>
<?php endforeach; ?>
</div>
<?php rodape();
