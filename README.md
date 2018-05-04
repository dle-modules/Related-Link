# Related Link
![version](https://img.shields.io/badge/version-1.1-green.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-11.0_--_13.x_(UTF--8)-red.svg?style=flat-square "DLE Version")
[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/dle-modules/Related-Link/blob/master/License)

**Модуль DLE Related Link** предназначен для создания релевантной кольцевой перелинковки новостей по категории в полной новости. Страницы, связанные в кольцо, будут иметь намного больший статистический вес. А релевантные данные повысят кликабельность и заинтересованность пользователей вашего сайта.
# Требования к системе
* Версия DLE: 11.0 и выше
* Поддерживаемая кодировка: UTF-8
* Версия php: 5.4 и выше
# Установка
1. Залить все файлы к себе на сервер по папкам, предварительно изменив название папки **Default** в **templates** на название своего шаблона.
2. Открыть **fullstory.tpl** и в нужном вам месте вставить подключение<pre><code>{include file="engine/mod_punpun/related_link/related_link.php?news_id={news-id}"}</code></pre>
# TPL файлы
1. **related_link.tpl** - отвечает за вывод новостей, имеет все те же теги что и краткая новость.
2. **related_block.tpl** - отвечает за показ новостей если они имеются. В файле есть 3 тега:<br/>
  2.1 **{content}** - выводит оформленные новости по шаблону из **related_link.tpl**.<br/>
  2.2 **[content] текст [/content]** - выводит текст внутри тегов если есть похожие новости.<br/>
  2.3 **[not-content] текст [/not-content]** - выводит текст внутри тегов если похожих новостей нет.
# Дополнительно
Стандартно выводиться 5 похожих новостей, для кольцевой перелинковки нужно как минимум 4. По-этому если вам требуется изменить на 4 или другое число, то вам поможет следующее подключение<pre><code>{include file="engine/mod_punpun/related_link/related_link.php?news_id={news-id}&limit=4"}</code></pre>Где число 4 и есть количество выводимых новостей.
