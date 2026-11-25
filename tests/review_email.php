<?php
require __DIR__.'/../app/services/ReviewEmailService.php';
$account=['nome'=>'Pessoa <teste>','email'=>'pessoa@example.invalid'];
$config=['reviews_to'=>'equipe@example.invalid'];
$state=[];$messages=[];
$send=static function($to,$subject,$message,$reply)use(&$messages){$messages[]=[$to,$subject,$message,$reply];return true;};
$assert=static function($condition){if(!$condition)throw new RuntimeException('Falha no fluxo de avaliação por e-mail.');};
$input=['acao'=>'enviar','nota'=>'5','comentario'=>'<script>alert(1)</script>','email'=>'invasor@example.invalid'];
$ref=ReviewEmailService::submit($input,$account,$state,$config,$send,1000);
$assert($messages[0][0]==='equipe@example.invalid' && $messages[0][3]===$account['email']);
$assert(!str_contains($messages[0][2]['html'],'<script>'));
$assert(str_contains($messages[0][2]['html'],'&lt;script&gt;') && str_contains($messages[0][2]['text'],'<script>'));
$assert(array_keys($state)===['reference','last_sent']);
try {ReviewEmailService::submit($input,$account,$state,$config,$send,1001);throw new RuntimeException('Sem limite de envio.');}catch(InvalidArgumentException $e){}
$input['acao']='retificar';
$assert(ReviewEmailService::submit($input,$account,$state,$config,$send,1061)===$ref);
$assert(ReviewEmailService::submit(['acao'=>'remover','confirmar_exclusao'=>'1'],$account,$state,$config,$send,1122)===$ref);
$before=$state;
try {ReviewEmailService::submit(['acao'=>'enviar','nota'=>'4'],$account,$state,$config,static fn()=>false,1183);throw new RuntimeException('Falha SMTP aceita.');}catch(InvalidArgumentException $e){}
$assert($state===$before);
$empty=[];
try {ReviewEmailService::submit(['acao'=>'remover','confirmar_exclusao'=>'1'],$account,$empty,$config,$send,1300);throw new RuntimeException('Referência ausente aceita.');}catch(InvalidArgumentException $e){}
echo "Avaliação por e-mail: destinatário, escape, gerenciamento, limite e falha SMTP aprovados.\n";
