<?php


namespace App\Message;


class CommentMessage
{
    private ?int $id;
    private array $context;

    public function __construct(int $id, array $context = [])
    {
        $this->id = $id;
        $this->context = $context;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }


}
