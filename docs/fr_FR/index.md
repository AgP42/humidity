Présentation
============

Ce plugin permet de gérer un humidificateur ou un déshumidificateur, non connecté, via une prise connectée et une sonde d'humidité.

Lien vers le code source : [https://github.com/AgP42/humidity/](https://github.com/AgP42/humidity/)

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
* **Consigne** : la consigne d'humidité souhaitée. Cette consigne peut être une valeur fixe ou une commande de type info. Vous pouvez par exemple utiliser un virtuel pour changer la valeur de la consigne via une requête http. Voir les exemples ci-dessous.
* **Hystérésis** : Il s'agit de la valeur de tolérance souhaitée autour de la consigne donnée. Par exemple si votre consigne est à 60% avec un hystérésis de 5%, un humidificateur sera actif jusqu'à 65% puis il ne se réenclenchera qu'en dessous de 55%. Ceci permet de ne pas constamment déclencher/couper l'appareil lorsque la consigne est atteinte. Par défaut l'hystérésis est à 0.
* **Puissance électrique** : Champs facultatif permettant de suivre le fonctionnement effectif de l'appareil et de générer une alerte en conséquence. Un humidificateur n'ayant plus d'eau ou un déshumidificateur dont le réservoir est plein ne consommeront plus autant que lors de leur fonctionnement.
* **Seuil min** : seuil minimum de consommation de l'appareil en fonctionnement nominal. Ce seuil permet de générer l'alerte lorsque la puissance devient inférieure au seuil. Par exemple pour un humidificateur consommant 8W en nominal et 1W en veille (lorsque son réservoir est vide), une bonne valeur est un seuil de 2W. Valeur par défaut : 0W

Onglet **Actions**
---

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/OngletActions.png)

Cet onglet permet de configurer les actions à réaliser pour activer et arrêter votre appareil, ainsi que les actions d'alertes (uniquement disponible si **Puissance électrique** est renseigné).

Pour les actions de type message vous pouvez utiliser les tags suivants : #humidity_name#, #humidity_value# ou #humidity_order#.

Onglet **Commandes**
---

![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/OngletCommandes.png)

Cet onglet vous permet une configuration fine des commandes utilisées par ce plugin grâce à différentes fonctions de core de Jeedom. Vous pouvez notamment configurer l'affichage des infos sur le dashboard.

Widget (Dashboard)
===
Jeedom v4 (avec slider de type "button") :
![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/widgetv4.png)

Jeedom v3 (avec slider de type "thermostat_perso") :
![](https://raw.githubusercontent.com/AgP42/humidity/master/docs/assets/images/widgetv3.png)

Les informations disponibles sont :
* **Capteur humidité** : Valeur du capteur d'humidité (passer la souris dessous vous indiquera la date de la valeur et la date de collecte et cliquer dessous vous affichera l'historique)
* **Puissance** : La puissance courante consommé par votre appareil (si configuré)
* **Changer consigne** : Le slider pour changer la consigne. Si vous avez défini une commande dans la configuration pour changer la consigne, c'est la dernière requête qui sera prise en compte.
* **Etat** : L'état courant de fonctionnement de votre appareil (tel que commandé par le plugin). Si votre humidificateur n'a plus d'eau, son état affiché ici pourra être "Actif" bien qu'il n'est plus en mesure de fonctionner.
* **Off** : Couper l'appareil
* **On** : Lance l'évaluation entre la consigne et la valeur courante et active ou coupe l'appareil au besoin.

Exemples
===

Changer la consigne via un Virtuel
---

Changer la consigne via une variable
---

Changer la consigne via un scenario
---

Piloter son humidificateur/déshumidificateur selon les HP/HC
---

Support
===

* Pour toute demande de support ou d'information : [Forum Jeedom](https://community.jeedom.com/c/plugins/)
* Pour un bug ou une demande d'évolution, merci de passer de préférence par [Github](https://github.com/AgP42/humidity/issues)
