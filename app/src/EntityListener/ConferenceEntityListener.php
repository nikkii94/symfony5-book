<?php

namespace App\EntityListener;

use App\Entity\Conference;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class ConferenceEntityListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Auto-generate slug on Entity create
     * @param Conference $conference
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Conference $conference, LifecycleEventArgs $event) :void {
        $conference->computeSlug($this->slugger);
    }

    /**
     * Update slug on Entity save
     * @param Conference $conference
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(Conference $conference, LifecycleEventArgs $event) :void {
        $conference->computeSlug($this->slugger);
    }
}
