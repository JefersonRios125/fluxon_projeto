<?php
require __DIR__.'/../app/services/EmailTemplate.php';
foreach(['access','delete','reset'] as $purpose){
    $value=$purpose==='reset'?'https://example.invalid/index.php?p=redefinir&t=abc':'123456';
    $m=EmailTemplate::security($purpose,$value);
    if(!str_contains($m['text'],$value)||!str_contains($m['html'],'Fluxon')||!str_contains($m['html'],'Não encaminhe'))throw new RuntimeException('Template incompleto.');
}
try{EmailTemplate::security('reset','javascript:alert(1)');throw new RuntimeException('URL inválida aceita.');}catch(InvalidArgumentException $e){}
echo "Templates de segurança e alternativa em texto aprovados.\n";
