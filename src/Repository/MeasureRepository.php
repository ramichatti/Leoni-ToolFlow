<?php

namespace App\Repository;

use App\Entity\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Measure>
 *
 * @method Measure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Measure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Measure[]    findAll()
 * @method Measure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function save(Measure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Measure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Get average measurements
     * 
     * @return array
     */
    public function getAverageMeasurements(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select(
                'AVG(m.section) as avgSection',
                'AVG(m.crimpingHeight) as avgCrimpingHeight',
                'AVG(m.insulationHeight) as avgInsulationHeight',
                'AVG(m.crimpingWidth) as avgCrimpingWidth',
                'AVG(m.insulationWidth) as avgInsulationWidth'
            )
            ->getQuery()
            ->getSingleResult();
            
        return [
            'section' => round((float)$result['avgSection'], 2),
            'crimpingHeight' => round((float)$result['avgCrimpingHeight'], 2),
            'insulationHeight' => round((float)$result['avgInsulationHeight'], 2),
            'crimpingWidth' => round((float)$result['avgCrimpingWidth'], 2),
            'insulationWidth' => round((float)$result['avgInsulationWidth'], 2),
        ];
    }
    
    /**
     * Get measure count by date for the last 7 days
     * 
     * @return array
     */
    public function countByDate(): array
    {
        $endDate = new \DateTime();
        $startDate = (new \DateTime())->modify('-6 days');
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DATE(m.created_at) as date, COUNT(m.id) as count
            FROM measure m
            WHERE m.created_at >= :startDate
            GROUP BY DATE(m.created_at)
            ORDER BY date ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('startDate', $startDate->format('Y-m-d'));
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAllAssociative();
            
        // Initialize array with all dates in range
        $counts = [];
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate->modify('+1 day')
        );
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $counts[$dateString] = 0;
        }
        
        // Fill in actual counts
        foreach ($result as $row) {
            $counts[$row['date']] = (int)$row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Get measurements distribution by section
     * 
     * @return array
     */
    public function getMeasurementsBySection(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.section as section, COUNT(m.id) as count')
            ->groupBy('m.section')
            ->orderBy('m.section', 'ASC')
            ->getQuery()
            ->getResult();
            
        $data = [];
        foreach ($result as $row) {
            $data[(string)$row['section']] = (int)$row['count'];
        }
        
        return $data;
    }
} 