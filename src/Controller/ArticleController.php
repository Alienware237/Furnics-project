<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Form\ArticleType;
use okpt\furnics\project\Services\ArticleManager;
use PhpParser\Node\Scalar\MagicConst\Dir;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    private LoggerInterface $logger;
    private $articleManager;
    private $entityManager;

    public function __construct(LoggerInterface $logger, ArticleManager $articleManager, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->articleManager = $articleManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/product/create", name="create_product")
     */
    public function createProduct(): Response
    {
        $articleDetail1 = [
            'description' => 'Article Description 2 for Test',
            'sizeAndQuantity' => [
                'M' => 2,
                'X' => 5
            ]
        ];

        $articleDetail2 = [
            'description' => 'Article Description 2 for Test',
            'sizeAndQuantity' => [
                'M' => 2,
                'X' => 5
            ]
        ];
        $articleDetail3 = [
            'sizeAndQuantity' => [
                'M' => 2,
                'X' => 5
            ]
        ];

        $this->articleManager->createArticle('First Article 1', json_encode($articleDetail1), '19.99', 'Category1', ['/public/uploads/6ae31620ed2008b113ff18668e9cb240.jpg']);
        $this->articleManager->createArticle('Article Name 2', json_encode($articleDetail2), '17.99', 'Category2', ['/public/uploads/7f204c6b218a8877fb2481383033c1e7.jpg']);
        $this->articleManager->createArticle('Article Name 3', json_encode($articleDetail3), '18.99', 'Category3', ['public/uploads/af48075fdf77c49b05fccb069e0256bb.jpg']);

        return new Response('Article created successfully!');
    }

    #[Route('/article-form', name: 'article_form')]
    public function articleForm(Request $request, SluggerInterface $slugger, LoggerInterface $logger)
    {
        // Create a new article object
        $article = new Article();

        if ($request->isMethod('POST')) {
            // Extract form data from request
            $articleName = $request->get('articleName');
            $description = $request->get('description');
            $articlePrice = $request->get('articlePrice');
            $articleCategory = $request->get('articleCategory');
            $categoryDescription = $request->get('categoryDescription');
            $sizeAndQuantities = $request->get('sizeAndQuantities', []);
            $descriptions['description'] = $description;
            $descriptions['sizeAndQuantity'] = $sizeAndQuantities;

            // Validate required fields
            if (!$articleName || !$description || !$articlePrice || !$articleCategory) {
                return new JsonResponse(['error' => 'Required fields are missing.'], 400);
            }

            // Set values on article object
            $article->setArticleName($articleName);
            $article->setDescription(json_encode($descriptions));
            $article->setArticlePrice((float)$articlePrice);
            $article->setArticleCategory($articleCategory);
            $article->setCategoryDescription($categoryDescription);
            //$article->setSizeAndQuantities($sizeAndQuantities);

            // Handle file uploads
            $uploadedFilenames = [];
            $files = $request->files->get('articleImages');


            if ($files) {
                foreach ($files as $file) {
                    //print_r($file);
                    if ($file) {
                        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $newFilename = md5(uniqid()) . '.' . $file->guessExtension();

                        try {
                            $file->move(dirname(__DIR__) . '/../public/uploads', $newFilename);
                            $uploadedFilenames[] = 'uploads/' . $newFilename;
                        } catch (FileException $e) {
                            return new JsonResponse(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
                        }
                    }
                }
            }

            // Set uploaded filenames on article
            if (!empty($uploadedFilenames)) {
                $article->setArticleImages(json_encode($uploadedFilenames));
            }

            // Log form data
            $logger->info("ArticleName: " . $articleName . ", Description: " . $description);

            // Save the form data to a log file
            $filesystem = new Filesystem();
            try {
                $filesystem->appendToFile('logs/logfile.txt', $articleName . " " . $description . PHP_EOL);
            } catch (IOExceptionInterface $exception) {
                $logger->error("An error occurred while writing to the file: " . $exception->getMessage());
            }

            // Persist article to database
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => 'Article created successfully',
                'files' => $uploadedFilenames
            ]);
        }

        // Render the form template for GET request
        return $this->render('Forms/article-form.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }


    #[Route('/success', name: 'success_route')]
    public function successRoute(Request $request): Response
    {
        //print_r($request);
        // Retrieve the form data from route parameters
        $articleName = $request->query->get('articleName');
        $description = $request->query->get('description');
        $articleImages = $request->query->get('articleImages');
        $sizeAndQuantities = $request->query->get('sizeAndQuantities');
        print_r($articleImages);
        print_r($sizeAndQuantities);
        print_r($articleName);

        return $this->render('success_page/index.html.twig', [
            'articleName' => $articleName,
            'description' => $description,
            'articleImages' => $articleImages,
            'sizeAndQuantities' => $sizeAndQuantities
        ]);
    }

    public function showArticles(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    // Controller action for deleting an article
    #[Route('article/delete/{id}', name: 'delete_article')]
    public function deleteArticle($id): Response
    {
        //$articleId = $request->query->get('id');
        echo "Article to deleted: " . $id;
        $response = $this->articleManager->deleteArticle($id);
        echo "Response: ";
        var_dump($response);

        // Return a response indicating success or failure
        if ($response) {
            return new Response('Article deleted successfully');
        } else {
            return new Response('Failed to delete article', 500); // 500 is the status code for internal server error
        }
    }

    #[Route('/update-article-quantity', name: 'update_article_quantity', methods: ['POST'])]
    public function updateArticleQuantity(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->debug(json_encode($data));
        //print_r($data);
        $articleId = $data['articleId'];
        $action = $data['action'];

        //$this->logger->debug('$data[articleId]: ' . $articleId);

        $userEmail = $this->getUser()->getEmail();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);

        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'article' => $article]);

        if ($cartItem) {
            if ($action === 'increase') {
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
            } else {
                $cartItem->setQuantity(max(0, $cartItem->getQuantity() - 1));
            }
            $this->entityManager->persist($cartItem);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'newQuantity' => $cartItem->getQuantity()
            ]);
        }

        return new JsonResponse(['success' => false]);
    }

    #[Route('/remove-article', name: 'remove_article', methods: ['POST'])]
    public function removeArticle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $articleId = $data['articleId'];

        $userEmail = $this->getUser()->getUserIdentifier();

        //print_r($userEmail);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);


        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'article' => $article]);

        // Fetch the article and remove it
        if ($cartItem) {
            $this->entityManager->remove($cartItem);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false]);
    }

    private function removeArticleFromArray(Article $deletedArticle): void
    {
        $articles = $this->getArticles(); // Assuming you have a method to fetch articles
        foreach ($articles as $key => $article) {
            if ($article->getId() === $deletedArticle->getId()) {
                unset($articles[$key]);
                break; // Assuming each article has a unique ID, we can break the loop after finding and removing the article
            }
        }
        $this->setArticles($articles); // Assuming you have a method to set articles
    }

}
