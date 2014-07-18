<?php
 /**
  * ----------------------------------------------------
  * | Автор: Андрей Рыжов (Dune) <info@rznw.ru>         |
  * | Сайт: www.rznw.ru                                 |
  * | Телефон: +7 (4912) 51-10-23                       |
  * | Дата: 15.07.14                                      
  * ----------------------------------------------------
  *
  * Обертка для кеширования результатов.
  */


class Cache 
{
    protected $host = 'localhost';
    protected $port = 11;

    protected $cacheAdaptor = null;

    protected $modelName = '';
    protected $model = null;
    protected $cacheMethods = [];

    /**
     * @var ServiceManager
     */
    protected $serviceManager = null;

    public function __construct($sm)
    {
        $this->serviceManager = $sm;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param $name имя сервиса
     * @param array $cacheMethods методы для которых включается кеширование
     * @return $this
     */
    public function setModelName($name, $cacheMethods = [])
    {
        $this->model = null;
        $this->modelName = $name;
        $this->cacheMethods = $cacheMethods;
        return $this;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function getModel()
    {
        if (!$this->model) {
            $this->model = $this->serviceManager->get($this->modelName);
        }
        return $this->model;
    }

    protected function _getCacheAdaptor()
    {
        if (!$this->cacheAdaptor) {
            $this->cacheAdaptor = new Memcache();
            $this->cacheAdaptor->connect($this->host, $this->port);
        }
        return $this->cacheAdaptor;
    }

    /**
     * Через этот магический метод пропускаем  все запросы к модели.
     * Указанные кешируем.
     *
     * @param $name
     * @param $arguments
     * @return array|mixed|string
     */
    public function __call($name, $arguments)
    {
        if (!in_array($name, $this->cacheMethods)) {
            $model = $this->getModel();
            return call_user_func_array([$model, $name], $arguments);
        }

        $key = 'ObjectCache' . md5($this->getModelName() . '+' . $name . '+' . serialize($arguments));

        $adaptor = $this->_getCacheAdaptor();
        if ($result = $adaptor->get($key)) {
            $result = unserialize($result);
            return $result['data'];
        }

        $model = $this->getModel();
        $result = call_user_func_array([$model, $name], $arguments);
        $storeResult = ['data' => $result, 'time' => time()];
        $adaptor->set($key, serialize($storeResult));
        return $result;
    }

} 