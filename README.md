# Клиент BadaPush

## Использование клиента

Клиент используется для отправки push-уведомлений, sms и постановки задач на обзвон, а также получения результатов отправки и обзвона. 

Установка стандартная:

```
composer require druidvav/badapush-client
```

Для каждого из действий нужно создать сервис на сайте badapush.ru и получить API-ключ.

Для работы используются два основных класса

1. `Druidvav\BadapushClient\BadapushQueueClient` — Используется для отправки сообщений через очередь (только push и sms)
2. `Druidvav\BadapushClient\BadapushClient` — Используется для отправки сообщений сразу

### Инициализация клиента

Для симфони выглядит так:

```yaml
queue_client:
  class: Druidvav\BadapushClient\BadapushQueueClient
  arguments: [ "API_KEY" ]
client:
  class: Druidvav\BadapushClient\BadapushClient
  arguments: [ "API_KEY" ]
```

Либо напрямую:

```php
$queueClient = new Druidvav\BadapushClient\BadapushQueueClient("API_KEY");
$client = new Druidvav\BadapushClient\BadapushClient("API_KEY");
```

### Отправка SMS

Отправку SMS рекоммендуем делать через очередь.

```php
use Druidvav\BadapushClient\Payload\Payload; 
// Symfony
$this->get('queue_client')->sendPayload(Payload::create($phoneNumber, $message));
// PHP
$queueClient->sendPayload(Payload::create($phoneNumber, $message));
```

### Отправка пуш уведомлений

// Скоро //

### Задачи на обзвон «Badapush Caller»

#### Отправка задач на обзвон

Отправка задач на обзвон работает только без очереди.

```php
use Druidvav\BadapushClient\Payload\CallerPayload;

$payload = CallerPayload::create($phoneNumber); // Указываем номер телефона, по которому звонить
$payload->setExternalId($externalId); // ID в вашей системе, чтобы вы могли опознать результат обзвона. Например, номер посылки.
$payload->setTimezone($timezone); // Необязательно, часовой пояс клиента в том виде, в каком его отдает dadata.
$payload->setGroup($groupName); // Строка с названием очереди обзвона для разбиения задач на группы. Если разбивка не нужна — укажите одну строку для всех задач.
$payload->setResultOptions([
    'ok' => 'Готово',
    'received' => 'Уже забрал посылку'
]); // Не обязательно, варианты ответа доступные для выбора обзвонщику. Если не указать — будет только ok. Нужно указывать только если вы потом будете обрабатывать результаты обзвона.
$payload->setDataFields([
    'key' => $value,
    'key' => $value
]); // Не обязательно, значения дополнительных полей передаваемых задаче обзвона, подробнее смотри в разделе "Настройка сервисов". Если не указывать доступно только решение "ok" => "Готово!"
$this->get('client')->sendPayload($payload);
```

#### Отмена задач на обзвон

Допустим, вы поставили задачу на уведомление клиента о том, что он не забрал посылку, а он посылку уже забрал. Или на сбор информации, которую вы уже получили. Чтобы обзвонщики не звонили просто так — желательно отменить задачу. Задачи отменяются по `externalID` , если с переданным `externalId` нет задач — ничего страшного. Следить за тем, что задача реально была создана, необязательно.

```php
$this->get('client')->cancelCallsByExternalId($externalId, 'Причина отмены');
```

#### Сбор результатов выполнения задач

Привожу пример кода по сбору информации о выполненных задачах на обзвон.

```php
$maxId = 0;
do {
    $results = $this->get('client')->retrieveMessages($maxId); // Получаем пачку сообщений для обработки. $maxId, если не равен нулю помечает все сообщения с id до maxId включительно как прочитанные и больше не возвращает их в следующих запросах. По аналогии с API Telegram.
    foreach ($results as $result) {
        $maxId = max($maxId, $result->getId());
        if (!$result->getExternalId()) { // Если мы забыли передать externalId, то обработать не сможем :)
            continue;
        }
        $response = $result->getResponse();
        switch ($response['result']) {
            case 'invalid_job':
                // Обзвонщик нажал кнопку, что задачу невозможно выполнить
                break;
            case 'invalid_device_id':
                // Обзвонщик нажал кнопку, что мобильный телефон неправильный
                break;
            case 'cancel':
                // Задача была отменена по API
                break;
            case 'delay':
                // Звонок отложен обзвонщиком (недозвон, либо просьба перезвонить)
                break;
            case 'ok':
            case 'received': // Или любой из вариантов переданных в setDataFields 
                // Обзвонщик выполнил задачу, выбрав один из вариантов
                break;
            default: throw new Exception('Unknown code: ' . $response['result']);
        }
    }
} while (sizeof($results) > 0); // Выполняем пока не обработаем все результаты
```

## Настройка сервисов в badapush

### Сервис «Badapush Caller»

Сервис для обзвонов — готов к использованию сразу после создания. Если вы хотите добавить дополнительные поля в интерфейс обзвона или добавить ссылку на вашу админку — потребуется настроить поле «Конфигурация полей». Поле должно содержать правильный JSON определенного формата.

Пример:

```json
[
  { "field": "name", "title": "Клиент" },
  { "field": "package_id", "title": "Посылка", "url_field": "package_url" }
]
```

Как видим, здесь указывается массив объектов с определенными полями:

- `field` — значение `key` из примера вызова `setDataFields` вышe.
- `title` — название поля отображаемое обзвонщику в интерфейсе
- `url_field` — значение `key` в котором указывается ссылка, которая будет отображена на значении отображаемом в этом поле. **Внимание!** Первая переданная в задаче ссылка будет отображаться как iframe на странице обзвонщика, чтобы он мог увидеть необходимую информацию из проекта не уходя со страницы обзвона.

Полей может быть сколько угодно, но лучше ограничиться разумным количеством и передать ссылку на админку проекта.

### Сервис Apple Push Notification Service

Для работы нужно указать Bundle ID вашего приложения, а также приложить сертификат, либо (что проще) приложить файл p12.

Сгенерировать файл можно, например, по [этой инструкции](https://help.attendify.com/en/articles/613466-how-to-export-a-push-notification-apns-certificate-in-a-p12-file). Если вы указали при экспорте пароль — укажите его при загрузке файла в badapush. Обратите внимание, что сертификат должен быть обязательно **Production**.

Для работы мы используем протокол http/2.

### Сервис Google Cloud Messaging

Он же Firebase Cloud Messaging. Для работы требуется ключ «Firebase Server Key», который можно получить в админке Firebase вашего проекта.

Инструкция: https://firebase.google.com/docs/cloud-messaging/auth-server#authorize-legacy-protocol-send-requests

На данном этапе мы используем Legacy-протокол.

### Сервис SmsTraffic

Для работы нужно обязательно указать логин и пароль от учетной записи [SmsTraffic](https://www.smstraffic.ru), а также название отправителя, которое должно быть согласовано в сервисе smstraffic.