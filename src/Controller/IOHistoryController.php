<?php

namespace App\Controller;

use App\Repository\IORepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class IOHistoryController extends AbstractController
{
    private $security;
    private $ioRepository;

    public function __construct(Security $security, IORepository $ioRepository)
    {
        $this->security = $security;
        $this->ioRepository = $ioRepository;
    }

    #[Route('/io/history', name: 'app_io_history')]
    public function index(): Response
    {
        // Get the connected user
        $user = $this->security->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your I/O history.');
        }

        // Get the user's I/O history, ordered by date (most recent first)
        $ioHistory = $this->ioRepository->findBy(
            ['user' => $user],
            ['dateEntre' => 'DESC']
        );

        return $this->render('io/history.html.twig', [
            'ioHistory' => $ioHistory
        ]);
    }

    #[Route('/io/history/table', name: 'app_io_history_table', methods: ['GET'])]
    public function table(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your I/O history.');
        }

        // Base data
        $ioHistory = $this->ioRepository->findBy(
            ['user' => $user],
            ['dateEntre' => 'DESC']
        );

        // Read filters from query params
        $status = $request->query->get('status'); // IN | OUT
        $conformite = $request->query->get('conformite'); // conforme | non conforme
        $toolId = $request->query->get('toolId');
        $machine = $request->query->get('machine');
        $withCahier = $request->query->get('withCahier'); // yes/no/1/0
        $dateFrom = $request->query->get('dateFrom'); // YYYY-MM-DD
        $dateTo = $request->query->get('dateTo'); // YYYY-MM-DD
        $dnas = $request->query->get('dnas');
        $emplacement = $request->query->get('emplacement');
        $armoire = $request->query->get('armoire');

        // Simple in-memory filtering (OK for small sets)
        $filtered = array_filter($ioHistory, function ($io) use ($status, $conformite, $toolId, $machine, $withCahier, $dateFrom, $dateTo, $dnas, $emplacement, $armoire) {
            // Status filter
            if ($status && method_exists($io, 'getStatus')) {
                $ioStatus = $io->getStatus();
                if ($ioStatus && method_exists($ioStatus, 'value')) {
                    if ($ioStatus->value !== $status) {
                        return false;
                    }
                }
            }
            // conformite
            if ($conformite && method_exists($io, 'getConformite')) {
                $c = strtolower((string)$io->getConformite());
                if ($c !== strtolower($conformite)) {
                    return false;
                }
            }
            // Tool ID substring filter
            if ($toolId) {
                $tool = method_exists($io, 'getTool') ? $io->getTool() : null;
                $idStr = $tool ? (string) $tool->getId() : '';
                if (stripos($idStr, (string) $toolId) === false) {
                    return false;
                }
            }

            // Tool DNAS substring filter
            if ($dnas) {
                $tool = method_exists($io, 'getTool') ? $io->getTool() : null;
                $val = $tool && method_exists($tool, 'getDnas') ? (string) $tool->getDnas() : '';
                if (stripos($val, (string) $dnas) === false) {
                    return false;
                }
            }

            // Tool Emplacement substring filter
            if ($emplacement) {
                $tool = method_exists($io, 'getTool') ? $io->getTool() : null;
                $val = $tool && method_exists($tool, 'getEmplacement') ? (string) $tool->getEmplacement() : '';
                if (stripos($val, (string) $emplacement) === false) {
                    return false;
                }
            }

            // Tool Armoire substring filter
            if ($armoire) {
                $tool = method_exists($io, 'getTool') ? $io->getTool() : null;
                $val = $tool && method_exists($tool, 'getArmoire') ? (string) $tool->getArmoire() : '';
                if (stripos($val, (string) $armoire) === false) {
                    return false;
                }
            }
            // machine equals
            if ($machine !== null && $machine !== '' && method_exists($io, 'getMachine')) {
                if ((string)$io->getMachine() !== (string)$machine) {
                    return false;
                }
            }
            // withCahier equals
            if ($withCahier !== null && $withCahier !== '' && method_exists($io, 'getWithCahier')) {
                if (strtolower((string)$io->getWithCahier()) !== strtolower((string)$withCahier)) {
                    return false;
                }
            }
            // date range
            if ((($dateFrom && $dateFrom !== '') || ($dateTo && $dateTo !== '')) && method_exists($io, 'getDateEntre')) {
                $date = $io->getDateEntre();
                if ($dateFrom) {
                    $from = \DateTime::createFromFormat('Y-m-d', $dateFrom);
                    if ($from && $date < (clone $from)->setTime(0, 0, 0)) {
                        return false;
                    }
                }
                if ($dateTo) {
                    $to = \DateTime::createFromFormat('Y-m-d', $dateTo);
                    if ($to && $date > (clone $to)->setTime(23, 59, 59)) {
                        return false;
                    }
                }
            }
            return true;
        });

        // If AJAX request, return only table rows (partial) for dynamic updates
        if ($request->isXmlHttpRequest()) {
            return $this->render('io/_history_table_rows.html.twig', [
                'ioHistory' => $filtered,
            ]);
        }

        return $this->render('io/history_table.html.twig', [
            'ioHistory' => $filtered,
            // Echo filters back to template
            'filters' => [
                'status' => $status,
                'conformite' => $conformite,
                'toolId' => $toolId,
                'machine' => $machine,
                'withCahier' => $withCahier,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'dnas' => $dnas,
                'emplacement' => $emplacement,
                'armoire' => $armoire,
            ],
        ]);
    }
}