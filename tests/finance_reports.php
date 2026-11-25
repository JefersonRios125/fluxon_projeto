<?php
require_once __DIR__.'/../app/services/FinanceReport.php';
$target=$argv[1]??sys_get_temp_dir();
$rows=[];
for($i=0;$i<65;$i++){
    $rows[]=['occurred_on'=>'2026-11-10','description'=>$i===0?'=SUM(A1:A2) descrição com ação (teste)':'Serviço de manutenção '.$i,
        'account'=>'Conta demonstração','category'=>'Serviços','kind'=>$i%2?'saida':'entrada','reversed_at'=>null,'amount_cents'=>12345+$i];
}
$in=$out=0;
foreach($rows as $row){if($row['kind']==='entrada')$in+=$row['amount_cents'];else $out+=$row['amount_cents'];}
$meta=['date'=>'10/11/2026 10:00 -03','filters'=>'Filtros: conta Demonstração; categoria Serviços; período 01/11/2026 a 10/11/2026; situação todos.',
    'totals'=>'Registros: 65 | Entradas: '.FinanceReport::money($in).' | Saídas: '.FinanceReport::money($out).' | Resultado: '.FinanceReport::money($in-$out)];
file_put_contents($target.'/report.pdf',FinanceReport::pdf($rows,$meta));
file_put_contents($target.'/report.xlsx',FinanceReport::xlsx($rows,$meta));
$meta['totals']='Registros: 0 | Entradas: R$ 0,00 | Saídas: R$ 0,00 | Resultado: R$ 0,00';
file_put_contents($target.'/empty.pdf',FinanceReport::pdf([],$meta));
file_put_contents($target.'/empty.xlsx',FinanceReport::xlsx([],$meta));
echo "Relatórios de teste gerados.\n";
