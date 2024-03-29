<?php


namespace App\MessageHandler;


use App\Entity\Comment;
use App\ImageOptimizer;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Notification\CommentReviewNotification;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private SpamChecker $spamChecker;
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private MessageBusInterface $bus;
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;
//    private MailerInterface $mailer;
    private ImageOptimizer $imageOptimizer;
    private string $photoDir;
//    private string $adminEmail;
    private NotifierInterface $notifier;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository,
        SpamChecker $spamChecker, MessageBusInterface $bus, WorkflowInterface $commentStateMachine,
        LoggerInterface $logger, ImageOptimizer $imageOptimizer, NotifierInterface $notifier, string $photoDir)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamChecker = $spamChecker;
        $this->bus = $bus;
        $this->workflow = $commentStateMachine;
        $this->logger = $logger;
//        $this->mailer = $mailer;
//        $this->adminEmail = $adminEmail;
        $this->imageOptimizer = $imageOptimizer;
        $this->photoDir = $photoDir;
        $this->notifier = $notifier;
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
        if (!$comment instanceof Comment) {
            return;
        }

        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $commentMessage->getContext());
            $transition = 'accept';
            switch ($score) {
                case 2:
                    $transition = 'reject_spam';
                    break;
                case 1:
                    $transition = 'might_be_spam';
                    break;
                default:
                    break;
            }
            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($commentMessage);
        }
        elseif (
            $this->workflow->can($comment, 'publish') ||
            $this->workflow->can($comment, 'publish_ham')
        ) {
//            $this->mailer->send((new NotificationEmail())
//                ->subject('New comment posted')
//                ->htmlTemplate('emails/comment_notification.html.twig')
//                ->from($this->adminEmail)
//                ->to($this->adminEmail)
//                ->context(['comment' => $comment])
//            );
            $this->notifier->send(new CommentReviewNotification($comment), ...$this->notifier->getAdminRecipients());
        }
        elseif($this->workflow->can($comment, 'optimize')){
            if (!empty($comment->getPhotoFilename())) {
                $this->imageOptimizer->resize($this->photoDir.'/'.$comment->getPhotoFilename());
            }
            $this->workflow->apply($comment, 'optimize');
            $this->entityManager->flush();
        }
        else {
            $this->logger->debug('Dropping comment message', [
                'comment' => $comment->getId(),
                'state' => $comment->getState()
            ]);
        }
    }
}
