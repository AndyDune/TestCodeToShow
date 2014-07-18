<?php
 /**
  * ----------------------------------------------------
  * | Автор: Андрей Рыжов (Dune) <info@rznw.ru>         |
  * | Сайт: www.rznw.ru                                 |
  * | Телефон: +7 (4912) 51-10-23                       |
  * | Дата: 15.07.14                                      
  * ----------------------------------------------------
  *
  * Менеджер сервисов. Синглетон.
  */


class ServiceManager
{
    static private $instance = null;

    protected $instances = [];

    static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new ServiceManager();
        }
        return self::$instance;
    }

    /**
     * Регистрация фабрики.
     *
     * @param $name
     * @param $factory
     * @return $this
     */
    public function addFactory($name, $factory)
    {
        $this->factories[$name] = $factory;

        return $this;
    }

    /**
     *  Возврат сервиса. Упрощенный (минимальный контроль ошибок).
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function get($name)
    {
        if (isset($this->instances[$name])) {
            // Уже есть сервис
            return $this->instances[$name];
        }

        if (!array_key_exists($name, $this->factories)) {
            throw new Exception('Ошибка');
        }

        $factory = $this->factories[$name];
        if (is_callable($factory)) {
            // Отработка фабрики
            $instance = call_user_func($factory, $this);
        } else {
            throw new Exception('Ошибка');
        }
        return $instance;
    }


} 