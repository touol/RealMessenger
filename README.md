Новая версия RealMessenger -компонент для обмена личными сообщениями.
Теперь gtsNotify, требующийся для него, работает через <a href="comet-server.ru">comet-server.ru</a>. Также добавлены смайлики и онлайн-офлайн статус, и доработана верстка.
<img src="https://file.modx.pro/files/2/2/4/22485e051bd60e4e50c477ba36fbc1bf.png" />
До скайпа далеко, но реализован минимальный набор мессенджера.
<cut/>
<b>Установка</b>
Установить с modstore gtsNotify, настроить его на <a href="comet-server.ru">comet-server.ru</a>.
Установить и настроить UserFiles.
Устанавливаем RealMessenger и на нужной странице вызываем сниппет: 
<code>{'!RealMessenger' | snippet}</code> 
Для bootstrap v4 указываем чанки:
<code> {'!RealMessenger' | snippet :[ 
'OuterTpl'=>'b4.tpl.RealMessenger.outer', 
'SearchContactTpl'=>'b4.tpl.RealMessenger.search.contact', 
]}</code>
Поправить стили если возникнут проблемы. Для смайликов требуется база utf8mb4.
<b>Оплата</b>
RealMessenger можно скачать бесплатно с https://gettables.ru/. Оплата, на модсторе, чисто за техподдержку и удобство. gtsNotify стоит 90р. UserFiles - 990р.
<b>Техподдержка</b>
Первые 3 месяца, с этого дня 17.03.2022, техподдержка бесплатна, чтоб знать какие проблеммы возникают.
<b>Пакеты</b>
<a href="gettables.ru/assets/packages/realmessenger-2.0.0-beta.transport.zip">gettables.ru/assets/packages/realmessenger-2.0.0-beta.transport.zip</a>
<a href="https://modstore.pro/office/packages/gtsnotify">gtsnotify</a>
<a href="https://modstore.pro/packages/photos-and-files/userfiles">userfiles</a>
<b>GitHub</b>
<a href="https://github.com/touol/gtsNotify">https://github.com/touol/gtsNotify</a>
<a href="https://github.com/touol/RealMessenger">https://github.com/touol/RealMessenger</a>

<strong>Демо</strong>
Демонстрация работы на <a href="https://gettables.ru/">https://gettables.ru/</a>. Требуется авторизация.
<img src="https://file.modx.pro/files/7/8/9/789f52abd7dd5692bd973464ff7c6947.png" />
П.С. Демо не работает. Что-то снова сломалось. Счас разберусь :-)
П.С. Теперь работает. Я оказывается тупо провайдера не настроил.