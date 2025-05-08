<?php

namespace WechatPayComplaintBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

/**
 * @method ComplaintMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComplaintMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComplaintMedia[]    findAll()
 * @method ComplaintMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplaintMediaRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComplaintMedia::class);
    }
}
