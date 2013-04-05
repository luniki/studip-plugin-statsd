<h2>Statsd-Einstellungen</h2>


<? if (isset($flash['info'])) : ?>
<?= MessageBox::info($flash['info']) ?>
<? endif ?>

<? if (!$statsd_active) : ?>
<form action="<?= PluginEngine::getLink("statsdadminplugin/settings") ?>" method="post">

  <fieldset>

    <legend>Wo befindet sich Ihr Statsd?</legend>

    <label>
      <?= _('IP:') ?>
      <input required type="text" name="statsd[ip]" value="<?= htmlReady($settings['ip']) ?>">
    </label>

    <label>
      <?= _('Port:') ?>
      <input required type="text" name="statsd[port]" value="<?= htmlReady($settings['port']) ?>">
    </label>

  </fieldset>

  <fieldset>

   <legend>Unter welchem Präfix wollen Sie die Daten dieses Stud.IPs speichern?</legend>

    <label>
      <?= _('Präfix:') ?>
      <input required type="text" maxlength="5" name="statsd[prefix]" value="<?= htmlReady($settings['prefix']) ?>">
    </label>
  </fieldset>

  <div class="button-group">
    <?= \Studip\Button::createAccept(_("Übernehmen und aktivieren")) ?>
  </div>
 </form>

<? else : ?>

  <dl>
    <dt>Statsd-IP</dt>   <dd><?= htmlReady($settings['ip']) ?></dd>
    <dt>Statsd-Port</dt> <dd><?= htmlReady($settings['port']) ?></dd>
    <dt>Präfix</dt>      <dd><?= htmlReady($settings['prefix']) ?></dd>
  </dl>

  <form action="<?= PluginEngine::getLink("statsdadminplugin/deactivate") ?>" method="post">
    <?= \Studip\Button::createAccept(_("Deaktivieren")) ?>
  </form>

<? endif ?>
