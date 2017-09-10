-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Сен 06 2017 г., 21:28
-- Версия сервера: 10.1.21-MariaDB
-- Версия PHP: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `g-soft`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sum` decimal(12,2) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `bills`
--

INSERT INTO `bills` (`id`, `number`, `sum`, `cargo_id`) VALUES
(1, '12234-А', '10000.00', 1),
(2, '232456', '20000.24', 2),
(3, '232457', '31623.64', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `cargo`
--

CREATE TABLE `cargo` (
  `id` int(11) NOT NULL,
  `container` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `man_id` int(11) DEFAULT NULL,
  `date_arrival` datetime DEFAULT NULL,
  `status` enum('awaiting','on board','finished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'awaiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `cargo`
--

INSERT INTO `cargo` (`id`, `container`, `client_id`, `man_id`, `date_arrival`, `status`) VALUES
(1, '921943', 1, 4, '2017-09-16 00:00:00', 'finished'),
(2, '32354F', 2, 5, '2017-08-27 00:00:00', 'on board'),
(3, '432FD', 3, NULL, '2018-08-31 00:00:00', 'awaiting'),
(4, 'DS342', 1, 7, '2017-04-13 00:00:00', 'on board'),
(12, 'abrsafh12', 2, NULL, NULL, 'awaiting'),
(13, 'еновый', 2, NULL, NULL, 'awaiting'),
(14, 'еновый', 2, NULL, NULL, 'awaiting'),
(15, 'yjdsq', 2, NULL, NULL, 'awaiting'),
(16, 'fds', 2, NULL, NULL, 'awaiting'),
(17, 'fds', 2, NULL, NULL, 'awaiting'),
(18, 'fdf', 2, NULL, NULL, 'awaiting'),
(40, '123456', 24, NULL, NULL, 'awaiting'),
(41, '123456', 24, NULL, NULL, 'awaiting'),
(42, '2121', 24, NULL, NULL, 'awaiting'),
(43, '323', 24, NULL, NULL, 'awaiting'),
(44, 'dsds', 2, NULL, NULL, 'awaiting');

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `clients` (`id`, `company_name`, `inn`, `address`, `email`, `tel`) VALUES
(1, 'Vermut', '1234567890', 'Санкт-Петербург', 'ba@mail.ru', '89238456574'),
(2, 'Whiskey', '1234567891', 'Москва', 'be@ar.com', '89123336452'),
(3, 'Liquor', '1234567892', 'Севастополь', 'se@vas.org', '3456523'),
(24, 'Мамба', '123456789', 'Мухабарат', 'ingo@di.crya', '89218493528');

-- --------------------------------------------------------

--
-- Структура таблицы `logins`
--

CREATE TABLE `logins` (
  `id` int(11) NOT NULL,
  `token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `logins`
--

INSERT INTO `logins` (`id`, `token`, `userid`) VALUES
(1, '\'some token\'', 1),
(2, '\'some token\'', 1),
(17, '2dc01b93a382e0522dfe7dd6cbdd65a3d2534e70580e5d789ea67d3b854efb49', 2),
(28, '01c859bf9f853e6e21e3555855988e57a3dc1d3bd664d2e044a2d8596c1b3fe1', 4),
(31, 'be28e127d79b37e4b1845d70f760466fcb9d067b78d92a212ebb3756fbdf54b1', 2),
(32, 'c1d238ebd729ddcea3f2a8cc4d55765d25cd12289b1248aacb070fb699eea2a5', 2),
(33, 'b05c75f92401a553590c6c3b9e79a7a8ea8e2ae205690a443dc41e3896ea1a9d', 2),
(34, '6373f0a79d008fb1825cf9c8b1922c2b00a9a08f5e4cb5791ab704ff8be151f3', 2),
(35, 'f69763e247ccee2293db898c9d838bf08e3c0f377ce4148577f927c2ce2196b5', 4),
(38, '90fc17c1b785b2e15d729c2a5831e5b587bba0c083cfbd2016596b5ec6d4fba6', 2),
(39, '73e6c5d758ee37072850083af12d1397f233c10f53ff4280f1f131ce3929d6ca', 4),
(40, 'e919490f3a6c27bb37f6abf4712828a82b616ec76c5d779b61daa158fa6979fe', 2),
(41, '07724aa42058e078873b84f5125668b11ea23d7ea05596a476277335af296e3d', 4),
(48, '338fc90509e26918eda96ed623de1ca31223356e7e26f22bd6ee9e8caec5b2be', 24),
(49, '5a625e18cb285f9eac284b0777bf97f7d121f912d9712b1fb66b6e743d7eeb96', 2),
(50, '213e01d7b7b4de78e59df7e9d07cc32c078639a4b45e45374adf62ccdfff52b2', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `managers`
--

INSERT INTO `managers` (`id`, `surname`, `name`, `email`, `tel`) VALUES
(4, 'Сумеречный', 'Илья', 'ilsum1894@gmail.com', '8-921-453-22-11'),
(5, 'Кислый', 'Денис', 'denkis1995@gmail.com', '+79213215523'),
(6, 'Родесский', 'Миколас', 'ma@ya.ru', NULL),
(7, 'Карамазова', 'Наталья', 'na@ta.sha', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `passwords`
--

CREATE TABLE `passwords` (
  `userid` int(11) NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `passwords`
--

INSERT INTO `passwords` (`userid`, `hash`) VALUES
(1, 'test hash'),
(2, '$2y$10$Tr7XjQWedIkMBYTITYZ2AOqa/29UVugf/UX2Znaz..GIBu0rZyXsy'),
(4, '$2y$10$HnB3P9NKUFktwsVoRtx3o.G9a2lQ42TjxgVpJoWGWt9Kr2tgu0y4G'),
(24, '$2y$10$R0Paa7uBniD/UliHfUA9ROw6ZohHeMd/yDlg.3/X/w4IEMJMbYU7.');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usergroup` enum('client','manager') COLLATE utf8_unicode_ci DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `usergroup`) VALUES
(1, 'admin', 'client'),
(2, 'abra', 'client'),
(3, NULL, 'client'),
(4, 'abrams', 'manager'),
(5, NULL, 'manager'),
(6, NULL, 'manager'),
(7, NULL, 'manager'),
(24, 'fikus', 'client');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Индексы таблицы `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `man_id` (`man_id`);

--
-- Индексы таблицы `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`);

--
-- Индексы таблицы `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `passwords`
--
ALTER TABLE `passwords`
  ADD UNIQUE KEY `userid` (`userid`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT для таблицы `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT для таблицы `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `cargo`
--
ALTER TABLE `cargo`
  ADD CONSTRAINT `cargo_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cargo_ibfk_2` FOREIGN KEY (`man_id`) REFERENCES `managers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `logins`
--
ALTER TABLE `logins`
  ADD CONSTRAINT `logins_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `passwords`
--
ALTER TABLE `passwords`
  ADD CONSTRAINT `passwords_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
