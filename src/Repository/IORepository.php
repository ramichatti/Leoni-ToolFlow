<?php

namespace App\Repository;

use App\Entity\IO;
use App\Enum\IOStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IO>
 *
 * @method IO|null find($id, $lockMode = null, $lockVersion = null)
 * @method IO|null findOneBy(array $criteria, array $orderBy = null)
 * @method IO[]    findAll()
 * @method IO[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method int     countMonth(int $month)
 */
class IORepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IO::class);
    }

    public function save(IO $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IO $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Find the last IO for a specific tool
     * 
     * @param string $toolId
     * @return IO|null
     */
    public function findLastIOByTool(string $toolId): ?IO
    {
        return $this->createQueryBuilder('io')
            ->andWhere('io.tool = :toolId')
            ->setParameter('toolId', $toolId)
            ->orderBy('io.dateEntre', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Count IOs by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->createQueryBuilder('io')
            ->select('io.status as status, COUNT(io.id) as count')
            ->groupBy('io.status')
            ->getQuery()
            ->getResult();
            
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']->value] = (int)$row['count'];
        }
        
        // Ensure all statuses are represented
        foreach (IOStatus::cases() as $status) {
            if (!isset($counts[$status->value])) {
                $counts[$status->value] = 0;
            }
        }
        
        return $counts;
    }
    
    /**
     * Get IO count by date for the last 7 days
     * 
     * @return array
     */
    public function countByDate(): array
    {
        $endDate = new \DateTime();
        $startDate = (new \DateTime())->modify('-6 days');
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DATE(io.date_entre) as date, COUNT(io.id) as count
            FROM i_o io
            WHERE io.date_entre >= :startDate
            GROUP BY DATE(io.date_entre)
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
     * Count IOs by month and year
     * 
     * @param int $month The month (1-12)
     * @param int|null $year The year (optional)
     * @return int
     */
    public function countByMonth(int $month, ?int $year = null): int
    {
        $startDate = new \DateTime();
        $startDate->setDate($year ?? (int)$startDate->format('Y'), $month, 1);
        $startDate->setTime(0, 0, 0);
        
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $endDate->setTime(23, 59, 59);
        
        $qb = $this->createQueryBuilder('io')
            ->select('COUNT(io.id)')
            ->where('io.dateEntre >= :startDate')
            ->andWhere('io.dateEntre <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
            
        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get monthly counts for each month in a year
     * 
     * @param int|null $year The year to get counts for (defaults to current year)
     * @return array<int, int> Array of counts indexed by month number
     */
    public function getMonthlyCountsByYear(?int $year = null): array
    {
        $year = $year ?? (int)(new \DateTime())->format('Y');
        $counts = array_fill(1, 12, 0);
        
        for ($month = 1; $month <= 12; $month++) {
            $counts[$month] = $this->countByMonth($month, $year);
        }
        
        return $counts;
            
        // Initialize counts for all months
        $counts = array_fill(1, 12, 0);
        
        // Fill in actual counts
        foreach ($result as $row) {
            $counts[(int)$row['month']] = (int)$row['count'];
        }
            
        return $counts;
    }

    /**
     * Count IOs by manufacturer and status
     * 
     * @return array
     */
    public function countByManufacturerAndStatus(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT t.manufacturer as manufacturer, io.status as status, COUNT(io.id) as count
            FROM i_o io
            JOIN tool t ON io.tool_id = t.id
            GROUP BY t.manufacturer, io.status
            ORDER BY t.manufacturer ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAllAssociative();
        
        // Process the results
        $data = [];
        foreach ($result as $row) {
            if (!isset($data[$row['manufacturer']])) {
                $data[$row['manufacturer']] = [
                    'IN' => 0,
                    'OUT' => 0
                ];
            }
            $data[$row['manufacturer']][$row['status']] = (int)$row['count'];
        }
        
        return $data;
    }
    
    /**
     * Count IOs by description type and status
     * 
     * @return array
     */
    public function countByDescriptionAndStatus(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT t.description as description, io.status as status, COUNT(io.id) as count
            FROM i_o io
            JOIN tool t ON io.tool_id = t.id
            GROUP BY t.description, io.status
            ORDER BY t.description ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $result = $resultSet->fetchAllAssociative();
        
        // Process the results
        $data = [];
        foreach ($result as $row) {
            if (!isset($data[$row['description']])) {
                $data[$row['description']] = [
                    'IN' => 0,
                    'OUT' => 0
                ];
            }
            $data[$row['description']][$row['status']] = (int)$row['count'];
        }
        
        return $data;
    }
} 