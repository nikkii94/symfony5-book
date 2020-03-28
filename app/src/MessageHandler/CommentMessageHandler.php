<?php


namespace App\MessageHandler;


use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private ?SpamChecker $spamChecker;
    private ?EntityManagerInterface $entityManager;
    private ?CommentRepository $commentRepository;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository,
        SpamChecker $spamChecker)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamChecker = $spamChecker;
    }

    /**
     * @param CommentMessage $commentMessage
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __invoke(CommentMessage $commentMessage)
    {
        $comment = $this->commentRepository->find($commentMessage->getId());
        if (!$comment) {
            return;
        }
        $comment->setState(
            (2 === $this->spamChecker->getSpamScore($comment, $commentMessage->getContext()))
                   ? 'spam'
                   : 'published'
        );
        $this->entityManager->flush();
    }
}
