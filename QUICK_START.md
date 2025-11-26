# ⚡ Quick Start - E-Commerce SuplementaPlus

## 🚀 Comece Agora em 5 Minutos

### Passo 1: Clonar/Copiar Projeto
```bash
# Copie toda a pasta para seu servidor local
# Exemplo: C:/xampp/htdocs/ecommerce-SuplementaPlus
```

### Passo 2: Criar Banco de Dados
```sql
-- Abrir phpMyAdmin ou pgAdmin
-- Criar database: CREATE DATABASE ecommerce_suplementaplus;
-- Importar arquivo: database/schema.sql
```

### Passo 3: Configurar Conexão
Editar `backend/config/db.php`:
```php
// Seu servidor
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_suplementaplus');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_PORT', 5432); // PostgreSQL
```

### Passo 4: Adicionar Produtos (Opcional)
```sql
INSERT INTO produtos (nome, preco, descricao, estoque, imagem, categoria)
VALUES 
('Whey Protein', 99.90, 'Proteína isolada...', 50, 'whey.jpg', 'Proteínas'),
('Creatina', 79.90, 'Monoidrato de creatina...', 30, 'creatina.jpg', 'Creatina');
```

### Passo 5: Acessar
```
http://localhost/ecommerce-SuplementaPlus/
```

---

## 🎯 Fluxo Rápido de Teste

1. **Registrar Usuário**
   - Clique em "Registrar"
   - Email: `teste@email.com`
   - Senha: `senha123`

2. **Fazer Login**
   - Email: `teste@email.com`
   - Senha: `senha123`

3. **Adicionar Produtos**
   - Clique "Adicionar ao Carrinho"
   - Repita 2-3 vezes

4. **Fazer Compra**
   - Abrir carrinho
   - Clicar "Ir para Checkout"
   - Preencher dados de entrega
   - Clicar "Finalizar Compra"

5. **Ver Pedido**
   - Clique em "Meus Pedidos"
   - Clique "Ver Detalhes"

---

## 📁 Arquivos Principais

| Arquivo | Função |
|---------|--------|
| `index.html` | Home |
| `register.html` | Cadastro |
| `login.html` | Login |
| `src/php/index.php` | Produtos |
| `src/php/carrinho.php` | Carrinho |
| `src/php/checkout.php` | Checkout |
| `src/php/meus-pedidos.php` | Pedidos |

---

## 🔧 Solução de Problemas

### "Erro de conexão com banco"
→ Verificar credenciais em `backend/config/db.php`

### "Página branca / Sem conteúdo"
→ Ativar error reporting em `backend/config/db.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### "Imagens não aparecem"
→ Colocar imagens em `assets/` com nome correto

### "Não consegue fazer login"
→ Verificar se usuário foi criado no banco

---

## 💾 Estrutura Mínima do Banco

Apenas 4 tabelas são essenciais:
```sql
usuarios      -- email, senha
produtos      -- nome, preco, imagem
pedidos       -- usuario_id, total, status
itens_pedido  -- pedido_id, produto_id, quantidade
```

---

## 🎨 Personalizações Rápidas

### Mudar Cores
Em `src/css/style.css`:
```css
:root {
  --cor-primaria: #4CAF50;    /* Verde */
  --cor-secundaria: #2196F3;  /* Azul */
  --cor-destaque: #FF9800;    /* Laranja */
}
```

### Mudar Nome da Loja
Em `src/php/includes/header.php`:
```php
<h1>Sua Loja</h1>
```

### Adicionar Logo
Em `src/php/includes/header.php`:
```html
<img src="logo.png" alt="Logo">
```

---

## ✅ Checklist Inicial

- [ ] Banco de dados criado
- [ ] Schema.sql importado
- [ ] db.php configurado
- [ ] Projeto em htdocs/
- [ ] Servidor rodando
- [ ] Acesso via localhost
- [ ] Registro funcionando
- [ ] Login funcionando
- [ ] Produtos exibindo
- [ ] Carrinho funcionando

---

## 📱 Testar Responsividade

### Desktop
- Abrir site normalmente
- Deve ocupar toda a tela

### Tablet
- Abrir DevTools (F12)
- Ctrl+Shift+M (toggle device)
- Selecionar iPad (768px)

### Mobile
- DevTools aberto
- Selecionar iPhone (375px)

---

## 🚀 Deploy em Produção

1. Fazer backup do banco
2. Alterar credenciais em db.php
3. Desativar error_reporting
4. Testar tudo novamente
5. Upload para servidor

---

## 📞 Arquivos de Documentação

- `README_MVP.md` - Overview geral
- `MVP_COMPLETO.md` - Funcionalidades em detalhe
- `TESTES.md` - 20 cenários de teste
- `ESTRUTURA_PROJETO.md` - Mapa completo
- `QUICK_START.md` - Este arquivo

---

**🎉 Pronto! Seu MVP está rodando!**

Próximo passo: Execute os testes em `TESTES.md`
