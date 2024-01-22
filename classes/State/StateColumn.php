<?php

namespace Shasoft\DbSchema\State;

use Shasoft\DbSchema\Command\Seeder;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DefaultValue;
use Shasoft\DbSchema\Command\AutoIncrement;
use Shasoft\DbSchema\Command\ConversionInput;
use Shasoft\DbSchema\Command\ConversionOutput;

// Состояние колонки базы данных
class StateColumn extends StateTableChild
{
    // Комментарий
    public function comment(): string
    {
        return $this->value(Title::class);
    }
    // Максимальная длинна поля
    public function getDbSchemaType(): ?string
    {
        return $this->value(DbSchemaType::class);
    }
    // Поле является автоинкрементирующим?
    public function hasAutoIncrement(): ?bool
    {
        return $this->value(AutoIncrement::class, false);
    }
    // Колонка может быть нулевой?
    public function hasNullable(): ?bool
    {
        return is_null($this->value(DefaultValue::class, null));
    }
    // Конвертировать во внутренний формат 
    public function input(mixed $value): mixed
    {
        if (!is_null($value)) {
            if ($this->has(ConversionInput::class)) {
                return $this->get(ConversionInput::class)->convert($value);
            }
        }
        return $value;
    }
    // Конвертировать во внешний формат 
    public function output(mixed $value): mixed
    {
        if (!is_null($value)) {
            if ($this->has(ConversionOutput::class)) {
                return $this->get(ConversionOutput::class)->convert($value);
            }
        }
        return $value;
    }
    // Сгенерировать значение с вероятностью $procNULL из 100 генерировать NULL если колонка это допускает
    public function seeder(int $procNULL): mixed
    {
        $ret = null;
        if ($this->has(Seeder::class)) {
            // Если значение может быть NULL?
            $isSet = true;
            // Если вероятность генерации NULL не нулевая И колонка поддерживает NULL
            if ($procNULL > 0 && $this->hasNullable()) {
                // то проверить шанс выпадения NULL
                if (ColumnInteger::random(1, 100) <= $procNULL) {
                    $isSet = false;
                }
            }
            if ($isSet) {
                $ret = $this->get(Seeder::class)->generate();
            }
        }
        return $ret;
    }
};
