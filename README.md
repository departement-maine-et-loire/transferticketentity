# GLPi Transfer Ticket Entity Plugin (FR)

## Introduction

Ce plugin permet, pour les profils autorisés, de transférer des tickets d'une entité à une autre sur laquelle ils n'ont pas les accès.
Il s'adresse aux organisations qui ont configurés, dans GLPI, leurs périmètres d'activités par entité (DRH, Comptabilité, SI, ...).

L'intérêt étant de pouvoir transférer un ticket entre techniciens "GLPI" d'entités différentes et de continuer à assurer la confidentialité des données entre entités.

Pour exemple : 
Un profil "technicien comptable" a, par défaut, visibilité que sur les tickets de son entité "Comptabilité".
Dès lors, où un des tickets, qui lui est assigné, concerne la DRH, il aura la possibilité de leur transférer avec suivi.
Une fois le transfert effectué, il n'a plus aucune visibilité sur le ticket.

## Documentation

Pour configurer les prérequis du transfert d'entité :

- Autoriser la fonction Transfert : définit si le transfert est autorisé vers l'entité et groupes associés
- Groupe à assigner obligatoire pour effectuer un transfert : 
	- Définit si le ticket transféré doit être obligatoirement assigné à un groupe.
	- Si non, le choix "aucun" apparaîtra dans la liste du groupe cible et sera à sélectionner
	- Si le ticket est envoyé dans une entité sans groupe, il sera considéré comme "nouveau"
- Justification obligatoire pour effectuer un transfert
	- Si oui, le champ de saisi s'affiche avec un encart rouge
	- Si non, ce dernier apparaîtra avec un encart bleu toutefois la zone de saisie restera active si besoin.
- Garder la catégorie après le transfert :
    - Si oui, la catégorie du ticket sera gardé après le transfert.
    - Si non, la catégorie du ticket sera remise à null.

## Où configurer le plugin

Vous pouvez configurer les droits d'accès au plugin dans l'administration des profils.
Les prérequis du transfert se gèrent dans l'administration des entités.

## Compatibilité

Ce plugin a été testé jusqu'à la version de GLPI 10.0.10

# GLPi Transfer Ticket Entity Plugin (EN)

## Introduction

This plugin enables authorized profiles to transfer tickets from one entity to another to which they do not have access.
It is designed for organizations that have configured their GLPI activity perimeters by entity (HR, Accounting, IS, etc.).

The aim is to be able to transfer a ticket between "GLPI" technicians from different entities, and to continue to ensure the confidentiality of data between entities.

For example: 
An "accounting technician" profile has, by default, visibility only on the tickets of its "Accounting" entity.
If one of the tickets assigned to him concerns the HR department, he will be able to transfer it with follow-up.
Once the transfer has been made, he no longer has any visibility of the ticket.

## Documentation

To configure entity transfer prerequisites :

- Allow transfer function : defines whether transfer is allowed to the entity and associated groups
- Assigned group required to make a transfer : 
    - Defines whether the transferred ticket must be assigned to a group.
    - If not, the choice "none" will appear in the target group list and must be selected.
    - If the ticket is sent to an entity without a group, it will be considered "new"
- Justification required to make a transfer :
    - If yes, the input field is displayed with a red highlighting.
    - If no, it will appear with a blue highlighting, but the input field will remain active if required.
- Keep category after transfer :
    - If yes, category's ticket will be keep.
    - If no, category's ticket will be set to null.

## Where set up the plugin

You can configure access rights to the plugin in profile administration.
The transfer prerequisites are managed in the entity administration.

## Compatibility

This plugin has been tested up to GLPI version 10.0.10