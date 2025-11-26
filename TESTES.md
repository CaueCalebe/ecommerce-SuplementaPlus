# 🧪 Guia de Testes - MVP SuplementaPlus

## Pré-requisitos
- Servidor local (XAMPP, LARAGON, etc.)
- PHP 7.4+
- PostgreSQL configurado
- Database schema criado via `database/schema.sql`

---

## 🎯 Cenários de Teste

### TESTE 1: Registro de Novo Usuário
**Passos:**
1. Abrir `register.html`
2. Preencher formulário com:
   - Nome: "João Silva"
   - Email: "joao@teste.com"
   - Senha: "senha123"
3. Clicar "Registrar"

**Resultado Esperado:** ✅
- Usuário criado no banco
- Redirecionado para login.html
- Mensagem de sucesso (implementar no futuro)

**Possíveis Erros:**
- Email duplicado → Mensagem de erro
- Campos vazios → Validação JavaScript
- Senha muito curta → Mensagem de erro

---

### TESTE 2: Login com Credenciais Válidas
**Passos:**
1. Abrir `login.html`
2. Preencher com:
   - Email: "joao@teste.com"
   - Senha: "senha123"
3. Clicar "Entrar"

**Resultado Esperado:** ✅
- Session criada com usuario_id
- Redirecionado para `index.php`
- Pode ver sua conta no header

**Possíveis Erros:**
- Email/senha incorretos → Mensagem de erro
- Usuário não existe → Mensagem de erro

---

### TESTE 3: Login com Credenciais Inválidas
**Passos:**
1. Abrir `login.html`
2. Preencher com dados incorretos
3. Clicar "Entrar"

**Resultado Esperado:** ✅
- Mensagem de erro: "Email ou senha incorretos"
- Permanecer em login.html

---

### TESTE 4: Visualizar Produtos
**Passos:**
1. Estar logado
2. Abrir `index.php` (ou clicar em "Produtos")
3. Verificar produtos exibidos

**Resultado Esperado:** ✅
- Todos os produtos visíveis em grid
- Imagens carregadas
- Preços exibidos

**Possíveis Erros:**
- Banco vazio → Adicionar produtos manualmente
- Imagens não encontradas → Verificar caminho em assets/

---

### TESTE 5: Adicionar Produto ao Carrinho
**Passos:**
1. Na página de produtos, clicar "Adicionar ao Carrinho"
2. Voltar e adicionar outro produto
3. Adicionar o mesmo produto 2 vezes

**Resultado Esperado:** ✅
- Carrinho salvo em SESSION
- Quantidades acumulam (não duplicam)
- Ícone de carrinho mostra quantidade total

**Verificação:**
```javascript
console.log($_SESSION['carrinho']);
// Esperado: ['1' => 2, '5' => 1]  (não ['1', '1', '5'])
```

---

### TESTE 6: Visualizar Carrinho
**Passos:**
1. Clicar no ícone/link de carrinho
2. Abrir `carrinho.php`

**Resultado Esperado:** ✅
- Tabela com produtos adicionados
- Quantidade de cada item
- Subtotal de cada produto
- Total geral
- Botões: Atualizar, Remover, Limpar, Checkout, Continuar Comprando

---

### TESTE 7: Atualizar Quantidade no Carrinho
**Passos:**
1. Em `carrinho.php`
2. Mudar quantidade de um produto
3. Clicar "Atualizar"

**Resultado Esperado:** ✅
- Página recarrega
- Nova quantidade refletida
- Subtotal e total recalculados

---

### TESTE 8: Remover Item do Carrinho
**Passos:**
1. Em `carrinho.php`
2. Clicar "Remover" em um produto

**Resultado Esperado:** ✅
- Produto removido da tabela
- Total recalculado
- Página recarrega

---

### TESTE 9: Limpar Carrinho
**Passos:**
1. Em `carrinho.php`
2. Clicar "Limpar Carrinho"
3. Confirmar no popup

**Resultado Esperado:** ✅
- Todos os itens removidos
- Carrinho vazio
- Mensagem "Seu carrinho está vazio"

---

### TESTE 10: Ir para Checkout
**Passos:**
1. Em `carrinho.php` com itens
2. Clicar "Ir para Checkout"

**Resultado Esperado:** ✅
- Redirecionado para `checkout.php`
- Resumo do pedido visível
- Formulário para dados de entrega

---

### TESTE 11: Preencher Formulário de Checkout
**Passos:**
1. Em `checkout.php`
2. Preencher todos os campos:
   - Nome Completo: "João Silva Santos"
   - Email: "joao@teste.com"
   - Telefone: "(11) 99999-9999"
   - CEP: "01310-100"
   - Endereço: "Rua Augusta"
   - Número: "2500"
   - Complemento: "Apto 1201"
   - Cidade: "São Paulo"
   - Estado: "SP"
3. Selecionar método: "Crédito"
4. Clicar "Finalizar Compra"

**Resultado Esperado:** ✅
- Pedido criado no banco
- Redirecionado para `meus-pedidos.php`
- Mensagem: "Pedido criado com sucesso!"
- Carrinho limpo

**Verificação no Banco:**
```sql
SELECT * FROM pedidos WHERE usuario_id = 1;
SELECT * FROM itens_pedido WHERE pedido_id = 1;
```

---

### TESTE 12: Validação de Campos do Checkout
**Passos:**
1. Tentar enviar com campos vazios
2. Tentar com email inválido
3. Tentar com CEP muito curto

**Resultado Esperado:** ✅
- Mensagens de erro para cada campo
- Pedido NÃO criado
- Dados permanecem preenchidos

---

### TESTE 13: Histórico de Pedidos
**Passos:**
1. Após criar pedido, estar em `meus-pedidos.php`
2. Verificar listagem de pedidos

**Resultado Esperado:** ✅
- Pedido aparece com:
  - Número do pedido
  - Data
  - Status (Pendente)
  - Valor total
  - Botões: Ver Detalhes, Cancelar

---

### TESTE 14: Ver Detalhes do Pedido
**Passos:**
1. Em `meus-pedidos.php`
2. Clicar "Ver Detalhes"

**Resultado Esperado:** ✅
- Página mostra:
  - Status do pedido com badge colorido
  - Dados de entrega completos
  - Método de pagamento
  - Tabela com produtos
  - Total do pedido

---

### TESTE 15: Cancelar Pedido
**Passos:**
1. Em `meus-pedidos.php`
2. Clicar "Cancelar Pedido"
3. Confirmar

**Resultado Esperado:** ✅
- Status muda para "Cancelado"
- Badge muda de cor (vermelho)
- Botão "Cancelar" desaparece
- Mensagem de sucesso

**Verificação no Banco:**
```sql
SELECT status FROM pedidos WHERE id = 1;
-- Esperado: 'cancelado'
```

---

### TESTE 16: Segurança - CSRF Token
**Passos:**
1. Em `checkout.php`, abrir Developer Tools
2. Verificar form_data
3. Tentar enviar requisição POST sem token (via script)

**Resultado Esperado:** ✅
- Erro "Token de segurança inválido"
- Pedido NÃO criado

---

### TESTE 17: Responsividade - Desktop
**Passos:**
1. Abrir site em Desktop (1920x1080)
2. Verificar todas as páginas

**Resultado Esperado:** ✅
- Grid com 2 colunas
- Resumo na lateral direita
- Todos os elementos visíveis

---

### TESTE 18: Responsividade - Tablet
**Passos:**
1. Developer Tools → Device Toolbar
2. Selecionar iPad (768px)
3. Verificar layout

**Resultado Esperado:** ✅
- Grid colapsado para 1 coluna
- Resumo embaixo
- Fonte reduzida
- Tudo legível

---

### TESTE 19: Responsividade - Mobile
**Passos:**
1. Developer Tools → Device Toolbar
2. Selecionar iPhone (375px)
3. Verificar layout

**Resultado Esperado:** ✅
- Uma coluna
- Botões full-width
- Tabelas com scroll
- Fácil de usar com toque

---

### TESTE 20: Logout
**Passos:**
1. Logado em qualquer página
2. Clicar "Sair" (logout)

**Resultado Esperado:** ✅
- Session destruída
- Redirecionado para login
- Não consegue acessar carrinho sem login

---

## 🐛 Bugs Conhecidos e Soluções

### Bug: "Undefined function conectarBD()"
**Causa:** Linter não reconhece função em db.php
**Solução:** É apenas aviso, código funciona normalmente

### Bug: Imagens não carregam
**Causa:** Caminho relativo incorreto
**Solução:** Verificar estrutura de pasta assets/

### Bug: Banco vazio
**Causa:** Schema não executado
**Solução:** Executar `database/schema.sql` no PostgreSQL

---

## 📊 Testes de Carga (Opcional)

Para testar com múltiplos usuários:
```bash
# Usar Apache Bench
ab -n 100 -c 10 http://localhost/index.php

# Ou usar PostMan para simular requisições
```

---

## ✅ Checklist Final

- [ ] Todos os 20 testes passaram
- [ ] Nenhum erro no console do navegador
- [ ] Banco de dados operacional
- [ ] Responsividade funcionando
- [ ] CSRF tokens validados
- [ ] Senhas seguras (password_hash)
- [ ] Validação de entrada funcionando
- [ ] Pedidos salvos corretamente

---

**🎉 Sucesso nos testes = MVP pronto para produção!**
