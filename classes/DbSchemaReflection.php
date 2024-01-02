<?php

namespace Shasoft\DbSchema;

class DbSchemaReflection
{
    // Получить свойство объекта
    static private array $cacheProperty = [];
    static public function getObjectProperty(object $object, string $name): ?\ReflectionProperty
    {
        $refRet = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refRet) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasProperty($name)) {
                $refRet = $refClass->getProperty($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        if (is_null($refRet)) {
            s_dump($object, $name);
        }
        // Установить возможность доступа к защищенным и приватным свойствам
        $refRet->setAccessible(true);
        return $refRet;
    }
    // Получить метод объекта
    static public function getObjectMethod(object $object, string $name): ?\ReflectionMethod
    {
        $refRet = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refRet) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasMethod($name)) {
                $refRet = $refClass->getMethod($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        // Установить возможность доступа к защищенным и приватным методам
        if (is_null($refRet)) {
            s_dd($object, $refRet);
        }
        $refRet->setAccessible(true);
        //
        return $refRet;
    }
    // Получить значение защищенного/приватного свойства объекта
    static public function getObjectPropertyValue(object $object, string $name, mixed $default): mixed
    {
        //*
        $refProperty = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refProperty) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasProperty($name)) {
                $refProperty = $refClass->getProperty($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        //*/
        $refProperty = self::getObjectProperty($object, $name);
        if (is_null($refProperty)) {
            return $default;
        }
        return $refProperty->getValue($object);
    }
}
