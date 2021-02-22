Настройки полей Data Record
===
[<< документация](readme.md)

**type** - тип данных
* int
* float
* bool
* string
* json (принимает на вход строку json или массив)

**required** - признак обязательности заполнения

**minValue** - минимальное значение для int, float

**maxValue** - максимальное значение для int, float

**minLength** - минимальная длина строки в символах utf-8  для полей типа string

**maxLength** - максимальная длина строки в символах utf-8  для полей типа string

**default** - значение по умолчанию

**defaultValueAdapter** - адаптер для сложного значения по умолчанию, должен реализовать Dvelum\Data\Record\DefaultValue\DefaultValueInterface, передается имя класса

**validator** - адптер валидации занения Dvelum\Validator\ValidatorInterface, передается имя класса