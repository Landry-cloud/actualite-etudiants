-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 18 déc. 2025 à 05:20
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `leader`
--

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `post_id`, `utilisateur_id`, `contenu`, `created_at`) VALUES
(1, 5, 13, 'de aona', '2025-12-12 03:55:53'),
(2, 6, 13, 'gg', '2025-12-12 03:56:54'),
(3, 7, 12, 'gd', '2025-12-12 03:57:55'),
(4, 7, 13, 'fash', '2025-12-12 04:49:51');

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `post`
--

INSERT INTO `post` (`id`, `utilisateur_id`, `contenu`, `created_at`) VALUES
(3, 11, 'Bonjour les jeunes 😎,est ce que vous-êtes prêts pour une année universitaire de ouf au sein de notre institut ISFPS Leader!!!!!\r\n\r\nVoici votre salle :\r\n-Pour les L1 ,vous êtes affectés dans la grande salle au deuxième étage.\r\n-Pour les L2 (informatique) ,vous êtes affectés au petite salle aussi au deuxième étage à côté du bureau de 300 .', '2025-12-07 14:32:03'),
(4, 11, 'fjdjrfjkjg', '2025-12-08 05:55:58'),
(5, 11, 'FILAZANA ,ANTANANARIVO faha 10 ny volana Desambra 2025; ho anareo izay mbola tsy nahavoaloha ny frais de formation dia iangaviana isika mba hanefa izany @ ity herinandro ity mba tsy hisian\'ny fanelingelenana anareo mandritra ny fialan-tsasatra manomboka ny 23 desambra hatramin\'ny 26 desambra , 😂vazivazy ihany izany fa hatramin\'ny 08 janoary 2026;\r\nMASINA NY TANINDRAZANA.', '2025-12-10 04:07:43'),
(6, 11, 'gg', '2025-12-10 05:16:17'),
(7, 11, 'dea aona', '2025-12-12 03:54:44'),
(8, 11, 'eto', '2025-12-12 04:24:40');

-- --------------------------------------------------------

--
-- Structure de la table `reaction`
--

CREATE TABLE `reaction` (
  `id` int(11) NOT NULL,
  `entite_type` enum('post','commentaire') NOT NULL,
  `entite_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type_reaction` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reaction`
--

INSERT INTO `reaction` (`id`, `entite_type`, `entite_id`, `utilisateur_id`, `type_reaction`, `created_at`) VALUES
(3, 'post', 3, 12, 'haha', '2025-12-07 14:32:56'),
(4, 'post', 4, 12, 'love', '2025-12-08 06:15:05'),
(5, 'post', 5, 12, 'like', '2025-12-10 04:08:32'),
(6, 'post', 5, 13, '👍', '2025-12-10 05:07:24'),
(7, 'post', 3, 13, 'haha', '2025-12-10 05:31:26'),
(8, 'post', 6, 13, '👍', '2025-12-10 07:01:08'),
(9, 'post', 5, 14, 'like', '2025-12-10 07:46:35'),
(11, 'post', 7, 13, '👍', '2025-12-12 03:55:31'),
(13, 'post', 7, 12, '😡', '2025-12-12 03:58:15'),
(14, 'post', 8, 13, 'like', '2025-12-12 04:49:11');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `role` enum('admin','etudiant') NOT NULL DEFAULT 'etudiant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `email`, `motdepasse`, `role`, `created_at`) VALUES
(11, 'RAVALISON Fitiavana Landry', 'landryravalison67@gmail.com', '$2y$10$9lGFDWBoKNmyLvz6BAy3t.my4T221SQTlaCzeXNyPAN7zhnv6/iV2', 'admin', '2025-12-07 14:18:33'),
(12, 'etudiant', 'etudiant@gmail.com', '$2y$10$HJ0U67t6oU2sYqaSCEwZHOe7AIf3l3OMAr4PDTCzCpCxp7SM5/VMK', 'etudiant', '2025-12-07 14:23:33'),
(13, 'RAKOTO Basile manana trosa', 'basilekely@gmail.com', '$2y$10$G7rnBXDZAcIrCWtC.Fo70OD6mYoCF5ttzNNBgdI28CeqXaETpD0au', 'etudiant', '2025-12-10 05:06:52');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `reaction`
--
ALTER TABLE `reaction`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_reaction` (`entite_type`,`entite_id`,`utilisateur_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `reaction`
--
ALTER TABLE `reaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
