Data Record, валидация данных
===
[<< документация](readme.md)

DVelum-Core в отличие от платформы DVelum не имеет ORM.  

В ядре реализован класс Data\Record, по функционалу похожий на ORM\Record в платформе.
C помощью этого класса можно валидировать данные, которые позже будут отправлены в какое-либо хранилище.

Data\Record не привязан к бд, настройки полей заводятся в конфигурационных файлах вручную.


```application/configs/common/(dist/local)/data_record.php``` - Реестр структур данных, в котором мы привязываем имя Record к файлу конфигурации.

Для примера создадим рекорд Invoice


```php
<?php
return [
    // имя объекта	Record
     'Invoice' => [
        'config' => 'data_objects/invoice.php' // путь к файлу конфигурации
    ]
];
```
Файл конфигурации для Record нужно будет создать в папке  ```application/configs/common/local/data_objects/invoice.php``` 

Структура файла:

```php
<?php
use Dvelum\Data\Record\DefaultValue\CurrentDateTimeString;
return [
	'fields' =>[
		'id' => [
			'type' => 'int',
			'required' => false
		 ],
		'client_id'=>[
			'type' => 'int',
			'required' => true,
			'minValue' => 1
		],
		'date' => [
			'type' => 'string',
			'defaultValueAdapter' => CurrentDateTimeString::class
		],
		'sum' => [
			'type' => 'float',
			'minValue' => 0
		]

	]
];
```
[Подробное описание возможных настроек полей](./data_record_fields.md)

После того как настройки внесены, объект может быть создан следующим образом:

```php
<?php
// инстанцируем фабрику, передаем в нее реестр объектов
$factory = new Factory(\Dvelum\Config::storage()->get('data_record.php'));
// создаем экземпляр Record
$record = $factory->create('Invoice');

try{
	// при задании значений пройдет валидация и приведение типа полей
	$record->setData([
		'client_id' => 1,
		'sum' => 200
	]);
	//если мы не передадим поле date, дата возмется из настроек по умолчанию
	//так же можно использовать
	$record->set('sum', 300);
}catch(\Throwable $e){
	// переданы невалидные данные
}

// помечает данные как обработанные, используется при сохранении
$record->commitChanges();

// если мы сделаем
$record->set('sum', 400);
// то получим массив с полями которые были обновлены после последнего commitChanges или создания объекта 
$updates = $record->getUpdates();
// все данные объекта можно получить при помощи
$record->getData(); 

// При необходимости можно проверить наличие всех обязательных полей
$validationResult = $record->validateRequired();

// Для удобства экспорта данных в хранилище, например в БД, есть подготовленный класс экспорта, 
// который конвертирует поля в нужный вид (например json поле в строку)
$export = new \Dvelum\Data\Record\Export\Database();
// или
$export = $factory->getDbExport();
// получить все данные
$data = $export->exportRecord($record);
// или получить только обновления
$data = $export->exportUpdates($record);

```
