Proc�dure d'installation du plugin non termin�e.
Proc�dure d'installation de secours:
1. t�l�charger ma version de galette modifi�e (7.8) https://github.com/trinitrotoluene76/galette.git
   t�l�charger la release v1.4 du plugin contenant Galette 7.8 sur https://github.com/trinitrotoluene76/galette-plugin-subcription.git
2. copier coller le tout sur votre serveur
3. installer Galette conform�ment � la proc�dure d�crite sur leur site (dans un navigateur taper l'url IPduserveur/galette/install)
4. n'installer pas le plugin depuis le panneau admin
5. remplacer enti�rement la base de donn�es Galette par bdd_test_sql.sql situ�e dans le sous r�pertoire doc avec phpmyadmin
		d�tails de la bdd:
		admin login admin, mot de passe 0000
		3 adh�rents pr�programm�s:
		pr�sident: login president mdp pr�sident
		responsable de sous groupe: login responsable1 mot de passe: responsable1
		responsable de sous groupe: login responsable2 mot de passe: responsable2
6. enjoy, c'est fini. (Ce plugin est 100% fonctionnel, il est utilis� depuis plus de 2ans dans une association de 450 adherents)

Toute aide est la bienvenue pour finir ce d�veloppement.
Si vous aimez mon travail n'h�sitez pas � m'envoyer un message.

Cdt,
Amaury FROMENT