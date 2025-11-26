const API = {
  // Base URL para o backend
  baseURL: './src/php/',

  // Registrar novo usuário
  async register(email, password, repeatPassword) {
    if (password !== repeatPassword) {
      return { success: false, message: 'As senhas não coincidem' };
    }

    if (password.length < 6) {
      return { success: false, message: 'A senha deve ter no mínimo 6 caracteres' };
    }

    try {
      const formData = new FormData();
      formData.append('email', email);
      formData.append('password', password);

      const response = await fetch(this.baseURL + 'register.php', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      return data;
    } catch (error) {
      return { success: false, message: 'Erro na conexão: ' + error.message };
    }
  },

  // Fazer login
  async login(email, password) {
    try {
      const formData = new FormData();
      formData.append('email', email);
      formData.append('password', password);

      const response = await fetch(this.baseURL + 'login.php', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        localStorage.setItem('user', JSON.stringify(data.user));
      }
      return data;
    } catch (error) {
      return { success: false, message: 'Erro na conexão: ' + error.message };
    }
  },

  // Fazer logout
  logout() {
    localStorage.removeItem('user');
    localStorage.removeItem('carrinho');
    return { success: true };
  },

  // Obter usuário autenticado
  getUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },

  // Adicionar produto ao carrinho
  addToCart(product) {
    let carrinho = localStorage.getItem('carrinho');
    carrinho = carrinho ? JSON.parse(carrinho) : [];

    // CORREÇÃO: Converter IDs para string para comparação consistente
    const productId = String(product.id);

    // Verifica se o produto já está no carrinho
    const existing = carrinho.find(item => String(item.id) === productId);
    if (existing) {
      existing.quantidade++;
    } else {
      carrinho.push({
        id: productId,
        name: product.name,
        price: parseFloat(product.price),
        quantidade: 1,
      });
    }

    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    return { success: true, message: 'Produto adicionado ao carrinho!' };
  },

  // Obter carrinho
  getCart() {
    const carrinho = localStorage.getItem('carrinho');
    return carrinho ? JSON.parse(carrinho) : [];
  },

  // Remover produto do carrinho
  removeFromCart(productId) {
    let carrinho = this.getCart();
    // CORREÇÃO: Converter para string
    carrinho = carrinho.filter(item => String(item.id) !== String(productId));
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    return { success: true };
  },

  // Atualizar quantidade no carrinho
  updateCartQuantity(productId, quantity) {
    let carrinho = this.getCart();
    // CORREÇÃO: Converter para string
    const item = carrinho.find(item => String(item.id) === String(productId));
    if (item) {
      if (quantity <= 0) {
        return this.removeFromCart(productId);
      }
      item.quantidade = parseInt(quantity);
      localStorage.setItem('carrinho', JSON.stringify(carrinho));
    }
    return { success: true };
  },

  // Calcular total do carrinho
  calculateTotal() {
    const carrinho = this.getCart();
    const subtotal = carrinho.reduce((sum, item) => {
      const price = parseFloat(item.price);
      const quantidade = parseInt(item.quantidade);
      return sum + (price * quantidade);
    }, 0);
    const shipping = carrinho.length > 0 ? 10.0 : 0; // Frete fixo de R$ 10
    const total = subtotal + shipping;

    return {
      subtotal: subtotal.toFixed(2),
      shipping: shipping.toFixed(2),
      total: total.toFixed(2),
    };
  },

  // Obter quantidade total de itens no carrinho
  getCartCount() {
    const carrinho = this.getCart();
    return carrinho.reduce((sum, item) => sum + parseInt(item.quantidade), 0);
  },

  // Limpar carrinho
  clearCart() {
    localStorage.removeItem('carrinho');
    return { success: true };
  },

  // Validar email
  isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
};