<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('humidity');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
        <div class="cursor eqLogicAction logoPrimary" data-action="add">
          <i class="fas fa-plus-circle"></i>
          <br>
          <span>{{Ajouter}}</span>
      </div>
        <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
      <br>
      <span>{{Configuration}}</span>
    </div>
    </div>
    <legend><i class="fas fa-tint"></i> {{Mes équipements}}</legend>
  	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <div class="eqLogicThumbnailContainer">
        <?php
    foreach ($eqLogics as $eqLogic) {
    	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
    	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
    	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
    	echo '<br>';
    	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
    	echo '</div>';
    }
    ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>

      <li role="presentation"><a href="#actionstab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-hand-point-right"></i> {{Actions}}</a></li>

      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>

    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

      <!-- TAB equipement -->
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
        <form class="form-horizontal">
          <fieldset>
          <legend><i class="fas fa-tachometer-alt"></i> {{Général}}</legend>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
              </div>
            </div>
            <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                    foreach (jeeObject::all() as $object) {
                    	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                    }
                    ?>
                </select>
              </div>
            </div>

  	        <div class="form-group">
            <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-9">
                <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
              </div>
            </div>

          	<div class="form-group">
          		<label class="col-sm-3 control-label"></label>
          		<div class="col-sm-9">
          			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
          			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
          		</div>
          	</div>

          </fieldset>
        </form>

        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-tint"></i> {{Configuration}}</legend>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Type}}
              </label>
              <div class="col-sm-2">
                <select class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="humidity_type">
                  <option value="humid" selected>Humidificateur</option>
                  <option value="deshumid">Déshumidificateur</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Sonde humidité}}<!-- <sup><i class="fas fa-question-circle tooltips" title="{{}}"></i></sup> --></label>
              <div class="col-sm-4">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="sensor_humidity"/>
                  <span class="input-group-btn">
                    <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                  </span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Consigne}}<sup><i class="fas fa-question-circle tooltips" title="{{Valeur fixe ou une commande de type info (d'un virtuel par exemple) ou une variable (format '#variable(ma_var)#'). Dans tous les cas la consigne peut être modifiée via le dashboard.}}"></i></sup></label>
              <div class="col-sm-2">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="target_humidity"/>
                  <span class="input-group-btn">
                    <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                  </span>
                </div>
              </div>

              <label class="col-sm-1 control-label">{{Hystérésis}}<sup><i class="fas fa-question-circle tooltips" title="{{Seuil de tolérance autour de la consigne choisie. 0 par défaut.}}"></i></sup></label>
              <div class="col-sm-1">
                <div class="input-group">
                  <input type="number" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="hysteresis"/>
                </div>
              </div>

            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Puissance électrique (facultatif)}}<sup><i class="fas fa-question-circle tooltips" title="{{Facultatif - Permet de générer une alerte si l'équipement ne consomme plus (réservoir d'eau vide ou plein), ou s'il consomme trop.}}"></i></sup></label>
              <div class="col-sm-2">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="puissance_elec"/>
                  <span class="input-group-btn">
                    <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                  </span>
                </div>
              </div>

              <label class="col-sm-1 control-label">{{Seuil min}}<sup><i class="fas fa-question-circle tooltips" title="{{Seuil de puissance en dessous duquel l'équipement est considéré arrêté. Min ou max doit être renseigné pour activer l'alerte.}}"></i></sup></label>
              <div class="col-sm-1">
                <div class="input-group">
                  <input type="number" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="seuil_elec"/>
                </div>
              </div>

              <label class="col-sm-1 control-label">{{Seuil max}}<sup><i class="fas fa-question-circle tooltips" title="{{Seuil de puissance au-dessus duquel vous souhaitez recevoir une alerte. Min ou max doit être renseigné pour activer l'alerte.}}"></i></sup></label>
              <div class="col-sm-1">
                <div class="input-group">
                  <input type="number" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="seuil_elec_max"/>
                </div>
              </div>

            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Sonde niveau d'eau (facultatif)}}<sup><i class="fas fa-question-circle tooltips" title="{{Facultatif - Permet de générer une alerte si la sonde détecte un réservoir plein ou vide}}"></i></sup></label>
              <div class="col-sm-2">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="sonde_eau"/>
                  <span class="input-group-btn">
                    <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                  </span>
                </div>
              </div>

              <div class="col-sm-1">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr tooltips" data-l1key="configuration" data-l2key="invert"/>{{Inverser}}<sup><i class="fas fa-question-circle tooltips" title="{{Cochez pour que l'alerte soit générée lorsque le capteur remonte l'état 0.}}"></i></sup></label></span>
              </div>

            </div>

          </fieldset>
        </form>

      </div>

      <!-- TAB actions -->
      <div class="tab-pane" id="actionstab">

        <br/>
<!--         <div class="alert alert-info">
          {{}}
        </div> -->

        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-toggle-on"></i> {{Pour lancer, je dois ?}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions pour activer l'humidificateur ou le déshumidificateur}}"></i></sup>
              <a class="btn btn-success btn-sm addAction" data-type="action_on" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
            </legend>
            <div id="div_action_on"></div>

          </fieldset>
        </form>

        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-toggle-off"></i> {{Pour arrêter, je dois ?}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions pour désactiver l'humidificateur ou le déshumidificateur}}"></i></sup>
              <a class="btn btn-success btn-sm addAction" data-type="action_off" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
            </legend>
            <div id="div_action_off"></div>

          </fieldset>
        </form>

        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-child"></i> {{Pour alerter, je dois ?}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées lorsque le plugin souhaite activer l'appareil mais il ne consomme pas d'électricité. Celà signifie probablement que le réservoir de l'humidificateur est vide, ou celui du déshumidificateur est plein. Voir la doc pour les tags utilisables dans les messages}}"></i></sup>
              <a class="btn btn-success btn-sm addAction" data-type="action_alert" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
            </legend>
            <div id="div_action_alert"></div>

          </fieldset>
        </form>

        <br>

      </div>

      <!-- TAB commandes -->
      <div role="tabpanel" class="tab-pane" id="commandtab">
<!--         <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/> -->
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include_file('desktop', 'humidity', 'js', 'humidity');?>
<?php include_file('core', 'plugin.template', 'js');?>
