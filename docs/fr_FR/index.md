Présentation
============

Ce plugin permet de gérer un humidificateur ou un déshumidificateur, non connecté, via une prise connectée et une sonde d'humidité.

Changelog
==========

Voir le [Changelog](https://agp42.github.io/humidity/fr_FR/changelog)

Seules les modifications ayant un impact fonctionnel sur le plugin sont listées dans le changelog.

Configuration du plugin
========================

Onglet **Equipement**
---

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/OngletEquipement.png)

Configurez votre équipement :
* **Type** : Humidificateur ou déshumidificateur
* **Sonde humidité** : la commande de type info donnant la valeur de l'humidité à proximité de l'appareil
* **Consigne** : la consigne d'humidité souhaitée. Cette consigne peut être une valeur fixe ou une commande de type info (d'un virtuel par exemple) ou une variable (format '#variable(ma_var)#'). Utilisez un virtuel ou une variable pour changer la valeur de la consigne via une requête http de l'API. Dans tous les cas la consigne peut être modifiée via le dashboard. Voir les exemples ci-dessous.
* **Hystérésis** : Il s'agit de la valeur de tolérance souhaitée autour de la consigne donnée. Par exemple si votre consigne est à 60% avec un hystérésis de 5%, un humidificateur sera actif jusqu'à 65% puis il ne se réenclenchera qu'en dessous de 55%. Ceci permet de ne pas constamment déclencher/couper l'appareil lorsque l'humidité de la pièce est proche de la consigne. Par défaut l'hystérésis est à 0.
* **Puissance électrique** : Champs facultatif permettant de suivre le fonctionnement effectif de l'appareil et de générer une alerte en conséquence. Un humidificateur n'ayant plus d'eau ou un déshumidificateur dont le réservoir est plein ne consommeront plus autant que lors de leur fonctionnement nominal.
* **Seuil min** : seuil minimum de consommation de l'appareil en fonctionnement nominal. Ce seuil permet de générer l'alerte lorsque la puissance devient inférieure au seuil. Par exemple pour un humidificateur consommant 8W en nominal et 1W en veille (lorsque son réservoir est vide), une bonne valeur est un seuil de 2W. Valeur par défaut : 0W

Onglet **Actions**
---

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/OngletActions.png)

Cet onglet permet de configurer les actions à réaliser pour activer et arrêter votre appareil, ainsi que les actions d'alertes (uniquement disponible si **Puissance électrique** est renseigné).
Vous pouvez configurer plusieurs actions par état, elles seront réalisées simultanément.

Infos sur les actions :
* Les actions peuvent être le lancement d'un scenario si besoin.
* Sachant qu'un déshumidificateur sera d'autant plus efficace que la température est élevée, vous pouvez choisir de déclencher un chauffage en même temps que votre déshumidificateur.
* Vous pouvez vous envoyer une notification pour mettre à jour un widget android, via Tasker, à chaque changement d'état par exemple.
* Pour les actions de type message vous pouvez utiliser les tags suivants :
   * #humidity_name# => le nom de l'équipement donné dans l'onglet "Equipement"
   * #humidity_value# => la valeur courante de l'humidité dans la pièce
   * #humidity_order# => la consigne en cours
   * #humidity_state# => l'état de votre appareil ('Allumé' ou 'Eteint')
   * #humidity_mode# => le mode courant ('On' ou 'Off')

Onglet **Commandes**
---

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/OngletCommandes.png)

Cet onglet vous permet une configuration fine des commandes utilisées par ce plugin grâce aux différentes fonctions du core de Jeedom. Vous pouvez notamment configurer l'affichage des infos sur le dashboard.

Widget (Dashboard)
===
Jeedom v4 (avec slider de type "button") :

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/widgetv4.png)

Jeedom v3 (avec slider de type "thermostat_perso", sans la puissance consommée) :

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/widgetv3.png)

Les informations disponibles sont :
* **Capteur humidité** : Valeur du capteur d'humidité (passer la souris dessus vous indiquera la date de la valeur et la date de collecte et cliquer dessus vous affichera l'historique, comme pour toute info jeedom)
* **Puissance** : La puissance courante consommé par votre appareil (si configurée)
* **Changer consigne** : Le slider pour changer la consigne. Si vous avez défini une commande ou une variable dans la configuration pour changer la consigne, c'est la dernière requête qui sera prise en compte. La consigne commandée via une commande ou variable sera immédiatement actualisée sur le widget.
* **Etat** : L'état courant de fonctionnement de votre appareil (tel que commandé par le plugin). Si votre humidificateur n'a plus d'eau, son état affiché ici pourra être "Actif" bien qu'il n'est plus en mesure de fonctionner.
* **Off** : Couper l'appareil
* **On** : Lance l'évaluation entre la consigne et la valeur courante et active ou coupe l'appareil au besoin.

Exemples
===

Changer la consigne
---

Pour toutes les méthodes décrites ci-dessous, un changement de la consigne sera pris en compte immédiatement.

Le mode (On/Off) n'est pas modifié à la réception d'une nouvelle valeur de consigne. (Ainsi si le plugin était sur "Off", il ne passe pas sur "On" à la réeption d'une nouvelle valeur de consigne)

### via une variable (et appel API)

Créer une variable (Outils/Variables/Ajouter).

Dans le champs "Consigne" du plugin, utiliser cette variable, sans oublier de l'entourer par des '#'. Exemple : '#variable(consigne_humidity)#'.

Toute modification de la variable sera immédiatement prise en compte par le plugin.

Vous pouvez modifier la valeur par un appel API http sous le format suivant (voir [doc API HTTP](https://jeedom.github.io/core/fr_FR/api_http#tocAnchor-1-9)) :
* http://#IP_JEEDOM#/jeedom/core/api/jeeApi.php?apikey=#APIKEY#&type=variable&name=#NAME#&value=VALUE

### via un Virtuel (et appel API)

Créez un virtuel avec une commande de type info :

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/consigne_virtuel.png)

Dans le champs "Consigne" du plugin, utilisez cette commande (via le bouton de sélection de commandes).

Vous pouvez modifier la valeur par un appel API http avec l'une des commandes suivantes (voir la [documentation du plugin Virtuel](https://jeedom.github.io/plugin-virtual/fr_FR/)) :
* http://#IP_JEEDOM#/core/api/jeeApi.php?apikey=#VIRTUAL_APIKEY#&type=virtual&id=#CMD_ID#&value=#VOTRE_CONSIGNE#
* http://#IP_JEEDOM#/core/api/jeeApi.php?plugin=virtual&apikey=#VIRTUAL_APIKEY#&type=virtual&id=#CMD_ID#&value=#VOTRE_CONSIGNE#

### via un scenario

Appelez la commande "Changer consigne" via un scenario avec la valeur voulue.

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/consigne_scenario.png)


Piloter son humidificateur/déshumidificateur (On/Off)
---

### selon les HP/HC (via un scenario)

Dans un scénario déclenché par le changement d'état HP/HC :

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/hphc_scenario.png)

### Appel API

Dans l'onglet "Commandes" du plugin, cliquer sur le bouton de configuration de la commande "On" ou "Off" puis sur "URL directe". Utiliser cet URL via l'extérieur pour appeller la commande On ou Off. Par exemple via IFTTT ou via Tasker (Android) ou n'importe quel autre service.

### via d'autres plugins

Vous pouvez notamment utiliser les plugin suivants pour commander votre appareil selon divers criteres :

* [Mode](https://jeedom.github.io/plugin-mode/fr_FR/)
* [Agenda](https://jeedom.github.io/plugin-calendar/fr_FR/)
* [Presence](https://ticed35.github.io/jeedom-presence-doc/fr_FR/)

Ou même le lier à votre chauffage via le plugin [Thermostat](https://jeedom.github.io/plugin-thermostat/fr_FR/), sachant qu'un déshumidificateur sera d'autant plus efficace que la température est élevée.

Support
===

Pour toute demande de support, d'information, remonter un bug ou faire une demande d'évolution : [Forum Jeedom](https://community.jeedom.com/c/plugins/wellness/51)
