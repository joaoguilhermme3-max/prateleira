# Prateleira — sistema multiloja (etapa 1: base, cadastro e login)

## Como rodar
1. Crie um banco MySQL (hPanel da Hostinger ou local) e importe `sql/schema.sql`.
2. Copie `config/config.example.php` para `config/config.php` e preencha banco e `url_base`.
3. Local: precisa de PHP 8.1+ (XAMPP, Laragon ou `php -S localhost:8000` na pasta do projeto).
   O Live Server (porta 5500) NÃO executa PHP.
4. Abra `/cadastro.php`, crie uma loja e entre no painel.

## Estrutura
- `app/` núcleo (conexão, sessão, CSRF, regra de assinatura), bloqueado por .htaccess
- `config/` credenciais, bloqueado por .htaccess
- `sql/` esquema do banco, bloqueado por .htaccess
- `painel/` área logada do lojista
- `assets/css/` estilos

## Próximas etapas
2. Produtos, estoque e vendas por loja  3. Vitrine pública `/loja/<slug>` e faturamento
4. Cobrança (gateway + webhook) e bloqueio automático  5. PDV, relatórios
