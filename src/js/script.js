// Script.js - Gerenciador de interações do frontend

document.addEventListener('DOMContentLoaded', function() {

  // ===== ATUALIZAR LINK DE LOGIN/LOGOUT =====
  updateAuthLink();

  // ===== LIMPAR FORMULÁRIOS AO CARREGAR PÁGINA =====
  const registerForm = document.getElementById('registerForm');
  const loginForm = document.getElementById('loginForm');

  // Limpar formulário de registro ao carregar
  if (registerForm) {
    registerForm.reset();
    const errorMsg = document.getElementById('registerError');
    const successMsg = document.getElementById('registerSuccess');
    if (errorMsg) errorMsg.style.display = 'none';
    if (successMsg) successMsg.style.display = 'none';
  }

  // Limpar formulário de login ao carregar
  if (loginForm) {
    loginForm.reset();
    const errorMsg = document.getElementById('loginError');
    const successMsg = document.getElementById('loginSuccess');
    if (errorMsg) errorMsg.style.display = 'none';
    if (successMsg) successMsg.style.display = 'none';
  }

  // ===== PÁGINAS DE LOGIN E REGISTRO =====

  // Formulário de Registro
  if (registerForm) {
    registerForm.addEventListener('submit', async function(event) {
      event.preventDefault();

      const email = document.querySelector('input[name="email"]').value.trim();
      const repeatEmail = document.querySelector('input[name="repeat_email"]').value.trim();
      const password = document.querySelector('input[name="password"]').value;
      const repeatPassword = document.querySelector('input[name="repeat_password"]').value;

      // Validação de campos vazios
      if (!email || !repeatEmail || !password || !repeatPassword) {
        showError('registerError', 'Todos os campos são obrigatórios.');
        return;
      }

      // Validação de email
      if (!API.isValidEmail(email)) {
        showError('registerError', 'Por favor, insira um email válido.');
        return;
      }

      // Validação de e-mails
      if (email !== repeatEmail) {
        showError('registerError', 'Os e-mails não coincidem.');
        return;
      }

      // Validação de senhas
      if (password !== repeatPassword) {
        showError('registerError', 'As senhas não coincidem.');
        return;
      }

      // Validação de tamanho mínimo de senha
      if (password.length < 6) {
        showError('registerError', 'A senha deve ter no mínimo 6 caracteres.');
        return;
      }

      // Desabilitar botão durante o processo
      const submitBtn = registerForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Registrando...';

      // Chamar API para registrar
      const result = await API.register(email, password, repeatPassword);

      if (result.success) {
        // Limpar os campos do formulário
        registerForm.reset();
        
        showSuccess('registerSuccess', 'Conta criada com sucesso! Redirecionando...');
        setTimeout(() => {
          window.location.href = './login.html';
        }, 2000);
      } else {
        showError('registerError', result.message || 'Erro ao registrar');
        // Reabilitar botão
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }

  // Formulário de Login
  if (loginForm) {
    loginForm.addEventListener('submit', async function(event) {
      event.preventDefault();

      const email = document.querySelector('input[name="email"]').value.trim();
      const password = document.querySelector('input[name="password"]').value;

      // Validação de campos vazios
      if (!email || !password) {
        showError('loginError', 'Email e senha são obrigatórios.');
        return;
      }

      // Validação de email
      if (!API.isValidEmail(email)) {
        showError('loginError', 'Por favor, insira um email válido.');
        return;
      }

      // Desabilitar botão durante o processo
      const submitBtn = loginForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Entrando...';

      const result = await API.login(email, password);

      if (result.success) {
        // Limpar os campos do formulário
        loginForm.reset();
        
        showSuccess('loginSuccess', 'Login realizado com sucesso! Redirecionando...');
        setTimeout(() => {
          window.location.href = './index.html';
        }, 2000);
      } else {
        showError('loginError', result.message || 'Email ou senha incorretos');
        // Reabilitar botão
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }

  // ===== PÁGINA PRINCIPAL (INDEX.HTML) =====

  // Adicionar ao carrinho
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  addToCartButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      const card = this.closest('.product-card');
      const product = {
        id: card.dataset.id,
        name: card.dataset.name,
        price: parseFloat(card.dataset.price),
      };

      const result = API.addToCart(product);
      
      // Mostrar feedback
      const originalText = this.textContent;
      this.textContent = '✓ Adicionado!';
      this.style.backgroundColor = '#229954';
      
      setTimeout(() => {
        this.textContent = originalText;
        this.style.backgroundColor = '';
      }, 2000);

      // Atualizar contador do carrinho
      updateCartBadge();
    });
  });

  // ===== PÁGINA DE CARRINHO (CARRINHO.HTML) =====
  
  if (window.location.pathname.includes('carrinho.html')) {
    renderCart();
  }

  // Botão de finalizar compra - CORREÇÃO CRÍTICA
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', async function() {
      const user = API.getUser();
      if (!user) {
        alert('Você precisa estar logado para finalizar a compra');
        window.location.href = './login.html';
        return;
      }

      const carrinho = API.getCart();
      if (carrinho.length === 0) {
        alert('Seu carrinho está vazio');
        return;
      }

      // Desabilitar botão durante processamento
      this.disabled = true;
      this.textContent = 'Processando...';

      try {
        // Chamar API de checkout
        const formData = new FormData();
        formData.append('user_id', user.id);
        formData.append('items', JSON.stringify(carrinho));

        const response = await fetch('./src/php/carrinho.php?action=checkout', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          alert('Compra finalizada com sucesso!');
          localStorage.removeItem('carrinho');
          window.location.href = './index.html';
        } else {
          alert('Erro ao finalizar compra: ' + result.message);
          this.disabled = false;
          this.textContent = 'Finalizar Compra';
        }
      } catch (error) {
        alert('Erro ao processar compra: ' + error.message);
        this.disabled = false;
        this.textContent = 'Finalizar Compra';
      }
    });
  }

  // ===== BARRA DE PESQUISA =====
  const searchButton = document.querySelector('.search-bar button');
  if (searchButton) {
    searchButton.addEventListener('click', function() {
      const searchInput = document.querySelector('.search-bar input');
      if (searchInput.value.trim() === "") {
        alert('Por favor, digite algo para buscar.');
      } else {
        alert('Buscando por: ' + searchInput.value);
        // Aqui você pode implementar busca real
      }
    });
  }

});

// ===== FUNÇÕES AUXILIARES =====

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = message;
    element.style.display = 'block';
    setTimeout(() => {
      element.style.display = 'none';
    }, 5000);
  }
}

function showSuccess(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = message;
    element.style.display = 'block';
  }
}

function updateAuthLink() {
  const authLink = document.getElementById('authLink');
  if (authLink) {
    const user = API.getUser();
    if (user) {
      authLink.textContent = 'Logout';
      authLink.href = '#';
      authLink.onclick = function(e) {
        e.preventDefault();
        API.logout();
        updateAuthLink();
        window.location.href = './index.html';
      };
    } else {
      authLink.textContent = 'Login';
      authLink.href = './login.html';
    }
  }
}

function updateCartBadge() {
  const carrinho = API.getCart();
  // Você pode usar isso para mostrar número de itens no carrinho
}

function renderCart() {
  const carrinho = API.getCart();
  const cartEmpty = document.getElementById('cartEmpty');
  const cartContent = document.getElementById('cartContent');
  const cartItems = document.getElementById('cartItems');

  if (carrinho.length === 0) {
    cartEmpty.style.display = 'block';
    cartContent.style.display = 'none';
    return;
  }

  cartEmpty.style.display = 'none';
  cartContent.style.display = 'block';

  cartItems.innerHTML = '';
  carrinho.forEach((item, index) => {
    const subtotal = (item.price * item.quantidade).toFixed(2);
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.name}</td>
      <td>R$ ${item.price.toFixed(2).replace('.', ',')}</td>
      <td>
        <div class="quantity-controls">
          <button class="qty-btn" onclick="updateQuantity('${item.id}', ${item.quantidade - 1})">−</button>
          <input type="number" min="1" value="${item.quantidade}" 
            onchange="updateQuantity('${item.id}', this.value)" class="qty-input">
          <button class="qty-btn" onclick="updateQuantity('${item.id}', ${item.quantidade + 1})">+</button>
        </div>
      </td>
      <td class="subtotal-cell">R$ ${subtotal.replace('.', ',')}</td>
      <td>
        <button class="btn-remove" onclick="removeItem('${item.id}')">Remover</button>
      </td>
    `;
    cartItems.appendChild(row);
  });

  // Atualizar totais
  const totals = API.calculateTotal();
  document.getElementById('subtotal').textContent = 'R$ ' + totals.subtotal.replace('.', ',');
  document.getElementById('shipping').textContent = 'R$ ' + totals.shipping.replace('.', ',');
  document.getElementById('total').textContent = 'R$ ' + totals.total.replace('.', ',');
}

function updateQuantity(productId, quantity) {
  const qty = parseInt(quantity);
  if (qty < 1) {
    return;
  }
  API.updateCartQuantity(productId, qty);
  renderCart();
}

function removeItem(productId) {
  if (confirm('Deseja remover este item?')) {
    API.removeFromCart(productId);
    renderCart();
  }
}