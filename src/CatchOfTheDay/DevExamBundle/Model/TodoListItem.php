<?php

namespace CatchOfTheDay\DevExamBundle\Model;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
class TodoListItem
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @var \DateTime
     * @JMS\Type("string")
     */
    private $created;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $text;

    /**
     * @var bool
     * @JMS\Type("boolean")
     */
    private $complete;

    public function __construct()
    {
        $this->id = Uuid::uuid1()->toString();
        $this->created = (new \DateTime())->format('Y-m-d H:i:s');
        $this->complete = false;
    }

    /**
     * @return mixed
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $complete
     * @return TodoListItem
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * @param \DateTime $created
     * @return TodoListItem
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @param string $id
     * @return TodoListItem
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $text
     * @return TodoListItem
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param array $data
     * @return TodoListItem
     */
    public static function fromAssocArray(array $data)
    {
        $item = new TodoListItem();
        $item->id = $data["id"];
        $item->text = $data["text"];
        $item->created = $data["created"];
        $item->complete = $data["complete"];

        return $item;
    }

    /**
     * @return array
     */
    public function toAssocArray()
    {
        $data = [];

        return $data;
    }
}