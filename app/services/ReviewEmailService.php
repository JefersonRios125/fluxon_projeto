<?php
require_once __DIR__ . '/ReviewService.php';
require_once __DIR__ . '/EmailTemplate.php';

final class ReviewEmailService
{
    public static function submit(array $input, array $account, array &$state, array $config, callable $send, ?int $now = null): string
    {
        $now ??= time();
        $action = $input['acao'] ?? '';
        if (!is_string($action) || !in_array($action,['enviar','retificar','remover'],true)) {
            throw new InvalidArgumentException('Operação inválida.');
        }
        if ($now - ($state['last_sent'] ?? 0) < 60) {
            throw new InvalidArgumentException('Aguarde um minuto antes de enviar outra mensagem.');
        }
        if ($action !== 'enviar' && empty($state['reference'])) {
            throw new InvalidArgumentException('Envie uma avaliação nesta sessão antes de solicitar alterações.');
        }
        if ($action === 'remover') {
            if (($input['confirmar_exclusao'] ?? '') !== '1') throw new InvalidArgumentException('Confirme a solicitação de exclusão.');
            $valid = ['comentario'=>'Solicito a exclusão da avaliação identificada pela referência acima.'];
        } else {
            $valid = ReviewService::validate($input);
        }
        $recipient = $config['reviews_to'] ?? 'fluxontesoureirodigital@gmail.com';
        if (!is_string($recipient) || !filter_var($recipient,FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Destinatário de avaliações inválido.');
        }
        $reference = $action === 'enviar' ? 'FLX-' . strtoupper(bin2hex(random_bytes(6))) : $state['reference'];
        $subject = 'Fluxon | ' . ['enviar'=>'Avaliação','retificar'=>'Retificação de avaliação','remover'=>'Exclusão de avaliação'][$action] . ' | ' . $reference;
        $message = EmailTemplate::review($account,$valid,$reference,$action);
        if (!$send($recipient,$subject,$message,$account['email'])) {
            throw new InvalidArgumentException('Não foi possível enviar sua mensagem. Seu formulário foi preservado para tentar novamente.');
        }
        // Somente referência e horário na sessão; nota e comentário não são persistidos.
        $state = ['reference'=>$reference,'last_sent'=>$now];
        return $reference;
    }
}
