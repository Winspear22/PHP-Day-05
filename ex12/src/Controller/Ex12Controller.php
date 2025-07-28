<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Address;
use App\Entity\BankAccount;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex12Controller extends AbstractController
{
	/**
	 * @Route("/ex12", name="ex12_index")
	 */
	public function index(Request $request, PersonRepository $repo): Response
	{
		$filterName = $request->query->get('filter_name', '');
		$sortBy = $request->query->get('sort_by', 'name');
		$sortDir = $request->query->get('sort_dir', 'asc');

		// --- PROTECTIONS ICI ---
		$allowedSorts = ['name', 'email', 'city', 'birthdate'];
		$allowedDir = ['asc', 'desc'];
		if (!in_array($sortBy, $allowedSorts))
		{
			$this->addFlash('notice', '⚠️ Tri non valide, utilisation du tri par défaut.');
			$sortBy = 'name';
		}
		if (!in_array($sortDir, $allowedDir))
		{
			$this->addFlash('notice', '⚠️ Sens de tri non valide, utilisation du tri par défaut.');
			$sortDir = 'asc';
		}
		if (mb_strlen($filterName) > 80)
		{
			$this->addFlash('notice', '⚠️ Filtre trop long ! Limité à 80 caractères.');
			$filterName = mb_substr($filterName, 0, 80);
		}
		if (!preg_match('/^[\p{L}\p{N} _\'\-]*$/u', $filterName))
		{
			$this->addFlash('notice', '⚠️ Le filtre contient des caractères non autorisés.');
			$filterName = preg_replace('/[^\p{L}\p{N} _\'\-]/u', '', $filterName);
		}
		// --- FIN PROTECTIONS ---

		try
		{
			$people = $repo->findWithFilters($filterName, $sortBy, $sortDir);
			$total_people = $repo->count([]);
		} 
		catch (\Exception $e)
		{
			$this->addFlash('notice', "Erreur d'accès à la base de données : " . $e->getMessage());
			$people = [];
			$total_people = 0;
		}
		return $this->render('index.html.twig', [
			'people' => $people,
			'filter_name' => $filterName,
			'sort_by' => $sortBy,
			'sort_dir' => $sortDir,
			'total_people' => $total_people,
		]);
	}

	/**
	 * @Route("/ex12/add-test-persons", name="ex12_add_test_persons", methods={"POST"})
	 */
	public function addTestPersons(EntityManagerInterface $em, PersonRepository $repo)
	{
		$messages = [];
		try
		{
			$count = $repo->count([]);
		}
		catch (\Exception $e)
		{
			$this->addFlash('notice', "Erreur lors de l'accès au repository de Person." . $e->getMessage());
			return $this->redirectToRoute('ex12_index');
		}
		$toAdd = max(0, 10 - $count);

		if ($toAdd <= 0)
		{
			$this->addFlash('notice', "Erreur, il y a déjà 10 personnes.");
			return $this->redirectToRoute('ex12_index');
		}
		try
		{
			for ($i = 1; $i <= $toAdd; $i++)
			{
				$p = new Person();
				$p->setUsername('user' . $i);
				$p->setName($this->randomAlphaString(5) . "Nom" . $i);
				$p->setEmail("user{$i}@mail.com");
				$p->setEnable((bool)rand(0,1));
				$p->setBirthdate(new \DateTime('-' . rand(18,40) . ' years'));
				$p->setMaritalStatus('single');

				// Ajout adresse (1 par personne)
				$a = new Address();
				$a->setStreet("Adresse {$i} avenue Testville");
				$a->setCity("Ville" . chr(65 + $i));
				$a->setCountry("France");
				$a->setPerson($p);
				$p->addAddress($a);

				// Ajout bank_account (1 par personne)
				$b = new BankAccount();
				$b->setIban('FR76' . str_pad($i, 20, '0', STR_PAD_LEFT));
				$b->setBankName("Bank{$i}");
				$p->setBankAccount($b);
				$b->setPerson($p);

				$em->persist($p);
				$em->persist($a);
				$em->persist($b);

				$messages[] = "Ajout de {$p->getName()}";
			}
			$em->flush();
			$this->addFlash('notice', '✅ ' . count($messages) . ' personnes ajoutées');
		}
		catch (\Exception $e)
		{
			$this->addFlash('notice', 'Erreur lors de l\'ajout de 10 personnes : ' . $e->getMessage());
		}
		return $this->redirectToRoute('ex12_index');
	}

	// Petite fonction utilitaire pour les noms random (copie la dans ton contrôleur si besoin)
	private function randomAlphaString($length = 8) 
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$result = '';
		for ($i = 0; $i < $length; $i++)
			$result .= $characters[random_int(0, strlen($characters) - 1)];
		return $result;
	}

	/**
	 * @Route("/ex12/drop-all", name="ex12_drop_all", methods={"POST"})
	 */
	public function dropAll(EntityManagerInterface $em, PersonRepository $repo)
	{
		try 
		{
			$people = $repo->findAll();
			foreach ($people as $p) $em->remove($p);
			$em->flush();
			$this->addFlash('notice', 'Toutes les personnes supprimées !');
		}
		catch (\Exception $e)
		{
			$this->addFlash('notice', "Erreur lors de la suppression : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex12_index');
	}
}
