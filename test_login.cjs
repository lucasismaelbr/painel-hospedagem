const { chromium } = require('playwright');

(async () => {
  console.log('Abrindo Chromium...');
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  // Captura erros do console
  const logs = [];
  page.on('console', msg => logs.push(`[${msg.type()}] ${msg.text()}`));
  page.on('requestfailed', req => logs.push(`[FALHA] ${req.url()} - ${req.failure()?.errorText}`));
  page.on('response', resp => { if (resp.status() >= 400) logs.push(`[HTTP ${resp.status()}] ${resp.url()}`); });

  console.log('Navegando para o site...');
  await page.goto('https://painel-hospedagem.onrender.com', { waitUntil: 'networkidle', timeout: 60000 });

  console.log('Página carregada:', page.url());
  await page.screenshot({ path: 'screenshot_login.png' });
  console.log('Screenshot salvo: screenshot_login.png');

  // Preenche o formulário de login
  await page.fill('input[type="email"], input[name="email"]', 'lucasmartins.ecom@gmail.com');
  await page.fill('input[type="password"], input[name="password"]', 'senha123');

  console.log('Clicando em Entrar...');
  await page.click('button[type="submit"]').catch(() => {});

  // Aguarda 10s sem esperar navegação (servidor pode demorar)
  console.log('Aguardando resposta do servidor (60s)...');
  await page.waitForTimeout(60000);

  console.log('URL após login:', page.url());
  console.log('\n=== LOGS DO BROWSER ===');
  logs.forEach(l => console.log(l));
  console.log('=======================\n');

  // Pega o texto da página sem screenshot (evita hang)
  const bodyText = await page.locator('body').innerText({ timeout: 5000 }).catch(() => 'Timeout ao ler body');
  console.log('Conteúdo da página:', bodyText.substring(0, 800));

  await browser.close();
})();
