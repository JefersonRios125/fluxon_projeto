<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
    exit("O PHP usado precisa da extensao pdo_pgsql.\n");
}
$config = (require __DIR__ . '/config.php')['db'];
$dsn = $config['dsn'];
if (!preg_match('/^pgsql:host=([^;]+)(?:;|$)/', $dsn, $match)) {
    exit("O formato da conexao nao corresponde ao esperado para este projeto.\n");
}
$host = $match[1];
$port = preg_match('/;port=(\d+)/', $dsn, $match) ? (int)$match[1] : 5432;
function tempo(int $inicio): float {
    return (hrtime(true) - $inicio) / 1000000;
}
function rodada(array $config, string $host, int $port): array {
    $inicio = hrtime(true);
    $pdo = new PDO($config['dsn'].';connect_timeout=5', $config['user'], $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false]);
    $conexao = tempo($inicio);
    $inicio = hrtime(true);
    $pdo->query('SELECT 1')->fetchColumn();
    $consulta = tempo($inicio);
    unset($pdo);
    $inicio = hrtime(true);
    $ips = gethostbynamel($host);
    $dns = tempo($inicio);
    if (!$ips) return [$conexao, $consulta, null, null];
    $inicio = hrtime(true);
    $socket = @stream_socket_client("tcp://{$ips[0]}:$port", $codigo, $mensagem, 5);
    $tcp = tempo($inicio);
    if ($socket) fclose($socket);
    return [$conexao, $consulta, $dns, $socket === false ? null : $tcp];
}
function linha(string $nome, array $valores): void {
    echo str_pad($nome,18);
    foreach ($valores as $valor) echo str_pad($valor === null ? 'n/d' : number_format($valor,1,',','.'),14,' ',STR_PAD_LEFT);
    echo PHP_EOL;
}
echo "Fluxon | tempos em milissegundos\n";
echo "Aberturas independentes usando a configuracao atual. DNS e TCP sao sondagens separadas.\n";
printf("%-18s%14s%14s%14s%14s\n",'Teste','PDO completo','SELECT 1','DNS IPv4','TCP IPv4');
$amostras = [];
for ($i=1; $i<=5; $i++) {
    try {
        $valores = rodada($config,$host,$port);
        $amostras[] = $valores;
        linha("Rodada $i",$valores);
    } catch (PDOException $ex) {
        echo "Falha na conexao ou na consulta. Confira se o Fluxon abre normalmente.\n";
        break;
    }
}
if (count($amostras) === 5) {
    $medianas = [];
    for ($i=0; $i<4; $i++) {
        $coluna = array_column($amostras,$i);
        sort($coluna,SORT_NUMERIC);
        $medianas[] = in_array(null,$coluna,true) ? null : $coluna[2];
    }
    linha('Mediana',$medianas);
}
