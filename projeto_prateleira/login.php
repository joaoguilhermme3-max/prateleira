<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/visao.php';
if (usuario()) redirecionar('painel/');

$erro = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!csrf_ok()) {
        $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
    } else {
        $s = db()->prepare('SELECT id, senha_hash FROM usuarios WHERE email = ?');
        $s->execute([$email]);
        $u = $s->fetch();
        // mensagem única para não revelar se o e-mail existe
        if ($u && password_verify((string)($_POST['senha'] ?? ''), $u['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$u['id'];
            redirecionar('painel/');
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
topo('Entrar');
?>
<form class="form" method="post" novalidate>
  <h1>Entrar</h1>
  <?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
  <?= csrf_campo() ?>
  <label>E-mail<input name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required></label>
  <label>Senha<input name="senha" type="password" autocomplete="current-password" required></label>
  <button class="btn">Entrar</button>
  <p class="nota">Ainda não tem loja? <a href="<?= e(url('cadastro.php')) ?>">Criar minha loja</a></p>
</form>
<?php rodape();
