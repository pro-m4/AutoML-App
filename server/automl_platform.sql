-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 29 Απρ 2026 στις 09:24:24
-- Έκδοση διακομιστή: 10.4.32-MariaDB
-- Έκδοση PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `automl_platform`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `datasets`
--

CREATE TABLE `datasets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `dataset_id` int(11) DEFAULT NULL,
  `dataset_path` varchar(255) DEFAULT NULL,
  `target_column` varchar(100) DEFAULT NULL,
  `selected_features` text DEFAULT NULL,
  `selected_frameworks` text DEFAULT NULL,
  `time_limit` int(11) DEFAULT 60,
  `sample_size` int(11) DEFAULT NULL,
  `metric` varchar(50) DEFAULT NULL,
  `task_type` varchar(50) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `results_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_notified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `trained_models`
--

CREATE TABLE `trained_models` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dataset_id` int(11) DEFAULT NULL,
  `dataset_name` varchar(255) NOT NULL,
  `target_column` varchar(100) NOT NULL,
  `framework` varchar(50) NOT NULL,
  `algorithm` varchar(100) DEFAULT NULL,
  `task_type` varchar(50) NOT NULL,
  `metric_used` varchar(50) NOT NULL,
  `score` decimal(10,6) NOT NULL,
  `model_path` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `email_verif` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `password`, `created_at`, `verification_token`, `is_active`, `email_verif`) VALUES
(16, 'test', 'test1', 'prodromos-4-@hotmail.gr', '$2y$10$tQDzJJGZIbqTgDWUpFdkc.bYaKPfAU6y0cJfj8dFMNjt39NeHJGZe', '2026-04-02 17:03:57', NULL, 0, 1),
(18, 'test2', 'test2', 'prodromosbezyrides@gmail.com', '$2y$10$t/3PWEITjTO/oOgx2e/kmOOxWT7JZM/1AH9Y/jJNAmGOS8wiVV1VS', '2026-04-16 19:42:40', NULL, 0, 0),
(25, 'Pro', 'Bezy', 'bezypro@gmail.com', '$2y$10$zkN3/Zqo43tTenA/6EhRme2z8T0J7Aa.t42rSYMM4JkG4wlRuyuem', '2026-04-22 13:28:31', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `user_predictions`
--

CREATE TABLE `user_predictions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `model_id` varchar(255) DEFAULT NULL,
  `input_file` varchar(255) NOT NULL,
  `output_file` varchar(255) NOT NULL,
  `framework` varchar(50) DEFAULT NULL,
  `metrics` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `user_predictions`
--

INSERT INTO `user_predictions` (`id`, `user_id`, `model_id`, `input_file`, `output_file`, `framework`, `metrics`, `created_at`) VALUES
(19, 16, 'model_job_125.joblib', 'iris2.csv', 'pred_1776639551.csv', 'flaml', NULL, '2026-04-19 22:59:17'),
(20, 16, 'model_job_125.joblib', 'iris.csv', 'pred_1776639566.csv', 'flaml', NULL, '2026-04-19 22:59:33'),
(21, 16, 'model_job_115.joblib', 'Boston.csv', 'pred_1776639625.csv', 'flaml', NULL, '2026-04-19 23:00:31'),
(22, 16, 'model_job_126.zip', 'iris2.csv', 'pred_1776671552.csv', 'mljar', NULL, '2026-04-20 07:52:39'),
(23, 16, 'model_job_126.zip', 'iris.csv', 'pred_1776671626.csv', 'mljar', NULL, '2026-04-20 07:53:54'),
(24, 25, 'model_job_127.zip', 'iris.csv', 'pred_1776900122.csv', 'h2o', NULL, '2026-04-22 23:22:40'),
(25, 25, 'model_job_136.zip', 'iris.csv', 'pred_1776944525.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-23 11:42:12'),
(26, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1776944560.csv', 'mljar', '[]', '2026-04-23 11:42:46'),
(27, 25, 'model_job_136.zip', 'iris.csv', 'pred_1776944576.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-23 11:43:02'),
(28, 25, 'model_job_136.zip', 'iris.csv', 'pred_1776944630.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-23 11:43:58'),
(29, 25, 'model_job_136.zip', 'iris.csv', 'pred_1776945028.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-23 11:50:35'),
(30, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1776945241.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-23 11:54:07'),
(31, 25, 'model_job_137.zip', 'iris.csv', 'pred_1776945468.csv', 'h2o', '{\"type\":\"classification\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98,\"Precision\":0.9801,\"Recall\":0.98}', '2026-04-23 11:58:24'),
(32, 25, 'model_job_136.zip', 'iris.csv', 'pred_1776945660.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-23 12:01:06'),
(33, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1776945824.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-23 12:03:51'),
(34, 25, 'model_job_138.joblib', 'iris2.csv', 'pred_1776945967.csv', 'flaml', '[]', '2026-04-23 12:06:13'),
(35, 25, 'model_job_137.zip', 'iris.csv', 'pred_1776946042.csv', 'h2o', '{\"type\":\"classification\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98,\"Precision\":0.9801,\"Recall\":0.98}', '2026-04-23 12:07:57'),
(36, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777113402.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-25 10:37:11'),
(37, 25, 'model_job_156.joblib', 'iris.csv', 'pred_1777372622.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0,\"Precision\":0,\"Recall\":0}', '2026-04-28 10:37:09'),
(38, 25, 'model_job_156.joblib', 'iris - Αντιγραφή.csv', 'pred_1777373023.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0,\"Precision\":0,\"Recall\":0}', '2026-04-28 10:43:49'),
(39, 25, 'model_job_156.joblib', 'iris2.csv', 'pred_1777373040.csv', 'flaml', '[]', '2026-04-28 10:44:07'),
(40, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777373144.csv', 'flaml', '[]', '2026-04-28 10:45:50'),
(41, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777373966.csv', 'flaml', '[]', '2026-04-28 10:59:33'),
(42, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777374251.csv', 'flaml', '[]', '2026-04-28 11:04:18'),
(43, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777374285.csv', 'flaml', '[]', '2026-04-28 11:04:51'),
(44, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777374315.csv', 'flaml', '[]', '2026-04-28 11:05:23'),
(45, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777374338.csv', 'flaml', '[]', '2026-04-28 11:05:44'),
(46, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777374734.csv', 'flaml', '[]', '2026-04-28 11:12:21'),
(47, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777374898.csv', 'flaml', '[]', '2026-04-28 11:15:04'),
(48, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777375567.csv', 'flaml', '{\"type\":\"regression\",\"RMSE\":1.6155,\"R2 Score\":0.9691}', '2026-04-28 11:26:12'),
(49, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777375589.csv', 'flaml', '[]', '2026-04-28 11:26:34'),
(50, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777375621.csv', 'flaml', '{\"type\":\"regression\",\"RMSE\":1.6155,\"R2 Score\":0.9691}', '2026-04-28 11:27:06'),
(51, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777375698.csv', 'flaml', '{\"type\":\"regression\",\"RMSE\":1.6155,\"MSE\":2.6099,\"MAE\":1.2506,\"R2 Score\":0.9691}', '2026-04-28 11:28:23'),
(52, 25, 'model_job_145.joblib', 'iris2.csv', 'pred_1777375751.csv', 'flaml', '[]', '2026-04-28 11:29:16'),
(53, 25, 'model_job_138.joblib', 'iris2.csv', 'pred_1777375828.csv', 'flaml', '[]', '2026-04-28 11:30:34'),
(54, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777375886.csv', 'flaml', '[]', '2026-04-28 11:31:31'),
(55, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777375959.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0}', '2026-04-28 11:32:44'),
(56, 25, 'model_job_141.joblib', 'iris.csv', 'pred_1777376041.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-28 11:34:09'),
(57, 25, 'model_job_141.joblib', 'iris.csv', 'pred_1777376242.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0}', '2026-04-28 11:37:30'),
(58, 25, 'model_job_141.joblib', 'iris.csv', 'pred_1777376363.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1\":0}', '2026-04-28 11:39:28'),
(59, 25, 'model_job_141.joblib', 'iris2.csv', 'pred_1777376380.csv', 'flaml', '[]', '2026-04-28 11:39:46'),
(60, 25, 'model_job_141.joblib', 'iris2.csv', 'pred_1777376435.csv', 'flaml', '[]', '2026-04-28 11:40:41'),
(61, 25, 'model_job_141.joblib', 'iris2.csv', 'pred_1777376519.csv', 'flaml', '[]', '2026-04-28 11:42:07'),
(62, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777376722.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-28 11:45:31'),
(63, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777376813.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733}', '2026-04-28 11:46:59'),
(64, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777377299.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733}', '2026-04-28 11:55:08'),
(65, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777377338.csv', 'flaml', '{\"type\":\"regression\",\"RMSE\":1.6155,\"R2\":0.9691}', '2026-04-28 11:55:46'),
(66, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777377393.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667}', '2026-04-28 11:56:40'),
(67, 25, 'model_job_137.zip', 'iris.csv', 'pred_1777377409.csv', 'h2o', '{\"type\":\"classification\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98}', '2026-04-28 11:57:37'),
(68, 25, 'model_job_145.joblib', 'iris2.csv', 'pred_1777378018.csv', 'flaml', '[]', '2026-04-28 12:07:04'),
(69, 25, 'model_job_145.joblib', 'iris.csv', 'pred_1777378035.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\"}', '2026-04-28 12:07:20'),
(70, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777378047.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:07:35'),
(71, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777378061.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:07:48'),
(72, 25, 'model_job_136.zip', 'Boston.csv', 'pred_1777378394.csv', 'mljar', '{\"type\":\"regression\",\"RMSE\":9.3219,\"R2\":-0.1768}', '2026-04-28 12:13:27'),
(73, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777378465.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:14:39'),
(74, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1777378486.csv', 'mljar', '{\"type\":\"regression\",\"RMSE\":5.9013,\"R2\":-50.1298}', '2026-04-28 12:14:52'),
(75, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1777378551.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:15:58'),
(76, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777378572.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:16:18'),
(77, 25, 'model_job_138.joblib', 'iris.csv', 'pred_1777378592.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"97.33%\"}', '2026-04-28 12:16:37'),
(78, 25, 'model_job_138.joblib', 'iris2.csv', 'pred_1777378602.csv', 'flaml', '[]', '2026-04-28 12:16:47'),
(79, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1777378612.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:16:58'),
(80, 25, 'model_job_157.joblib', 'Boston2.csv', 'pred_1777378633.csv', 'flaml', '[]', '2026-04-28 12:17:18'),
(81, 25, 'model_job_157.joblib', 'Boston.csv', 'pred_1777378666.csv', 'flaml', '{\"type\":\"regression\",\"RMSE\":579916177810632.5,\"R2\":-3.983706958425313e+27}', '2026-04-28 12:17:51'),
(82, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777378744.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:19:12'),
(83, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1777378758.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:19:25'),
(84, 25, 'model_job_149.zip', 'Boston2.csv', 'pred_1777378786.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:19:53'),
(85, 25, 'model_job_149.zip', 'Boston.csv', 'pred_1777378799.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:20:06'),
(86, 25, 'model_job_149.zip', 'Boston.csv', 'pred_1777378944.csv', 'mljar', '[]', '2026-04-28 12:22:38'),
(87, 25, 'model_job_149.zip', 'Boston2.csv', 'pred_1777378967.csv', 'mljar', '[]', '2026-04-28 12:22:53'),
(88, 25, 'model_job_155.joblib', 'iris.csv', 'pred_1777378985.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\"}', '2026-04-28 12:23:11'),
(89, 25, 'model_job_155.joblib', 'iris2.csv', 'pred_1777379001.csv', 'flaml', '[]', '2026-04-28 12:23:27'),
(90, 25, 'model_job_136.zip', 'iris2.csv', 'pred_1777379211.csv', 'mljar', '[]', '2026-04-28 12:26:58'),
(91, 25, 'model_job_136.zip', 'iris.csv', 'pred_1777379225.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667}', '2026-04-28 12:27:11'),
(92, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777379478.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\"}', '2026-04-28 12:31:24'),
(93, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777379699.csv', 'mljar', '[]', '2026-04-28 12:35:10'),
(94, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777379720.csv', 'flaml', '[]', '2026-04-28 12:35:25'),
(95, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777379737.csv', 'flaml', '{\"info\":\"Metrics calculation skipped\"}', '2026-04-28 12:35:42'),
(96, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777379752.csv', 'mljar', '{\"info\":\"Metrics calculation skipped\"}', '2026-04-28 12:35:59'),
(97, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777379803.csv', 'mljar', '{\"info\":\"Metrics calculation skipped\"}', '2026-04-28 12:36:50'),
(98, 25, 'model_job_159.zip', 'iris2.csv', 'pred_1777379840.csv', 'mljar', '[]', '2026-04-28 12:37:27'),
(99, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777379930.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667}', '2026-04-28 12:38:56'),
(100, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777379943.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0}', '2026-04-28 12:39:08'),
(101, 25, 'model_job_160.zip', 'iris.csv', 'pred_1777379953.csv', 'h2o', '{\"type\":\"classification\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98}', '2026-04-28 12:39:45'),
(102, 25, 'model_job_160.zip', 'iris.csv', 'pred_1777379994.csv', 'h2o', '{\"type\":\"classification\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98}', '2026-04-28 12:40:29'),
(103, 25, 'model_job_159.zip', 'iris.csv', 'pred_1777380035.csv', 'mljar', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667}', '2026-04-28 12:40:43'),
(104, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777380057.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0}', '2026-04-28 12:41:03'),
(105, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777380221.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0}', '2026-04-28 12:43:46'),
(106, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777380439.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0,\"Precision\":0,\"Recall\":0}', '2026-04-28 12:47:27'),
(107, 25, 'model_job_158.joblib', 'iris.csv', 'pred_1777380464.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"0.0%\",\"F1-Score\":0,\"Precision\":0,\"Recall\":0}', '2026-04-28 12:47:50'),
(108, 25, 'model_job_167.joblib', 'iris.csv', 'pred_1777381080.csv', 'flaml', '[]', '2026-04-28 12:58:05'),
(109, 25, 'model_job_167.joblib', 'iris2.csv', 'pred_1777381093.csv', 'flaml', '[]', '2026-04-28 12:58:17'),
(110, 25, 'model_job_170.zip', 'iris.csv', 'pred_1777381703.csv', 'mljar', '[]', '2026-04-28 13:08:30'),
(111, 25, 'model_job_170.zip', 'iris.csv', 'pred_1777381753.csv', 'mljar', '[]', '2026-04-28 13:09:20'),
(112, 25, 'model_job_173.zip', 'iris2.csv', 'pred_1777382072.csv', 'mljar', '[]', '2026-04-28 13:14:40'),
(113, 25, 'model_job_174.zip', 'Boston.csv', 'pred_1777382358.csv', 'mljar', '[]', '2026-04-28 13:19:28'),
(114, 25, 'model_job_174.zip', 'Boston2.csv', 'pred_1777382378.csv', 'mljar', '[]', '2026-04-28 13:19:44'),
(115, 25, 'model_job_167.joblib', 'iris.csv', 'pred_1777382589.csv', 'flaml', '{\"type\":\"classification\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-28 13:23:15'),
(116, 25, 'model_job_167.joblib', 'iris2.csv', 'pred_1777382602.csv', 'flaml', '[]', '2026-04-28 13:23:28'),
(117, 25, 'model_job_170.zip', 'iris2.csv', 'pred_1777382632.csv', 'mljar', '[]', '2026-04-28 13:23:58'),
(118, 25, 'model_job_174.zip', 'Boston.csv', 'pred_1777382774.csv', 'mljar', '[]', '2026-04-28 13:26:22'),
(119, 25, 'model_job_178.joblib', 'Boston.csv', 'pred_1777383252.csv', 'flaml', '{\"type\":\"regression\",\"target_detected\":\"medv\",\"RMSE\":1.1532,\"MSE\":1.33,\"MAE\":0.8498,\"R2 Score\":0.9842}', '2026-04-28 13:34:20'),
(120, 25, 'model_job_179.joblib', 'iris2.csv', 'pred_1777383344.csv', 'flaml', '[]', '2026-04-28 13:35:50'),
(121, 25, 'model_job_179.joblib', 'iris.csv', 'pred_1777383356.csv', 'flaml', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-28 13:36:03'),
(122, 25, 'model_job_180.zip', 'iris.csv', 'pred_1777384723.csv', 'mljar', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-28 13:58:49'),
(123, 25, 'model_job_179.joblib', 'iris2.csv', 'pred_1777384987.csv', 'flaml', '[]', '2026-04-28 14:03:13'),
(124, 25, 'model_job_180.zip', 'iris2.csv', 'pred_1777385090.csv', 'mljar', '[]', '2026-04-28 14:04:56'),
(125, 25, 'model_job_180.zip', 'iris.csv', 'pred_1777385105.csv', 'mljar', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"96.67%\",\"F1-Score\":0.9667,\"Precision\":0.9668,\"Recall\":0.9667}', '2026-04-28 14:05:10'),
(126, 25, 'model_job_178.joblib', 'iris.csv', 'pred_1777385118.csv', 'flaml', '[]', '2026-04-28 14:05:24'),
(127, 25, 'model_job_178.joblib', 'Boston.csv', 'pred_1777385142.csv', 'flaml', '{\"type\":\"regression\",\"target_detected\":\"medv\",\"RMSE\":1.1532,\"MSE\":1.33,\"MAE\":0.8498,\"R2 Score\":0.9842}', '2026-04-28 14:05:50'),
(128, 25, 'model_job_181.zip', 'Boston.csv', 'pred_1777385241.csv', 'mljar', '{\"type\":\"regression\",\"target_detected\":\"medv\",\"RMSE\":3.3294,\"MSE\":11.0852,\"MAE\":2.3998,\"R2 Score\":0.8687}', '2026-04-28 14:07:30'),
(129, 25, 'model_job_182.zip', 'iris.csv', 'pred_1777385438.csv', 'h2o', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98,\"Precision\":0.9801,\"Recall\":0.98}', '2026-04-28 14:11:15'),
(130, 25, 'model_job_182.zip', 'iris2.csv', 'pred_1777385486.csv', 'h2o', '[]', '2026-04-28 14:12:12'),
(131, 25, 'model_job_182.zip', 'iris.csv', 'pred_1777386490.csv', 'h2o', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"98.0%\",\"F1-Score\":0.98,\"Precision\":0.9801,\"Recall\":0.98}', '2026-04-28 14:28:56'),
(132, 25, 'model_job_187.zip', 'Boston.csv', 'pred_1777387378.csv', 'h2o', '{\"type\":\"regression\",\"target_detected\":\"medv\",\"RMSE\":1.4979,\"MSE\":2.2437,\"MAE\":1.009,\"R2 Score\":0.9734}', '2026-04-28 14:43:34'),
(133, 25, 'model_job_187.zip', 'Boston2.csv', 'pred_1777387424.csv', 'h2o', '[]', '2026-04-28 14:44:30'),
(134, 25, 'model_job_196.joblib', 'iris.csv', 'pred_1777402608.csv', 'flaml', '{\"type\":\"classification\",\"target_detected\":\"class\",\"Accuracy\":\"97.33%\",\"F1-Score\":0.9733,\"Precision\":0.9738,\"Recall\":0.9733}', '2026-04-28 18:56:56'),
(135, 25, 'model_job_207.zip', 'Boston.csv', 'pred_1777405070.csv', 'h2o', '{\"type\":\"regression\",\"target_detected\":\"medv\",\"RMSE\":1.0134,\"MSE\":1.027,\"MAE\":0.7499,\"R2 Score\":0.9878}', '2026-04-28 19:38:27'),
(136, 25, 'model_job_210.zip', 'iris.csv', 'pred_1777405141.csv', 'h2o', '{\"type\":\"regression\",\"target_detected\":\"a\",\"RMSE\":0.2935,\"MSE\":0.0862,\"MAE\":0.2376,\"R2 Score\":0.8735}', '2026-04-28 19:39:45'),
(137, 25, 'model_job_211.joblib', 'iris.csv', 'pred_1777405464.csv', 'flaml', '{\"type\":\"regression\",\"target_detected\":\"a\",\"RMSE\":0.226,\"MSE\":0.0511,\"MAE\":0.1811,\"R2 Score\":0.925}', '2026-04-28 19:44:31');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `verify_account`
--

CREATE TABLE `verify_account` (
  `id` int(11) NOT NULL,
  `verif_key` varchar(32) NOT NULL,
  `creation_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `datasets`
--
ALTER TABLE `datasets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Ευρετήρια για πίνακα `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `trained_models`
--
ALTER TABLE `trained_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_dataset_id` (`dataset_id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Ευρετήρια για πίνακα `user_predictions`
--
ALTER TABLE `user_predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Ευρετήρια για πίνακα `verify_account`
--
ALTER TABLE `verify_account`
  ADD PRIMARY KEY (`verif_key`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `datasets`
--
ALTER TABLE `datasets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT για πίνακα `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT για πίνακα `trained_models`
--
ALTER TABLE `trained_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT για πίνακα `user_predictions`
--
ALTER TABLE `user_predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `datasets`
--
ALTER TABLE `datasets`
  ADD CONSTRAINT `datasets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `trained_models`
--
ALTER TABLE `trained_models`
  ADD CONSTRAINT `fk_models_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_models_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Περιορισμοί για πίνακα `user_predictions`
--
ALTER TABLE `user_predictions`
  ADD CONSTRAINT `user_predictions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `verify_account`
--
ALTER TABLE `verify_account`
  ADD CONSTRAINT `verify_account_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
