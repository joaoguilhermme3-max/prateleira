<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/visao.php';
$u = exigir_login();
$lojaId = (int)$u['loja_id'];

$s = db()->prepare('SELECT a.status, a.vence_em, p.nome, p.preco_centavos, p.limite_produtos
                    FROM assinaturas a JOIN planos p ON p.id = a.plano_id
                    WHERE a.loja_id = ? ORDER BY a.id DESC LIMIT 1');
$s->execute([$lojaId]);
$ass = $s->fetch();
$ativa = assinatura_ativa($lojaId);

$s = db()->prepare('SELECT COUNT(*) FROM produtos WHERE loja_id = ? AND ativo = 1'); $s->execute([$lojaId]); $nProdutos = (int)$s->fetchColumn();
$s = db()->prepare("SELECT COALESCE(SUM(total_centavos), 0) FROM pedidos WHERE loja_id = ? AND status = 'pago'"); $s->execute([$lojaId]); $faturado = (int)$s->fetchColumn();

topo('Painel', $u);
?>
<h1>Olá, <?= e($u['nome']) ?></h1>
<p class="nota">Loja: <b><?= e($u['loja_nome']) ?></b> · endereço da vitrine: <code>/loja/<?= e($u['slug']) ?></code></p>

<?php if (!$ativa): ?>
  <div class="alerta">
    <b>Sua loja está bloqueada.</b>
    Plano <?= e($ass['nome'] ?? '—') ?> com pagamento pendente. Assim que o pagamento for confirmado, a vitrine, o estoque e as vendas são liberados.
    <!-- Etapa 4: botão "Pagar assinatura" (PIX/cartão no gateway) + webhook que muda assinaturas.status para 'ativa' -->
  </div>
<?php endif; ?>

<div class="metricas">
  <div class="met"><span>Assinatura</span><strong><?= $ativa ? 'Ativa até ' . e(date('d/m/Y', strtotime((string)$ass['vence_em']))) : 'Pendente' ?></strong></div>
  <div class="met"><span>Produtos</span><strong><?= $nProdutos ?> / <?= (int)($ass['limite_produtos'] ?? 0) ?></strong></div>
  <div class="met"><span>Faturamento (pedidos pagos)</span><strong><?= e(brl($faturado)) ?></strong></div>
</div>
<?php rodape();
