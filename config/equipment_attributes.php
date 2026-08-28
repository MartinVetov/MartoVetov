<?php

/*
|--------------------------------------------------------------------------
| Допълнителни характеристики според категорията (стъпка 6 от заявката)
|--------------------------------------------------------------------------
|
| Ключът е slug на категорията. Полетата се показват само когато клиентът
| е избрал съответната категория, за да не се пита за излишни неща.
|
| Поддържани типове: select, text, number, textarea, radio.
|
*/

return [
    'mini-bager' => [
        'depth' => [
            'label' => 'Приблизителна дълбочина на изкопа',
            'type' => 'select',
            'options' => [
                'do-1m' => 'До 1 м',
                '1-2m' => '1 – 2 м',
                '2-3m' => '2 – 3 м',
                'nad-3m' => 'Над 3 м',
                'ne-znam' => 'Не знам',
            ],
        ],
        'length' => [
            'label' => 'Приблизителна дължина / обем',
            'type' => 'text',
            'placeholder' => 'напр. 30 линейни метра',
        ],
        'access' => [
            'label' => 'Достъп до обекта',
            'type' => 'select',
            'options' => [
                'lesen' => 'Лесен — може да влезе камион',
                'ogranichen' => 'Ограничен — тесен вход',
                'truden' => 'Труден — само през двор/градина',
                'ne-znam' => 'Не знам',
            ],
        ],
        'entrance_width' => [
            'label' => 'Ширина на входа',
            'type' => 'text',
            'placeholder' => 'напр. 2,20 м',
        ],
        'terrain' => [
            'label' => 'Тип терен',
            'type' => 'select',
            'options' => [
                'zemya' => 'Земя / пръст',
                'glina' => 'Глина',
                'kamenist' => 'Каменист',
                'skala' => 'Скала',
                'asfalt-beton' => 'Асфалт / бетон',
                'ne-znam' => 'Не знам',
            ],
        ],
    ],

    'avtovishka' => [
        'height' => [
            'label' => 'Необходима работна височина',
            'type' => 'select',
            'options' => [
                'do-15m' => 'До 15 м',
                '15-25m' => '15 – 25 м',
                'nad-25m' => 'Над 25 м',
                'ne-znam' => 'Не знам',
            ],
        ],
        'access' => [
            'label' => 'Достъп до мястото',
            'type' => 'select',
            'options' => [
                'lesen' => 'Лесен — може да се паркира до обекта',
                'ogranichen' => 'Ограничен',
                'truden' => 'Труден',
                'ne-znam' => 'Не знам',
            ],
        ],
        'load_weight' => [
            'label' => 'Приблизително тегло за повдигане',
            'type' => 'text',
            'placeholder' => 'напр. 150 кг (двама души с инструменти)',
        ],
        'usage' => [
            'label' => 'Вътрешно или външно използване',
            'type' => 'select',
            'options' => [
                'vanshno' => 'Външно',
                'vatreshno' => 'Вътрешно',
                'i-dvete' => 'И двете',
            ],
        ],
    ],

    'samosval' => [
        'material' => [
            'label' => 'Какъв материал ще се превозва',
            'type' => 'text',
            'placeholder' => 'напр. изкопни земни маси, чакъл, строителни отпадъци',
        ],
        'volume' => [
            'label' => 'Приблизително количество',
            'type' => 'text',
            'placeholder' => 'напр. 20 куб. м',
        ],
        'trips' => [
            'label' => 'Брой курсове',
            'type' => 'select',
            'options' => [
                '1' => 1,
                '2-5' => '2 – 5',
                '5-10' => '5 – 10',
                'nad-10' => 'Над 10',
                'ne-znam' => 'Не знам',
            ],
        ],
        'access' => [
            'label' => 'Достъп до обекта',
            'type' => 'select',
            'options' => [
                'lesen' => 'Лесен',
                'ogranichen' => 'Ограничен',
                'truden' => 'Труден',
            ],
        ],
    ],

    'tovarach' => [
        'work_type' => [
            'label' => 'Вид работа',
            'type' => 'text',
            'placeholder' => 'напр. разчистване на двор, товарене на камиони',
        ],
        'access' => [
            'label' => 'Достъп до обекта',
            'type' => 'select',
            'options' => [
                'lesen' => 'Лесен',
                'ogranichen' => 'Ограничен',
                'truden' => 'Труден',
            ],
        ],
        'terrain' => [
            'label' => 'Тип терен',
            'type' => 'select',
            'options' => [
                'raven' => 'Равен',
                'naklonen' => 'Наклонен',
                'kalen' => 'Кален / мек',
                'ne-znam' => 'Не знам',
            ],
        ],
    ],

    'teleskopichen-tovarach' => [
        'height' => [
            'label' => 'Необходима височина на повдигане',
            'type' => 'text',
            'placeholder' => 'напр. 12 м',
        ],
        'load_weight' => [
            'label' => 'Приблизително тегло на товара',
            'type' => 'text',
            'placeholder' => 'напр. 1,5 тона',
        ],
        'access' => [
            'label' => 'Достъп до обекта',
            'type' => 'select',
            'options' => [
                'lesen' => 'Лесен',
                'ogranichen' => 'Ограничен',
                'truden' => 'Труден',
            ],
        ],
    ],

    'stroitelna-platforma' => [
        'height' => [
            'label' => 'Необходима работна височина',
            'type' => 'text',
            'placeholder' => 'напр. 10 м',
        ],
        'usage' => [
            'label' => 'Вътрешно или външно използване',
            'type' => 'select',
            'options' => [
                'vanshno' => 'Външно',
                'vatreshno' => 'Вътрешно',
            ],
        ],
        'surface' => [
            'label' => 'Работна повърхност',
            'type' => 'select',
            'options' => [
                'beton' => 'Бетон / асфалт',
                'zemya' => 'Земя',
                'ne-znam' => 'Не знам',
            ],
        ],
    ],

    'generator' => [
        'power' => [
            'label' => 'Необходима мощност',
            'type' => 'text',
            'placeholder' => 'напр. 20 kVA',
        ],
        'phase' => [
            'label' => 'Захранване',
            'type' => 'select',
            'options' => [
                'monofazno' => 'Монофазно (220V)',
                'trifazno' => 'Трифазно (380V)',
                'ne-znam' => 'Не знам',
            ],
        ],
        'fuel_included' => [
            'label' => 'Гориво',
            'type' => 'select',
            'options' => [
                'da' => 'С включено гориво',
                'ne' => 'Без гориво',
                'ne-znam' => 'Не знам',
            ],
        ],
    ],
];
