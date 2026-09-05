<!-- methode-cadre:v2 -->
# Méthode CADRE

Ce projet utilise la méthode CADRE. Le kit canonique vit dans `/Users/banelsems/cadre/methode-cadre/` et la mémoire de projet dans `.cadre/`.

Avant tout travail non trivial :

1. Lire `.cadre/status.yaml`, `.cadre/decisions.md` et `.cadre/config.md` lorsqu'ils existent.
2. Choisir le chemin proportionné dans `/Users/banelsems/cadre/methode-cadre/02-CHEMINS.md`.
3. Lire la commande pertinente dans `/Users/banelsems/cadre/methode-cadre/commandes/<commande>.md` et les artefacts qu'elle exige.
4. Ne jamais inventer une décision critique ; poser la question ou utiliser `[OUVERT]` / `[HYPOTHÈSE]`.
5. Ne jamais franchir PORTE 1 ou PORTE 2 sans un `VALIDÉ` humain explicite.
6. Mettre à jour `.cadre/status.yaml` en dernier lorsque la commande l'exige.

Si le projet devient un package Composer/Laravel publié ou réutilisable, lire aussi `/Users/banelsems/cadre/methode-cadre/annexes/bonus-stack-package-composer.md`.

Préserver les modifications utilisateur existantes. Une commande `/ship` ne constitue pas une autorisation implicite de pousser, fusionner, déployer ou publier.
