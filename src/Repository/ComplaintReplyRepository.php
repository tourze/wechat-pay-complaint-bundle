<?php

namespace WechatPayComplaintBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatPayComplaintBundle\Entity\ComplaintReply;

/**
 * @method ComplaintReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComplaintReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComplaintReply[]    findAll()
 * @method ComplaintReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplaintReplyRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComplaintReply::class);
    }
}
