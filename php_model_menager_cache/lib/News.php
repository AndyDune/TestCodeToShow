<?php
 /**
  * ----------------------------------------------------
  * | Автор: Андрей Рыжов (Dune) <info@rznw.ru>         |
  * | Сайт: www.rznw.ru                                 |
  * | Телефон: +7 (4912) 51-10-23                       |
  * | Дата: 15.07.14                                      
  * ----------------------------------------------------
  *
  * Модель новостей.
  * Поля: идентификатор, дата-время, заголовок, текст
  *
  */


class News 
{
    /**
     * @var mysqli
     */
    protected $adaptor = null;


    /**
     * имя таблицы
     *
     * @var string
     */
    protected $table = 'news';

    public function setAdaptor($adaptor)
    {
        $this->adaptor = $adaptor;
        return $this;
    }

    public function getMaxId()
    {
        $sql = <<<STR
        SELECT MAX(`id`) as `max` FROM {$this->table}
STR;
        $data = $this->adaptor->query($sql)->fetch_assoc();
        if (isset($data['max'])) {
            return $data['max'];
        }
        return 0;
    }


    public function get($id)
    {
        $sql = <<<STR
        SELECT * FROM {$this->table} WHERE `id` = ?
STR;

        $stmt = $this->adaptor->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_array(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        }
        return null;
    }


    public function add($data)
    {
        $sql = <<<STR
        INSERT INTO {$this->table} (`datetime`, `title`, `body`) VALUES (?, ?, ?)
STR;
        $stmt = $this->adaptor->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss',
                isset($data['datetime']) ? $data['datetime'] : date('Y-m-d H:i:s') ,
                isset($data['title']) ? $data['title'] : '' ,
                isset($data['body'])? $data['body'] : ''
            );
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    public function update($id, $data)
    {
        $sql = <<<STR
        UPDATE {$this->table} SET
         `datetime` = ?,
         `title` = ?,
         `body` = ?
         WHERE `id` = ?
STR;
        $stmt = $this->adaptor->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sssi',
                isset($data['datetime']) ? $data['datetime'] : date('Y-m-d H:i:s') ,
                isset($data['title']) ? $data['title'] : '' ,
                isset($data['body'])? $data['body'] : '',
                $id
            );
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;

    }

    public function delete($id)
    {
        $sql = <<<STR
        DELETE FROM {$this->table} WHERE `id` = ?
STR;
        $stmt = $this->adaptor->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }
        return null;
    }

    /**
     * Сколько всего
     * @return int
     */
    public function count()
    {
        $sql = <<<STR
        SELECT COUNT(*) as `count` FROM {$this->table}
STR;
        $data = $this->adaptor->query($sql)->fetch_assoc();
        if (isset($data['count'])) {
            return $data['count'];
        }
        return 0;

    }

    /**
     * Выбрать список.
     *
     * @param $count
     * @param int $shift
     * @return array|null
     */
    public function getList($count, $shift = 0)
    {
        $sql = <<<STR
        SELECT * FROM {$this->table}
        ORDER BY `datetime` DESC
        LIMIT ?
        OFFSET ?
STR;
        $stmt = $this->adaptor->prepare($sql);
        if ($stmt) {
            $results = [];
            $stmt->bind_param('ii', $count, $shift);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($res = $result->fetch_array(MYSQLI_ASSOC)) {
                $results[] = $res;
            }
            $stmt->close();
            return $results;
        }
        return null;


    }
}