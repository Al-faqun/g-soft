# g-soft
Test-task: Personal area for clients and managers

Это описание не обещает быть коротким, но прочтите, пожалуйста, до конца, я вложил в него душу!

Сайт сделан с разделением *публичных и закрытых* ресурсов: скрипты-точки входа, js, css располагаются в папке public; исходники классов, файлы конфигураций и прочие в главной папке. 'Document root' сервера устанавливается в папку public; таким образом возможность с интернета попасть в закрытые папки отсутствует, нет нужды перенапрягаться с htaccess. У сайта для каждого пользоваетеля есть два режима: в режиме 'In production' все сообщения об ошибках записываются в логи администратора, а в режиме 'In development' отображаются напрямую пользователю, что полезно при отладке. На данный момент на сайте работает режим 'In production', однако для просмотра сообщений об оишбках (чтобы вы могли полнее оценить сайт) доступен файл errors.log: http://shinoa.web44.net/errors.log

Этот сайт *предоставляет* возможность клиентам вести грузы, менеджерам следить за их приходом и статусом. 
Существует две категории пользователей: clients и managers. 

По-умолчанию зарегистрированный пользователь приобретает группу 'clients', сменить ему группу должен администратор через базу данных или админский кабинет (последнее не реализовано для краткости).

Помимо таблиц `clients` и `managers` (описания всех таблиц даны внизу), добавлена таблица `users`. Она необходима затем, чтобы хранить данные о зарегистрированных пользователях (логин и группу), в то время как две другие таблицы хранят обособленные от регистрации данные по конкретным группам. Обратите внимание, что ID двух этих таблиц заимствуется из таблицы `users`, таким образом эта таблица является "главной", ID пользователя во всех таблицах всегда указывается один и тот же с foreign key, и при удалении записи из таблицы `users` записи в остальных таблицах удаляются автоматически.

На сайте присутствует *регистрация* ; в таблице `passwords` хранятся хэши паролей, полученных от пользователя и зашифрованных алгоритмом CRYPT_BLOWFISH функции php password_hash(), который считается самым безопасным и рекомендуемым создателями php методом. Сами пароли нигде не хранятся по очевидным причинам безопасности: когда пользователь заходит на сайт под своим именем и паролем, если они прошли проверку, ему присваивается хэш (токен) и ID в таблице `logins`. 

В дальнейшем при попытке доступа к защищённым частям сайта, эти хэш и токен сравниваются с таковыми в бд (посредством функции php hash_equals, которая рекомендована разработчиками как самый оптимальный способ сравнения хешей). Хэш генерируется алгоритмом sha256 и не представляет особой взломоустойчивости, зато он генерируется заново при каждом успешном заходе пользователя на сайт, а в случае неверного хэша в куки - все логины (токены) пользователя уничтожаются, ему нужно вводить пароль заново.

Подробнее про безопасность: https://stackoverflow.com/a/244907

### Тестирование: 
в разработке применялись тесты PhpUnit; они покрывают только основные классы работы с бд и не очень умны, но страхуют от многих неприятностей и позволили найти множество мелких недочётов. Они располагаются в папке src/Tests.

### Скрипты: 
в приложении для простоты реализован паттерн Page Controllers, где каждому обособленному действию, такому как: отобразить страницу грузов, обработать вход пользователя с паролем, выходу пользователя из системы, отобразить страницу регистрации, и так далее - присвоен собственный скрипт. Большинство скрптов возвращают во всех случаев страницу на основе посланных в них пользователем запросов, но некоторые (info.php, editCargo.php, newCargo.php) возвращают ответы в формате JSON, поскольку спроектированы для доступа со страниц при помощи AJAX.

### Классы приложения: 
я не представляю своей разработки без ООП, поэтому создавал классы везде где можно, возможно, и где нельзя. Моя философия: создать модульную систему, каждая часть которой имеет чёткие обязанности, запреты, и контракты с другими частями, чтобы каждую часть программы или функцию можно легко было поддерживать и улучшать. Задачи делятся на методы, которые полагаются на Dependecy Injection и имеют благодаря этому ясный интерфейс.
Большинство классов распределены по подпапкам в папке src:  

<details>
  <summary>Классы приложения (открыть спойлер)</summary>
  
#### Controllers: 
контроллеры находятся на вершине в иерархии объектов приложения. Они имеют право работать с пользовательским вводом, делегировать вывод страницы классам View, обработку ошибок классу ErrorHelper. Контроллеры обычно содержат методы, справляющиеся с конкретными задачами, например, метод CargoContoller::List() отображает страницу списка грузов.

#### Input: 
методы работы с пользовательским вводом, проще говоря - валидаторы. Все данные форм должны пройти здесь проверку. Некоторые валидаторы во всех случаях возвращают правильные данные (если от пользователя неверные, то берут дефолтные), например, SearchQueryValidator, поскольку данные от него идут для уточнения выборки грузов из бд, и всегда нужны, независимо от ввода пользователя. Другие возвращают false в случае малейшем ошибки (CargoValidator, RegFormValidator), а если всё правильно, то возвращают отфильтрованные и проверенные, то есть абсолютно достоверные данные в виде объекта или массива, которые затем применяет контроллер для работы с бд.

#### Database: 
"жернова" приложения, эти классы называются "мапперы", поскольку в большинстве своём они связывают (map) таблицы базы данных с конкретными классами php. Все операции с вводом данных в таблицу применяют Prepared Statements для исключения возможности sql-инъекции. На каждую таблицу - собственный маппер, хотя некоторые мапперы в связи со спецификой работы обращаются сразу к нескольким таблицам. Для связи с бд применяется объект PDO, который передаётся в виде ссылки, один на весь скрипт. В случае, если в маппере выполняется не один sql запрос типа insert/update, а несколько, применяется Транзакция, в случае сбоя в одном из запросов, база откатывается (rollback) к прежнему состоянию.

#### Entities: 
"сущности", классы, которые представляют основные  объекты, которыми оперируют другие классы: пользователь, клиент, менеджер, груз. Сущности упрощают DI, позволяя перемещать данные между классами  в виде одной переменной. В сумме классы Input, Database и Entities составляют уровень "model" архитектуры MVC.

#### Views: 
единственные классы кроме ErrorHelper'а, которые имеют право отображать пользователю что-либо. Задача View в том, чтобы подготовить к отображению "сухие" данные, полученные из model.

#### Прочие классы, достойные заметки:

Loader заключает в себе методы хранения и получения общих для всего скрипта данных: объекта PDO, адреса главной директории сайта. StatusSelector позволяет управлять статусом приложения, получать его из конфигурационного файла, отображать пользователю при желании. Класс  Pager создаёт ссылки пажинации для таблицы грузов.
</details>



### Javacript: 
для получения информации о клиенте, менеджере, и прочих, используется Ajax с js и jquery. Верстка основана на bootstrap + ручная доработка. 

### Ниже представлены определения таблиц сайта:

###  Структура таблицы `cargo`
<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `cargo` (
  `id` int(11) NOT NULL,
  `container` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `man_id` int(11) DEFAULT NULL,
  `date_arrival` datetime DEFAULT NULL,
  `status` enum('awaiting','on board','finished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'awaiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; </pre>
</details>

### Структура таблицы `clients`

<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;</pre>
</details>

### Структура таблицы `logins`

<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `logins` (
  `id` int(11) NOT NULL,
  `token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;</pre>
</details>

### Структура таблицы `managers`

<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;</pre>
</details>

### Структура таблицы `passwords`

<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `passwords` (
  `userid` int(11) NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;</pre>
</details>

### Структура таблицы `users`

<details>
  <summary>открыть</summary>
  <pre>CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usergroup` enum('client','manager') COLLATE utf8_unicode_ci DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;</pre>
</details>

### Внешние ключи: 

<details>
  <summary>открыть</summary>
  <pre>--
-- Ограничения внешнего ключа таблицы `cargo`
--

ALTER TABLE `cargo`
  ADD CONSTRAINT `cargo_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cargo_ibfk_2` FOREIGN KEY (`man_id`) REFERENCES `managers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `clients`
--
<details>
  <summary>открыть</summary>
  <pre></pre>
</details>
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
  ADD CONSTRAINT `passwords_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;</pre>
</details>



