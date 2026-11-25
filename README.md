# Fluxon Analytics — Tesoureiro Digital

Projeto acadêmico de Análise e Desenvolvimento de Sistemas do Centro Universitário Piaget. O Fluxon ajuda a acompanhar movimentações, reservas e saldo disponível estimado em um ambiente financeiro simulado.

## O que está disponível

- **Painel:** indicadores de saldo registrado, reservas ativas e disponível estimado, com gráficos e filtros.
- **Cadastros e movimentações:** contas simuladas, categorias, regras de provisão, lançamentos manuais, importação fictícia sem duplicação e estorno.
- **Extrato e relatórios:** consulta com filtros, ordenação e paginação; exportação em PDF e XLSX.
- **Acesso e configurações:** cadastro, verificação por e-mail, código de acesso, recuperação de senha, Google OAuth opcional, perfil, foto, preferências, segurança, ajuda, avaliação por e-mail e exclusão de conta.
- **Interface:** visual compacto em todo o sistema, navegação compartilhada entre as telas financeiras e o perfil, opções de tema e tamanho de fonte.

## Rodar no XAMPP

Requisitos: PHP 8.1+, Apache, Composer e um projeto PostgreSQL no Supabase. Habilite no PHP as extensões `pdo_pgsql`, `pgsql`, `curl`, `openssl`, `gd`, `fileinfo`, `iconv` e `zip`. A extensão ZIP é usada na exportação XLSX.

1. Coloque o projeto em `C:\xampp\htdocs\fluxon_projeto-main`. No terminal, dentro da pasta, rode `composer install`.
2. **Banco novo:** no SQL Editor do Supabase, execute `database/schema.sql` e depois `database/migracao_fase3.sql`. **Banco PostgreSQL existente, antes da Fase 3:** execute somente `database/migracao_fase3.sql`. Se já aplicou essa migração e só quer atualizar o visual, pule este passo.
3. Copie `.env.example` para `.env`. Preencha os parâmetros do banco exibidos em **Connect → Session pooler** e configure o SMTP para os fluxos por e-mail. Mantenha `APP_BASE_URL` igual ao endereço real do `index.php`. Como alternativa, use `config.local.example.php` como modelo para `config.local.php`.
4. Inicie o Apache e abra [o Fluxon local](http://localhost/fluxon_projeto-main/index.php).

A autenticação é feita pelo Fluxon em PHP. O Supabase fornece o banco. Os dados do MySQL não são importados automaticamente; os scripts de `database/mysql_legado/` são apenas para a versão antiga.

## Navegação da conta

Em **Configurações**, os atalhos abrem Segurança e senha, Avaliar aplicativo, Ajuda e suporte, Perguntas frequentes e Sobre o sistema. Essas páginas usam o cabeçalho e o menu do Dashboard. Os botões verde-lima acompanham o tamanho do texto. Para encerrar a sessão, clique em **Sair da conta** ao lado da inicial do perfil.

## Diagnóstico local de desempenho

Com o Apache ativo e a conta conectada, abra [o diagnóstico de carregamento](http://localhost/fluxon_projeto-main/diagnostico.html) e clique em **Medir carregamento**. A tabela compara as medianas de três carregamentos do FAQ e do Dashboard e detalha as consultas financeiras. Os tempos de cada consulta já fazem parte do total de busca financeira.

Para investigar a conexão no Windows, execute `Medir_Conexao_Fluxon.bat` na raiz do projeto ou rode `php medir-conexao.php` no terminal. O teste mede cinco aberturas de conexão, uma consulta simples e sondagens independentes de DNS e TCP. A aplicação usa consultas parametrizadas de execução única no repositório financeiro e reúne saldo e reservas em uma consulta PostgreSQL.

## Testes e documentação

Execute `composer test` para verificar a sintaxe e as regras de validação. Para testar o fluxo completo, use dados fictícios e siga [Instalação e demonstração da Fase 3](INSTALAR_FASE3.md).

- [Requisitos da Fase 3](docs/FASE3_REQUISITOS.md)
- [Validação da Fase 3](docs/FASE3_VALIDACAO.md)
- [Integração do dashboard](INTEGRACAO_DASHBOARD.md)

As reservas são virtuais. O Fluxon não conecta bancos reais, movimenta dinheiro, paga tributos ou envia notificações agendadas. O disponível estimado não considera obrigações futuras que ainda não foram registradas.

Mantenha `.env`, `config.local.php`, logs e `vendor/` fora do Git. O Apache deve aplicar as regras dos arquivos `.htaccess`. 

**Equipe:** Igor Silva, Jeferson Rios Pereira e Deric Fahr Diniz.
 
## Avaliações e comunicação por e-mail

A nota e o comentário do formulário são enviados por SMTP para `REVIEWS_TO` (padrão: `fluxontesoureirodigital@gmail.com`), com nome e e-mail da conta autenticada e uma referência de atendimento. O e-mail do usuário é usado como Reply-To; o remetente continua sendo o configurado no SMTP.

Novas avaliações não são salvas em `user_reviews`. Na sessão ficam apenas referência e horário, com intervalo mínimo de um minuto entre envios. Retificação e exclusão geram solicitações por e-mail para análise da equipe; não alteram mensagens já entregues. Após encerrar a sessão, o usuário pode procurar o suporte com a referência ou seu e-mail.

As avaliações históricas no banco foram preservadas, sem exportação ou exclusão automática. A exclusão da conta não remove mensagens já entregues à equipe.

Os códigos de segurança, a recuperação de senha e a confirmação de exclusão usam templates HTML com alternativa em texto. Os relatórios PDF e XLSX têm cabeçalho e organização visual padronizados. Veja [a revisão desta entrega](docs/REVISAO_EMAIL_RELATORIOS.md).

### Atualizar uma instalação existente

1. Faça backup da pasta e preserve `.env`, `config.local.php` e `vendor/`.
2. Copie os arquivos desta versão sobre o projeto existente.
3. Se desejar configurar outro destinatário, adicione `REVIEWS_TO=seu-email-institucional` no `.env`. Não altere as credenciais SMTP que já funcionam.
4. Não há migração obrigatória para esta atualização. A tabela antiga de avaliações permanece apenas para o histórico.
5. Atualize a página com Ctrl+F5. Envie uma avaliação fictícia e confira a caixa institucional e o Reply-To. A confirmação em tela significa aceitação pelo servidor SMTP, não garante entrega na caixa de entrada.

Execute `composer test`: inclui validação do fluxo de avaliação por e-mail e dos templates, sem enviar mensagens reais. Configure o SMTP para testar a entrega em sua máquina.
