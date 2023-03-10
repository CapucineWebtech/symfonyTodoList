<?php
namespace App\Controller;

use App\Entity\Todo;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    /**
     * @param TodoRepository $todoRepository
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_index')]
    public function index(TodoRepository $todoRepository): Response
    {
        $user = $this->getUser();
        $tasks = $todoRepository->findBy(['user' => $user]);
        return $this->render('index.html.twig', [
            'tasks' => $tasks,
            'userName' => $user->getUserIdentifier()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/addTask', name: 'app_addTask')]
    public function addTask(Request $request): Response
    {
        $task = new Todo();
        $form = $this->createForm(TodoType::class, $task);
        $user = $this->getUser();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($user);

            if ($form['picture_name']->getData()){
                $task->setPictureName("en attente");
            }
            $this->em->persist($task);
            $this->em->flush();

            if ($form['picture_name']->getData()){
                $uploadedFile = $form['picture_name']->getData();
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
                $newFilename = $task->getId() . "_" . "image.jpg";

                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $task->setPictureName($newFilename);
                $this->em->flush();
            }

            $this->addFlash(
                'successAdd',
                "La t??che ?? bien ??t?? ajout??e."
            );
            return $this->redirectToRoute('app_index');
        }

        return $this->render('addTask.html.twig', [
            'form' => $form->createView(),
            'userName' => $user->getUserIdentifier()
        ]);
    }

    /**
     * @param Todo $todo
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/editTask/{id}', name: 'app_editTask')]
    public function editTask(Todo $todo, Request $request)
    {
        $user = $this->getUser();
        if ($user !== $todo->getUser()){
            return $this->redirectToRoute('app_index');
        }
        $form = $this->createForm(TodoType::class, $todo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $uploadedFile = $form['picture_name']->getData();
            if ($uploadedFile){
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
                $newFilename = $todo->getId() . "_" . "image.jpg";

                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $todo->setPictureName($newFilename);
            }
            $this->em->flush();
            $this->addFlash(
                'successEdit',
                "La t??che ?? bien ??t?? modifi??e."
            );
            return $this->redirectToRoute('app_index');
        }

        return $this->render('addTask.html.twig', [
            'form' => $form->createView(),
            'userName' => $user->getUserIdentifier()
        ]);
    }

    /**
     * @param Todo $todo
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/deleteTask/{id}', name: 'app_deleteTask', methods: ['DELETE'])]
    public function deleteTask(Todo $todo, Request $request) : RedirectResponse
    {
        if ($this->getUser() !== $todo->getUser()){
            return $this->redirectToRoute('app_index');
        }
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete-student'.$todo->getId(), $submittedToken)) {
            $this->em->remove($todo);
            $this->em->flush();
        }

        $this->addFlash(
            'successDelete',
            "La t??che ?? bien ??t?? supprim??e."
        );
        return $this->redirectToRoute('app_index');
    }
}