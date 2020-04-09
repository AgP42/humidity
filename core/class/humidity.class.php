<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

// TODO : listener sur la consigne ?
// Gerer l'ajout de chauffage pour mieux deshumidifier
// ajouter seuils pour ne pas bagoter autour de la consigne
// ajouter seuils detection non-conso

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class humidity extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */

    public static function listenerHumidity($_option) { // fct appelée par le listener du capteur d'humidité ou de la cmd de consigne

      $humidity = humidity::byId($_option['humidity_id']); // on prend l'eqLogic du trigger qui nous a appelé

      log::add('humidity', 'debug', '################ listenerHumidity : ' . $_option['value'] . '% ############');

      // il faut évaluer selon humidité actuelle et consigne si besoin de on ou off
      $humidity->evaluateHumidity();

    }


    public static function sensorConsoElec($_option) { // fct appelée par le listener de la conso elec

      $humidity = humidity::byId($_option['humidity_id']); // on prend l'eqLogic du trigger qui nous a appelé

      log::add('humidity', 'debug', '################ sensorConsoElec : ' . $_option['value'] . 'W ############');

    }

    /*     * *********************Méthodes d'instance************************* */

    public function cmdOnOff($_cmd) { // $_cmd=1 ou 0 selon si cmd recue demande ON ou OFF

      log::add('humidity', 'debug', '################ Humidity ' . $_cmd . ' ############');

      if($_cmd){ // ON demandé

        $this->setCache('etat', 1);
        $this->evaluateHumidity(); // il faut évaluer selon humidité actuelle et consigne si besoin de on ou off

      } else { // OFF demandé, on coupe

        $this->setCache('etat', 0);
        $this->execActions('action_off');

      }

    }

    public function evaluateHumidity() {


      if($this->getCache('etat') && $this->getIsEnable() == 1){ // seulement si on avait demandé ON et eq est actif. Si OFF ou inactif, on fait rien

        log::add('humidity', 'debug', '################ evaluate Humidity ############');

        // On va aller chercher les infos
        $type = $this->getConfiguration('humidity_type'); //'humid' ou 'deshumid' => direct dans la conf

        $order = $this->getCmd(null, 'order');
        if (is_object($order)) {
          $target = $order->execCmd(); // la consigne => via la cmd info qui est actualisée soit via une autre cmd, soit via la conf, soit via le slider du dashboard
        }

        $value = jeedom::evaluateExpression($this->getConfiguration('sensor_humidity')); // valeur courante du capteur humidité

        log::add('humidity', 'debug', 'type : ' . $type . ' - target : ' . $target . ' - value : ' . $value);

        // On a toutes nos infos, on peut lancer la logique

        if($type == 'humid' && $value <= $target){
          $this->execActions('action_on');
        } else if($type == 'humid' && $value > $target){
          $this->execActions('action_off');
        } else if($type == 'deshumid' && $value < $target){
          $this->execActions('action_off');
        } else if($type == 'deshumid' && $value >= $target){
          $this->execActions('action_on');
        }

      }

    }

    public function execActions($_config) { // on donne le type d'action en argument et ca nous execute toute la liste

      log::add('humidity', 'debug', '################ Execution des actions du type ' . $_config . ' pour ' . $this->getName() .  ' ############');

      foreach ($this->getConfiguration($_config) as $action) { // on boucle pour executer toutes les actions définies
        try {
          $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple un scenario un message
          if (isset($action['options'])) {
            $options = $action['options'];
/*            foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
              // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
              $value = str_replace('#senior_name#', $this->getName(), $value);
              $value = str_replace('#sensor_name#', $_sensor_name, $value);
              $value = str_replace('#sensor_type#', $_sensor_type, $value);
              $value = str_replace('#sensor_value#', $_sensor_value, $value);
              $value = str_replace('#low_threshold#', $_seuilBas, $value);
              $options[$key] = str_replace('#high_threshold#', $_seuilHaut, $value);
            }*/
          }
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
          log::add('seniorcare', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
        }
      } //*/

    }

    public function cleanAllListener() {

      log::add('humidity', 'debug', 'Fct cleanAllListener pour : ' . $this->getName());

      $listeners = listener::byClass('humidity'); // on prend tous nos listeners de ce plugin, pour toutes les eqLogic
      foreach ($listeners as $listener) {
        $humidity_id_listener = $listener->getOption()['humidity_id'];

        if($humidity_id_listener == $this->getId()){ // si on correspond au bon eqLogic, on le vire
          $listener->remove();
        }

      }

    }

    public function preInsert() {

    }

    public function postInsert() {

      log::add('humidity', 'info', 'Création de ' . $this->getHumanName());

      $cmd = $this->getCmd(null, 'humidity_on');
      if (!is_object($cmd)) {
        $cmd = new humidityCmd();
        $cmd->setName(__('On', __FILE__));
      }
      $cmd->setLogicalId('humidity_on');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();


      $cmd = $this->getCmd(null, 'humidity_off');
      if (!is_object($cmd)) {
        $cmd = new humidityCmd();
        $cmd->setName(__('Off', __FILE__));
      }
      $cmd->setLogicalId('humidity_off');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

    }

    public function preSave() {

    }

    public function postSave() {

        // creation des cmd à la sauvegarde de l'équipement

        $cmd = $this->getCmd(null, 'sensor_humidity');
        if (!is_object($cmd)) {
          //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
          $cmd = new humidityCmd();
          $cmd->setLogicalId('sensor_humidity');
          $cmd->setIsVisible(1);
          $cmd->setIsHistorized(1);
          $cmd->setEqLogic_id($this->getId());
        }
        //ici apres, jeedom va utiliser ces infos a chaque fois que l'equipement est sauvegardé, si l'utilisateur le change, ces valeurs là re-écraseront les choix utilisateurs.
        $cmd->setConfiguration('historizeMode', 'avg');
        $cmd->setConfiguration('historizeRound', 0);
        $cmd->setName(__('Capteur humidité', __FILE__));
        $cmd->setValue($this->getConfiguration('sensor_humidity'));
        $cmd->setType('info');
        $cmd->setSubType('numeric');
        $cmd->setUnite('%');
        $cmd->save();

        // va chopper la valeur de la commande puis la suivre a chaque changement
        if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
          $cmd->setCollectDate('');
          $cmd->event($cmd->execute());
        }

        // pour la consigne il nous faut une info et une action slider
        // l'info est liée à la cmd donnée en conf, ou initialisée par une valeur fixe en conf ou changé par un slider sur le dashboard
        $order = $this->getCmd(null, 'order');
        if (!is_object($order)) {
          $order = new humidityCmd();
          $order->setIsVisible(0);
          $order->setUnite('%');
          $order->setName(__('Consigne', __FILE__));
          $order->setConfiguration('historizeMode', 'none');
          $order->setIsHistorized(1);
        }
        $order->setEqLogic_id($this->getId());
        $order->setType('info');
        $order->setSubType('numeric');
        $order->setLogicalId('order');
        $order->setValue($this->getConfiguration('target_humidity'));
        $order->setConfiguration('minValue', 0);
        $order->setConfiguration('maxValue', 100);
        $order->save();

        if(!is_numeric($this->getConfiguration('target_humidity'))){ // si notre champs n'est pas direct un nombre, c'est que ca doit etre une cmd, on va l'executer

          // log::add('humidity', 'warning', '$order->execCmd() : ' . $order->execCmd() . ' $order->execute() : ' . $order->execute());

          // va chopper la valeur de la commande puis la suivre a chaque changement
          $order->setCollectDate('');
          $order->event($order->execute());

        } else { // sinon c'est une valeur et on va la prendre telle quelle
          $order->setCollectDate('');
          $order->event($this->getConfiguration('target_humidity'));
        }

        // le slider du dashboard
        $humidity = $this->getCmd(null, 'humidity_target');
        if (!is_object($humidity)) {
          $humidity = new humidityCmd();
          $humidity->setTemplate('dashboard', 'humidity');
          $humidity->setTemplate('mobile', 'humidity');
          $humidity->setUnite('%');
          $humidity->setName(__('Changer Consigne', __FILE__));
          $humidity->setIsVisible(1);
        }
        $humidity->setEqLogic_id($this->getId());
        $humidity->setConfiguration('minValue', 0);
        $humidity->setConfiguration('maxValue', 100);
        $humidity->setType('action');
        $humidity->setSubType('slider');
        $humidity->setLogicalId('humidity_target');
        $humidity->setValue($order->getId());
        $humidity->save();


        if($this->getConfiguration('conso')!=''){ //si on a une commande de conso definie

          $cmd = $this->getCmd(null, 'conso_elec');
          if (!is_object($cmd)) {
            //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
            $cmd = new humidityCmd();
            $cmd->setLogicalId('conso_elec');
            $cmd->setIsVisible(1);
            $cmd->setIsHistorized(1);
            $cmd->setEqLogic_id($this->getId());
          }
          //ici apres, jeedom va utiliser ces infos a chaque fois que l'equipement est sauvegardé, si l'utilisateur le change, ces valeurs là re-écraseront les choix utilisateurs.
          $cmd->setConfiguration('historizeMode', 'avg');
          $cmd->setConfiguration('historizeRound', 0);
          $cmd->setName(__('Consommation', __FILE__));
          $cmd->setValue($this->getConfiguration('conso'));
          $cmd->setType('info');
          $cmd->setSubType('numeric');
          $cmd->setUnite('W');
          $cmd->save();

          // va chopper la valeur de la commande puis la suivre a chaque changement
          if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
            $cmd->setCollectDate('');
            $cmd->event($cmd->execute());
          }

        } else {
          log::add('humidity', 'warning', 'Pas de commande dans le champs consommation électrique');
        }

        // Mise en place des listeners de capteurs pour réagir aux events

        if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners de capteurs

          // un peu de menage dans nos events avant de remettre tout ca en ligne avec la conf actuelle
          $this->cleanAllListener();

          // on boucle dans toutes les cmd existantes
          foreach ($this->getCmd() as $cmd) {

            // on assigne la fonction selon le type de capteur
            if ($cmd->getLogicalId() == 'sensor_humidity' || ($cmd->getLogicalId() == 'order' && !is_numeric($cmd->getValue()))) { // le capteur d'humidité et la consigne si c'est une cmd
              $listenerFunction = 'listenerHumidity';
            } else if ($cmd->getLogicalId() == 'conso_elec') {
              $listenerFunction = 'sensorConsoElec';
            } else {
              continue; // sinon c'est que c'est pas un truc auquel on veut assigner un listener, on passe notre tour
            }

            // on set le listener associée
            $listener = listener::byClassAndFunction('humidity', $listenerFunction, array('humidity_id' => intval($this->getId())));
            if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
              $listener = new listener();
              $listener->setClass('humidity');
              $listener->setFunction($listenerFunction); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
              $listener->setOption(array('humidity_id' => intval($this->getId())));
            }
            $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs definis (en l'occurence ici il n'y en aura qu'1 seul a chaque listener...)

            log::add('humidity', 'debug', 'Listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

            $listener->save();

          } // fin foreach cmd du plugin
        } // fin if eq actif
        else { // notre eq n'est pas actif ou il a ete desactivé, on supprime les listeners s'ils existaient

          $this->cleanAllListener();

        }

        // a la fin du save, on lance l'évaluation selon humidité actuelle et consigne si besoin de on ou off
        $this->evaluateHumidity();


    }

    public function preUpdate() {

    }

    public function postUpdate() {

    }

    public function preRemove() {

      log::add('humidity', 'info', 'Suppression de ' . $this->getHumanName());

      // quand on supprime notre eqLogic, on vire nos listeners associés
      $this->cleanAllListener();

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class humidityCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
      //$this est une cmd ici

      $eqLogic = $this->getEqLogic(); // on recupere l'eqLogic de cette commande

      if ($this->getLogicalId() == 'humidity_on') { // appel de la commande action "on"

        log::add('humidity', 'info', $this->getHumanName() . ' - ON');

        $eqLogic->cmdOnOff(1);

      } else if ($this->getLogicalId() == 'humidity_off') { // appel de la commande action "off"

        log::add('humidity', 'info', $this->getHumanName() . ' - OFF');

        $eqLogic->cmdOnOff(0);

      } else if ($this->getLogicalId() == 'humidity_target') { // appel de la commande action slider dashboard pour la consigne

        log::add('humidity', 'info', $this->getHumanName() . ' - Nouvelle consigne from dashboard : ' . $_options['slider']);

        // on assigne notre nouvelle valeur à la cmd info
        $order = $eqLogic->getCmd(null, 'order');
        if (is_object($order)) {
          $order->setCollectDate('');
          $order->event($_options['slider']);
        }

        // et on lance l'évaluation selon humidité actuelle et consigne si besoin de on ou off
        $eqLogic->evaluateHumidity();

      } else { // sinon c'est un sensor et on veut juste sa valeur

        log::add('humidity', 'debug', 'Fct execute pour : ' . $this->getLogicalId() . $this->getHumanName() . '- valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));

        return jeedom::evaluateExpression($this->getValue());

      }


    }

    /*     * **********************Getteur Setteur*************************** */
}


