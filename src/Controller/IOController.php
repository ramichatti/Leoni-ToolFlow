<?php

namespace App\Controller;

use App\Entity\IO;
use App\Entity\Measure;
use App\Entity\Tool;
use App\Enum\IOStatus;
use App\Repository\MeasureRepository;
use App\Repository\IORepository;
use App\Repository\ToolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class IOController extends AbstractController
{
    private $entityManager;
    private $security;
    private $ioRepository;
    private $measureRepository;
    private $toolRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        IORepository $ioRepository,
        MeasureRepository $measureRepository,
        ToolRepository $toolRepository
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->ioRepository = $ioRepository;
        $this->measureRepository = $measureRepository;
        $this->toolRepository = $toolRepository;
    }

    #[Route('/io', name: 'app_io_index')]
    public function index(): Response
    {
        $tools = $this->toolRepository->findAll();
        
        return $this->render('io/index.html.twig', [
            'tools' => $tools,
        ]);
    }

    #[Route('/io/form/{toolId}/{action}/{measureId}', name: 'app_io_form')]
    public function ioForm(Request $request, string $toolId, string $action, int $measureId = 0): Response
    {
        // Get the with_cahier parameter from the query string
        $withCahier = $request->query->get('withCahier', 'no');
        
        // Get the tool
        $tool = $this->entityManager->getRepository(Tool::class)->find($toolId);
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }

        // For IN action, we need a measure
        if (strtoupper($action) === 'IN' && $measureId > 0) {
            $measure = $this->measureRepository->find($measureId);
            if (!$measure) {
                throw $this->createNotFoundException('Measure not found');
            }
            
            // Render the sample input page for IN operations with a selected measure
            return $this->render('io/sample_input.html.twig', [
                'tool' => $tool,
                'action' => $action,
                'measure' => $measure,
                'withCahier' => $withCahier
            ]);
        } else if (strtoupper($action) === 'IN') {
            // For IN action without measure ID, show all measures for selection
            $measures = $this->measureRepository->findAll();
            
            return $this->render('io/measure_selection.html.twig', [
                'tool' => $tool,
                'action' => $action,
                'measures' => $measures,
                'withCahier' => $withCahier
            ]);
        } else {
            // For OUT action, we don't need a specific measure
            $measure = null;
        }

        return $this->render('io/form.html.twig', [
            'tool' => $tool,
            'action' => $action,
            'measure' => $measure,
            'withCahier' => $withCahier
        ]);
    }

    #[Route('/io/submit', name: 'app_io_submit', methods: ['POST'])]
    public function submitIO(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['success' => false, 'message' => 'Invalid JSON data']);
            }
            
            // Get the tool
            $tool = $this->entityManager->getRepository(Tool::class)->find($data['toolId']);
            if (!$tool) {
                return $this->json(['success' => false, 'message' => 'Tool not found']);
            }

            // Get current user
            $user = $this->security->getUser();
            if (!$user) {
                return $this->json(['success' => false, 'message' => 'User not authenticated']);
            }

            // Validate action
            if (!isset($data['action']) || !in_array($data['action'], ['IN', 'OUT'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid action. Must be either IN or OUT'
                ]);
            }

            // Create new IO record
            $io = new IO();
            $io->setTool($tool);
            $io->setUser($user);
            $io->setStatus($data['action'] === 'IN' ? IOStatus::IN : IOStatus::OUT);
            
            // Set with_cahier value if provided
            if (isset($data['with_cahier']) && in_array($data['with_cahier'], ['yes', 'no'])) {
                $io->setWithCahier($data['with_cahier']);
            } else {
                $io->setWithCahier('no'); // Default value
            }

            // Different handling based on action type
            if ($data['action'] === 'IN') {
                // For IN action, we need measure and sample values
                if (!isset($data['measureId']) || !$data['measureId']) {
                    return $this->json(['success' => false, 'message' => 'Measure ID is required for IN operation']);
                }
                
                // Get the measure
                $measure = $this->measureRepository->find($data['measureId']);
                if (!$measure) {
                    return $this->json(['success' => false, 'message' => 'Measure not found']);
                }
                
                $io->setMeasure($measure);
                
                // Validate sample values
                $tolerance = 0.1;
                $nonConformingFields = [];
                $isConform = true;
                
                foreach (["section", "crimpingHeight", "insulationHeight", "crimpingWidth", "insulationWidth"] as $field) {
                    if (!isset($data[$field]) || !is_numeric($data[$field]) || (float)$data[$field] <= 0) {
                        $fieldName = str_replace([
                            'crimpingHeight', 'insulationHeight', 'crimpingWidth', 'insulationWidth'
                        ], [
                            'crimping height', 'insulation height', 'crimping width', 'insulation width'
                        ], $field);
                        if ($field === 'section') $fieldName = 'section';
                        
                        return $this->json([
                            'success' => false, 
                            'message' => "Invalid value for $fieldName. Please enter a valid positive number."
                        ]);
                    }
                    
                    // Set the sample values
                    $setterMethod = 'set' . ucfirst($field);
                    $io->$setterMethod((float)$data[$field]);
                    
                    // Check if value is within tolerance
                    $standardField = str_replace(['crimping', 'insulation'], ['Crimping', 'Insulation'], $field);
                    $getterMethod = 'get' . $standardField;
                    
                    if (method_exists($measure, $getterMethod)) {
                        $standardValue = $measure->$getterMethod();
                        if (!$this->isWithinTolerance((float)$data[$field], $standardValue, $tolerance)) {
                            $nonConformingFields[] = $field;
                            $isConform = false;
                        }
                    }
                }
                
                // Set conformity status
                $io->setConformite($isConform ? 'conforme' : 'non conforme');
                
            } else {
                // For OUT action, we set all sample values to null
                $io->setMeasure(null);
                $io->setSection(null);
                $io->setCrimpingHeight(null);
                $io->setInsulationHeight(null);
                $io->setCrimpingWidth(null);
                $io->setInsulationWidth(null);
                $io->setConformite(null);
                
                // For OUT action, machine number is required
                if (!isset($data['machine']) || !is_numeric($data['machine']) || (int)$data['machine'] <= 0) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Machine number is required for OUT operation. Please enter a valid positive number.'
                    ]);
                }
                
                $io->setMachine((int)$data['machine']);
            }

            try {
                // Save to database
                $this->entityManager->persist($io);
                $this->entityManager->flush();
                
                // Log the successful insertion
                error_log('IO record successfully inserted with ID: ' . $io->getId() . ' and status: ' . $data['action']);
            } catch (\Exception $e) {
                // Log the error for debugging
                error_log('Error inserting IO record: ' . $e->getMessage());
                return $this->json([
                    'success' => false,
                    'message' => 'Error while inserting into the database: ' . $e->getMessage()
                ]);
            }

            $action = $data['action'] === 'IN' ? 'entry' : 'exit';
            $message = 'The ' . $action . ' operation was successfully recorded';
            
            // Add conformity message only for IN operations
            if ($data['action'] === 'IN') {
                $conformiteMessage = $isConform ? ' (Conforme aux normes)' : ' (Non conforme aux normes)';
                $message .= $conformiteMessage;
                
                // If there are non-conforming fields, add a warning
                if (!empty($nonConformingFields)) {
                    return $this->json([
                        'success' => true,
                        'message' => $message,
                        'warning' => true,
                        'nonConformingFields' => $nonConformingFields,
                        'conformite' => $io->getConformite()
                    ]);
                }
                
                return $this->json([
                    'success' => true, 
                    'message' => $message,
                    'conformite' => $io->getConformite()
                ]);
            }
            
            // For OUT operations, just return success
            return $this->json([
                'success' => true, 
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if a value is within the acceptable tolerance range
     */
    private function isWithinTolerance(float $value, float $standard, float $tolerance): bool
    {
        $min = $standard - $tolerance;
        $max = $standard + $tolerance;
        
        return $value >= $min && $value <= $max;
    }
}