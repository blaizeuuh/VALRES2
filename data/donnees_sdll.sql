-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mar. 21 juin 2022 à 11:20
-- Version du serveur :  5.7.17
-- Version de PHP :  5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `mdll`
--

--
-- Déchargement des données de la table `categorie_salle`
--

INSERT INTO `categorie_salle` (`id`, `libelle`) VALUES
(1, 'Réunion'),
(2, 'avec équipements'),
(3, 'Amphi');

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id`, `utilisateur_id`, `salle_id`, `date`, `periode`) VALUES
(1, 2, 6, '2022-06-06 00:00:00', 1),
(2, 6, 5, '2022-06-09 00:00:00', 2),
(3, 6, 10, '2022-06-09 00:00:00', 3),
(4, 7, 4, '2022-06-07 00:00:00', 2);

--
-- Déchargement des données de la table `salle`
--

INSERT INTO `salle` (`id`, `salle_nom`, `categorie`) VALUES
(1, 'Daum', 1),
(2, 'Corbin', 1),
(3, 'Baccarat', 1),
(4, 'Longwy', 1),
(5, 'Multimédia', 2),
(6, 'Amphithéâtre', 3),
(7, 'Lamour', 1),
(8, 'Grüber', 1),
(9, 'Majorelle', 1),
(10, 'Salle de restauration', 2),
(11, 'Galerie', 1),
(12, 'Salle informatique', 2),
(13, 'Hall d\'accueil', 2),
(14, 'Gallé', 1);

--
-- Déchargement des données de la table `structure`
--

INSERT INTO `structure` (`id`, `libelle`) VALUES
(1, 'Ligue'),
(2, 'Club sportif'),
(3, 'Comité départemental'),
(4, 'Association'),
(5, 'Lycée'),
(6, 'Collège'),
(7, 'Autres');

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utilisateur_id`, `nom`, `prenom`, `structure_id`, `structure_nom`, `structure_adresse`, `mail`) VALUES
(1, 'BANDILELLA', 'CLEMENT', 1, 'Ligue Volley Ball Lorraine', '30, rue Widric 1er 54600 Villers lès Nancy', 'cbandilella@llgvolleyball.fr'),
(2, 'BIACQUEL', 'VERONIQUE', 1, 'Ligue d\'escrime Lorraine', '5, rue des trois épis 54600 Villers lès Nancy', 'biancquel.veronique23@llgescrime.fr'),
(3, 'SILBERT', 'GILLES', 2, 'Sporting Club Ennery', '48 Rue Marcel Decker, 57365 Ennery', 'sportingclubennery589@gmail.com'),
(4, 'TORTEMANN', 'PIERRE', 4, 'Association Sportive Nancy Lorraine (ASNL)', '30, rue Widric 1er 54600 Villers lès Nancy', 'contactASNL@laposte.fr'),
(5, 'PERNOT', 'LEA', 5, 'Lycée public Frederic Chopin', '39 rue du Sergent Blandan 54000 Nancy', 'lea.pernot@ac-lorraine.fr'),
(6, 'ZUEL', 'STEPHANIE', 7, 'Fives Nordon', '5 Pl. Aimé Morot 54000 Nancy', 'zuel.s@fivesnordon.ue'),
(7, 'LIEVIN', 'NATHAN', 3, 'FFT- COMITE DEPARTEMENTAL DE TENNIS DE MOSELLE ', '42, rue de la commanderie 54840 Sexey les bois', 'lievinn@fft.fr');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
