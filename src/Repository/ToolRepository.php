<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Enum\DescriptionType;
use App\Enum\ManufacturerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tool>
 *
 * @method Tool|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tool|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tool[]    findAll()
 * @method Tool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }

    public function save(Tool $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tool $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findToolById(string $id): ?Tool
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Count tools by manufacturer
     * 
     * @return array
     */
    public function countByManufacturer(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.manufacturer as manufacturer, COUNT(t.id) as count')
            ->groupBy('t.manufacturer')
            ->getQuery()
            ->getResult();
            
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['manufacturer']->value] = (int)$row['count'];
        }
        
        // Ensure all manufacturers are represented
        foreach (ManufacturerType::cases() as $manufacturer) {
            if (!isset($counts[$manufacturer->value])) {
                $counts[$manufacturer->value] = 0;
            }
        }
        
        return $counts;
    }
    
    /**
     * Count tools by description type
     * 
     * @return array
     */
    public function countByDescriptionType(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.description as description, COUNT(t.id) as count')
            ->groupBy('t.description')
            ->getQuery()
            ->getResult();
            
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['description']->value] = (int)$row['count'];
        }
        
        // Ensure all description types are represented
        foreach (DescriptionType::cases() as $description) {
            if (!isset($counts[$description->value])) {
                $counts[$description->value] = 0;
            }
        }
        
        return $counts;
    }
} 