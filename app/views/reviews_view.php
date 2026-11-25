<?php
$rating = is_string($_POST['nota'] ?? null) ? $_POST['nota'] : null;
$comment = is_string($_POST['comentario'] ?? null) ? $_POST['comentario'] : '';
ob_start();
?>
<p>Sua nota e seu comentário serão enviados à equipe por e-mail, junto com seu nome e e-mail cadastrado.
    Novas avaliações não são gravadas no banco de dados nem publicadas para outros usuários.</p>
<p>Destinatário: <?= e($C['reviews_to']) ?>. O envio depende do serviço de e-mail configurado.</p>
<form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <label for="review-action">Tipo de mensagem</label>
    <select id="review-action" name="acao">
        <option value="enviar">Nova avaliação</option>
        <?php if ($reference): ?><option value="retificar" <?= ($_POST['acao']??'')==='retificar'?'selected':'' ?>>Retificar a avaliação <?= e($reference) ?></option><?php endif; ?>
    </select>
    <fieldset>
        <legend>Sua nota de 1 a 5 estrelas</legend>
        <div class="review-stars">
            <?php for ($n = 1; $n <= 5; $n++): ?>
            <label class="review-star"><input type="radio" name="nota" value="<?= $n ?>" required <?= (string) $rating ===
(string) $n
    ? 'checked'
    : '' ?>>
                <span class="review-symbol" aria-hidden="true">★</span><span><?= $n ?> <?= $n ===
 1
     ? 'estrela'
     : 'estrelas' ?></span></label>
            <?php endfor; ?>
        </div>
        <p>1 = muito insatisfeito · 5 = muito satisfeito</p>
    </fieldset>
    <label for="review-comment">Comentário (opcional, até 1000 caracteres)</label>
    <textarea id="review-comment" name="comentario" maxlength="1000" rows="5" placeholder="O que funcionou bem? O que pode melhorar?"><?= e(
    $comment,
) ?></textarea>
    <p>Evite incluir dados pessoais, senhas ou códigos de acesso no comentário.</p>
    <button class="btn" type="submit">Enviar por e-mail</button>
</form>
<?php if ($reference): ?>
<p>Referência desta sessão: <strong><?= e($reference) ?></strong>.</p>
<p>Retificar envia uma nova mensagem à equipe; não modifica o e-mail já entregue. Excluir envia uma solicitação para análise, sem remoção automática da caixa de e-mail.</p>
<form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="acao" value="remover">
    <label class="check"><input type="checkbox" name="confirmar_exclusao" value="1" required>Solicito à equipe a exclusão desta avaliação.</label>
    <button class="btn sec" type="submit">Solicitar exclusão por e-mail</button>
</form>
<?php endif; ?>
<p>Guarde sua referência. Após encerrar a sessão, solicite retificação ou exclusão pelo suporte, informando a referência ou seu e-mail cadastrado.</p>
<?php $pageContent = ob_get_clean();
require __DIR__ . '/account_page.php'; ?>
