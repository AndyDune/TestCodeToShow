<?php
// Используется всегда
include(__DIR__ . '/lib/ServiceManager.php');
include(__DIR__ . '/lib/Cache.php');

// В дальнейшем автозагрузка - используется не всегда
include(__DIR__ . '/lib/News.php');

$manager = ServiceManager::getInstance();
$manager->addFactory('cache', function($sm){
    $object = new Cache($sm);
    $object->setHost('localhost');
    $object->setPort(11211);
    return $object;
});

$manager->addFactory('news', function($sm){
    $object = new News();
    $adapter = new mysqli('localhost', 'root', '', 'test');
    $object->setAdaptor($adapter);
    return $object;
});
/** @var News $news */
//$news = $manager->get('news'); // Без кеша для теста модели
$news = $manager->get('cache')->setModelName('news',
                                            ['get', 'count', 'list', 'getMaxId']); // Включаем обертку для кеширования


$res = $news->add(['title' => 'test', 'body' => 'asdasasdsad']);
$res = $news->get(3);
$res = $news->getMaxId();
$res = $news->delete(1);
$res = $news->count();
$res = $news->getList(1, 1);
$res = $news->update(2, ['body' => '12311', 'title' => '123']);
print_r($res);