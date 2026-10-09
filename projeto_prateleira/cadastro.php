<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/visao.php';
if (usuario()) redirecionar('painel/');

$planos = db()->query('SELECT id, nome, preco_centavos FROM planos WHERE ativo = 1 ORDER BY preco_centavos')->fetchAll();
$erro = '';
$v = ['loja' => '', 'nome' => '', 'email' => '', 'plano' => (int)($_GET['plano'] ?? 0)];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
    } else {
        $v = ['loja' => trim($_POST['loja'] ?? ''), 'nome' => trim($_POST['nome'] ?? ''),
              'email' => strtolower(trim($_POST['email'] ?? '')), 'plano' => (int)($_POST['plano'] ?? 0)];
        $senha = (string)($_POST['senha'] ?? '');
        if (mb_strlen($v['loja']) < 3 || mb_strlen($v['loja']) > 80) $erro = 'Informe o nome da loja (3 a 80 caracteres).';
        elseif (mb_strlen($v['nome']) < 2) $erro = 'Informe o seu nome.';
        elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $erro = 'E-mail inválido.';
        elseif (strlen($senha) < 8) $erro = 'A senha precisa ter pelo menos 8 caracteres.';
        elseif (!in_array($v['plano'], array_map('intval', array_column($planos, 'id')), true)) $erro = 'Escolha um plano.';
        else {
            $pdo = db();
            $q = $pdo->prepare('SELECT 1 FROM usuarios WHERE email = ?');
            $q->execute([$v['email']]);
            if ($q->fetchColumn()) {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $base = slugify($v['loja']); $slug = $base; $n = 1;
                    $q = $pdo->prepare('SELECT 1 FROM lojas WHERE slug = ?');
                    while (true) { $q->execute([$slug]); if (!$q->fetchColumn()) break; $slug = $base . '-' . (++$n); }
                    $pdo->prepare('INSERT INTO lojas (nome, slug) VALUES (?, ?)')->execute([$v['loja'], $slug]);
                    $lojaId = (int)$pdo->lastInsertId();
                    $pdo->prepare('INSERT INTO usuarios (loja_id, nome, email, senha_hash) VALUES (?, ?, ?, ?)')
                        ->execute([$lojaId, $v['nome'], $v['email'], password_hash($senha, PASSWORD_DEFAULT)]);
                    $uid = (int)$pdo->lastInsertId();
                    $pdo->prepare('INSERT INTO assinaturas (loja_id, plano_id) VALUES (?, ?)')->execute([$lojaId, $v['plano']]);
                    $pdo->commit();
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $uid;
                    flash('Conta criada! Falta assinar o plano para liberar a sua loja.');
                    redirecionar('painel/');
                } catch (PDOException $ex) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $erro = 'Não foi possível criar a conta. Tente novamente.';
                }
            }
        }
    }
}
topo('Criar minha loja');
?>
<form class="form" method="post" novalidate>
  <h1>Criar minha loja</h1>
  <?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
  <?= csrf_campo() ?>
  <label>Nome da loja<input name="loja" value="<?= e($v['loja']) ?>" required></label>
  <label>Seu nome<input name="nome" value="<?= e($v['nome']) ?>" autocomplete="name" required></label>
  <label>E-mail<input name="email" type="email" value="<?= e($v['email']) ?>" autocomplete="email" required></label>
  <label>Senha (mínimo 8 caracteres)<input name="senha" type="password" autocomplete="new-password" required></label>
  <label>Plano
    <select name="plano" required>
      <option value="">Escolha...</option>
      <?php foreach ($planos as $p): ?>
        <option value="<?= (int)$p['id'] ?>" <?= $v['plano'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nome']) ?> — <?= e(brl((int)$p['preco_centavos'])) ?>/mês</option>
      <?php endforeach; ?>
    </select>
  </label>
  <button class="btn">Criar conta</button>
  <p class="nota">Já tem conta? <a href="<?= e(url('login.php')) ?>">Entrar</a></p>
</form>
<?php rodape();
