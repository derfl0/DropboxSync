<? if ($sync->getClient()): ?>
    Verbunden mit: <?= $sync->getClient()->getAccountInfo()['display_name'] ?><br>
    <a href="<?= $controller->url_for('show/sync') ?>">Dropbox synchronisieren</a><br>
    <a href="<?= $controller->url_for('show/kill') ?>">Dropbox Verknüpfung aufheben</a>
<? else: ?>
    <a href="<?= $authorizeUrl ?>">Dropbox authorisieren</a><br>
    <?= _('Kein Dropboxaccount?') ?>
    <a href="https://db.tt/4fxUZ13J"><?= _('Kostenlos registrieren') ?></a>
<? endif; ?>