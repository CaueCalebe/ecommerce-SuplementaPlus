// Este evento garante que o código JavaScript só será executado
// depois que todo o conteúdo da página (HTML) for carregado.
document.addEventListener('DOMContentLoaded', function() {

  // ===== VALIDAÇÃO DO FORMULÁRIO DE REGISTRO =====
  // Selecionamos o formulário da página de registro pela sua tag e atributo.
  const registerForm = document.querySelector('form[action="register.php"]');

  // Verificamos se o formulário de registro realmente existe na página atual
  // para não executar este código em outras páginas (como index.html).
  if (registerForm) {
    registerForm.addEventListener('submit', function(event) {
      // Seleciona os campos do formulário
      const email = document.querySelector('input[name="email"]');
      const repeatEmail = document.querySelector('input[name="repeat_email"]');
      const password = document.querySelector('input[name="password"]');
      const repeatPassword = document.querySelector('input[name="repeat_password"]');
      
      // 1. Validação de E-mail
      if (email.value !== repeatEmail.value) {
        // Impede o envio do formulário
        event.preventDefault();
        alert('Os e-mails não coincidem. Por favor, verifique.');
        return; // Para a execução
      }

      // 2. Validação de Senha
      if (password.value !== repeatPassword.value) {
        event.preventDefault();
        alert('As senhas não coincidem. Por favor, verifique.');
        return;
      }

      // 3. Validação do Comprimento da Senha
      if (password.value.length < 6) {
        event.preventDefault();
        alert('A senha deve ter no mínimo 6 caracteres.');
        return;
      }
    });
  }


  // ===== INTERATIVIDADE NA PÁGINA PRINCIPAL (INDEX.HTML) =====
  
  // Seleciona todos os cards de produtos
  const productCards = document.querySelectorAll('.product-card');
  
  // Adiciona um evento de clique para cada card de produto
  productCards.forEach(function(card) {
    card.addEventListener('click', function() {
      // Pega o nome do produto de dentro do card
      const productName = card.querySelector('h3').innerText;
      alert(productName + ' foi adicionado ao carrinho!');
    });
  });

  // Seleciona o botão de busca principal
  const searchButton = document.querySelector('.search-bar button');

  // Verifica se o botão de busca existe na página
  if (searchButton) {
    searchButton.addEventListener('click', function() {
        const searchInput = document.querySelector('.search-bar input');
        // Impede a busca caso o campo esteja vazio
        if (searchInput.value.trim() === "") {
            alert('Por favor, digite algo para buscar.');
        } else {
            alert('Buscando por: ' + searchInput.value);
        }
    });
  }

});