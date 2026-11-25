<?php
final class EmailTemplate
{
    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function message(string $title, string $intro, string $contentHtml, string $plain): array
    {
        $title = self::escape($title);
        $intro = self::escape($intro);
        $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>' .
            '<body style="margin:0;background:#eef3f3;font-family:Arial,Helvetica,sans-serif;color:#24383d">' .
            '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">' .
            '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #d9e4e4">' .
            '<tr><td style="padding:26px 30px;background:#0f3d3e;color:#c8ff3d;font-size:24px;font-weight:bold">FLUXON <span style="font-size:12px;color:#ffffff">Analytics</span></td></tr>' .
            '<tr><td style="padding:30px"><h1 style="font-size:23px;line-height:1.3;margin:0 0 16px;color:#0f3d3e">' . $title . '</h1>' .
            '<p style="font-size:15px;line-height:1.6">' . $intro . '</p>' . $contentHtml .
            '<p style="font-size:14px;line-height:1.6;margin-top:28px">Atenciosamente,<br><strong>Equipe Fluxon Analytics</strong></p></td></tr>' .
            '<tr><td style="padding:20px 30px;background:#f4f7f7;font-size:12px;line-height:1.6;color:#52666b">' .
            'Mensagem automática do Fluxon Analytics. Para suporte, contate fluxontesoureirodigital@gmail.com. Não encaminhe códigos ou links de segurança.</td></tr>' .
            '</table></td></tr></table></body></html>';
        return ['html'=>$html, 'text'=>"Fluxon Analytics\n\n" . html_entity_decode($title, ENT_QUOTES, 'UTF-8') . "\n\n" . html_entity_decode($intro, ENT_QUOTES, 'UTF-8') . "\n\n" . $plain . "\n\nAtenciosamente,\nEquipe Fluxon Analytics\nSuporte: fluxontesoureirodigital@gmail.com"];
    }

    public static function security(string $purpose, string $value): array
    {
        $reset = $purpose === 'reset';
        $delete = $purpose === 'delete';
        $title = $reset ? 'Redefinição de senha' : ($delete ? 'Confirmação de exclusão da conta' : 'Verificação de segurança');
        $intro = $reset ? 'Recebemos uma solicitação para redefinir a senha da sua conta.' :
            ($delete ? 'Recebemos uma solicitação de exclusão da sua conta Fluxon.' : 'Utilize o código abaixo para confirmar seu e-mail ou concluir o acesso à sua conta Fluxon.');
        $instruction = $reset ? 'Abra o link abaixo e cadastre uma nova senha. O link é válido por 1 hora e só pode ser utilizado uma vez.' :
            'Digite o código na tela do Fluxon. Ele é válido por 10 minutos, para uma única confirmação e até 5 tentativas.';
        $warning = $delete ? 'A confirmação remove a conta e os dados vinculados do banco ativo. Se não solicitou a exclusão, não utilize o código e procure o suporte.' :
            'Se você não realizou esta solicitação, ignore esta mensagem. Nunca compartilhe este código ou link, inclusive com pessoas que afirmem representar o Fluxon.';
        if ($reset) {
            $url = parse_url($value);
            if (!is_array($url) || !in_array($url['scheme'] ?? '', ['http','https'], true) || empty($url['host'])) {
                throw new InvalidArgumentException('URL de recuperação inválida.');
            }
            $safe = self::escape($value);
            $content = '<p style="margin:24px 0"><a href="' . $safe . '" style="display:inline-block;padding:14px 22px;background:#0f3d3e;color:#ffffff;text-decoration:none;font-weight:bold">Redefinir minha senha</a></p>' .
                '<p style="font-size:13px;overflow-wrap:anywhere">Se o botão não funcionar, copie e cole este endereço no navegador:<br><a href="' . $safe . '">' . $safe . '</a></p>';
        } else {
            if (!preg_match('/^[0-9]{6}$/D', $value)) throw new InvalidArgumentException('Código inválido.');
            $content = '<p style="padding:22px;background:#edf5f4;border:1px solid #cbdedb;text-align:center;font-size:32px;letter-spacing:6px;font-weight:bold;color:#0f3d3e">' . $value . '</p>';
        }
        return self::message($title,$intro,$content . '<p style="line-height:1.6">' . $instruction . '</p><p style="padding:16px;background:#fff5dd;line-height:1.6">' . $warning . '</p>',
            ($reset ? 'Link de recuperação: ' : 'Código de confirmação: ') . $value . "\n\n" . $instruction . "\n\n" . $warning);
    }

    public static function review(array $account, array $review, string $reference, string $action): array
    {
        $labels = ['enviar'=>'Nova avaliação','retificar'=>'Solicitação de retificação','remover'=>'Solicitação de exclusão de avaliação'];
        $title = $labels[$action];
        $fields = ['Referência'=>$reference,'Usuário'=>$account['nome'],'E-mail de contato'=>$account['email'],
            'Recebido em'=>(new DateTimeImmutable('now',new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y H:i:s P')];
        if ($action !== 'remover') $fields['Nota'] = $review['nota'] . ' de 5 estrelas';
        $html = '<table role="presentation" width="100%" style="border-collapse:collapse">';
        $plain = '';
        foreach ($fields as $key=>$value) {
            $html .= '<tr><th align="left" style="padding:10px;border-bottom:1px solid #d9e4e4">'.self::escape($key).'</th><td style="padding:10px;border-bottom:1px solid #d9e4e4">'.self::escape((string)$value).'</td></tr>';
            $plain .= $key . ': ' . $value . "\n";
        }
        $comment = $review['comentario'] ?? '';
        $html .= '</table><h2 style="font-size:17px">Comentário / solicitação</h2><p style="white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.6">'.nl2br(self::escape($comment ?: 'Não informado.')).'</p>';
        return self::message($title,'Mensagem recebida pelo formulário autenticado de avaliação do Fluxon.',$html,
            $plain . "\nComentário / solicitação:\n" . ($comment ?: 'Não informado.'));
    }
}
