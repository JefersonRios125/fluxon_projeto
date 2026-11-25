<?php
require_once __DIR__ . '/../services/ReviewEmailService.php';
if (empty($_SESSION['uid'])) go('login');
try {
    $q = $db->prepare('SELECT nome,email,email_verificado_em,versao_sessao FROM users WHERE id=?');
    $q->execute([$_SESSION['uid']]);
    $account = $q->fetch();
    if (!$account || !$account['email_verificado_em'] || (int)$account['versao_sessao'] !== ($_SESSION['versao_sessao'] ?? -1)) {
        session_destroy();
        go('login');
    }
    $_SESSION['review_email'] ??= [];
    if ($POST) {
        chk();
        try {
            $reference = ReviewEmailService::submit($_POST,$account,$_SESSION['review_email'],$C,'enviar');
            flash('ok', 'Mensagem enviada à equipe Fluxon. Referência: ' . $reference . '. Solicitações de retificação ou exclusão serão analisadas pela equipe.');
            go('avaliacao');
        } catch (InvalidArgumentException $ex) {
            $err = $ex->getMessage();
        }
    }
    $reference = $_SESSION['review_email']['reference'] ?? '';
    require __DIR__ . '/../views/reviews_view.php';
} catch (Throwable $ex) {
    error_log('Fluxon: falha ao processar avaliação por e-mail.');
    http_response_code(500);
    card('Avaliar aplicativo','<p>Não foi possível abrir o envio de avaliações. Tente novamente ou procure o suporte.</p><a href="?p=painel">Voltar ao painel</a>');
}
