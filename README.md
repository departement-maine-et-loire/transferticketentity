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

<!-- Ce plugin est documenté [ici](https://github.com/departement-maine-et-loire/glpi-example-plugin/wiki) -->

## Comment configurer le plugin

Vous pouvez configurer les droits d'accès au plugin dans l'administration des profils

## Compatibilité

Ce plugin a été testé jusqu'à la version de GLPI 10.0.9

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

<!-- Ce plugin est documenté [ici](https://github.com/departement-maine-et-loire/glpi-example-plugin/wiki) -->

## How to set up the plugin

You can configure access rights to the plugin in profile administration

## Compatibility

This plugin has been tested up to GLPI version 10.0.9
