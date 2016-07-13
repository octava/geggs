<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Exception\InvalidArgumentException;
use Octava\GeggsBundle\Exception\RuntimeException;

class ComposerLockHelper
{
    public function diff($lockOne, $lockTwo)
    {
        if (empty($lockOne)) {
            throw  new InvalidArgumentException('Invalid argument lockOne, must be not empty.');
        }
        if (empty($lockTwo)) {
            throw  new InvalidArgumentException('Invalid argument lockOne, must be not empty.');
        }

        $one = $this->jsonDecode($lockOne);
        $two = $this->jsonDecode($lockTwo);

        $onePackages = !empty($one['packages']) ? $one['packages'] : [];
        $twoPackages = !empty($two['packages']) ? $two['packages'] : [];
        $result = $this->findDiff($onePackages, $twoPackages);

        $onePackages = !empty($one['packages-dev']) ? $one['packages-dev'] : [];
        $twoPackages = !empty($two['packages-dev']) ? $two['packages-dev'] : [];
        $result = array_merge($result, $this->findDiff($onePackages, $twoPackages));

        return $result;
    }

    public function jsonDecode($json)
    {
        $result = json_decode($json, true);
        $error = null;
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = null;
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Достигнута максимальная глубина стека';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Некорректные разряды или не совпадение режимов';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Некорректный управляющий символ';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Синтаксическая ошибка, не корректный JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Некорректные символы UTF-8, возможно неверная кодировка';
                break;
            default:
                $error = 'Неизвестная ошибка';
                break;
        }
        if ($error) {
            throw new RuntimeException($error);
        }

        return $result;
    }

    protected function findDiff(array $onePackages, array $twoPackages)
    {
        $one = array_column($onePackages, 'time', 'name');
        $two = array_column($twoPackages, 'time', 'name');

        $result = [];
        foreach ($one as $name => $date) {
            $oneDate = new \DateTime($date);
            if (array_key_exists($name, $two)) {
                $twoDate = new \DateTime($two[$name]);
                if ($oneDate < $twoDate) {
                    $result[] = $name;
                }
            }
        }

        return $result;
    }
}
