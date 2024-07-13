<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Entity\Comment;
use okpt\furnics\project\Entity\Review;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Form\ArticleType;
use okpt\furnics\project\Services\ArticleManager;
use okpt\furnics\project\Services\CartManager;
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
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    private LoggerInterface $logger;
    private $articleManager;
    private $entityManager;
    private $cartManager;

    public function __construct(LoggerInterface $logger, ArticleManager $articleManager, EntityManagerInterface $entityManager, CartManager $cartManager)
    {
        $this->logger = $logger;
        $this->articleManager = $articleManager;
        $this->entityManager = $entityManager;
        $this->cartManager = $cartManager;
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

    #[Route('/admin', name: 'admin')]
    public function articleForm(Request $request, SluggerInterface $slugger, LoggerInterface $logger)
    {
        // ================================= List of All Articles ======================================
        $allArticles = $this->articleManager->getAllArticles();
        $user = $this->getUser();


        if ($request->isMethod('POST')) {
            // Extract form data from request
            $articleName = $request->get('articleName');
            $description = $request->get('description');
            $articlePrice = $request->get('articlePrice');
            $articleCategory = $request->get('articleCategory');
            $categoryDescription = $request->get('categoryDescription');
            $sizeAndQuantities = $request->get('sizeAndQuantities', []);
            $descriptions = [
                'description' => $description,
                'categoryDescription' => $categoryDescription,
                'sizeAndQuantity' => $sizeAndQuantities
            ];

            // Validate required fields
            if (!$articleName || !$description || !$articlePrice || !$articleCategory) {
                return new JsonResponse(['error' => 'Required fields are missing.'], 400);
            }

            $action = 'created';

            // Create a new Article Entity
            $article = new Article();

            $articleId = $request->get('articleId');

            if ($articleId) {
                // Article exist in the DB; just edit it !
                $article = $this->entityManager->getRepository(Article::class)->find($articleId);

                $action = 'updated';
            }

            $files = $request->files->get('articleImages');


            if ($files) {
                //Article exists in the DB and has been edited or
                // the article is new and its photos arrived with the request
                // else article exists but has not been modified in any way linked
                // to its images

                // Handle file uploads
                $uploadedFilenames = [];

                if($articleId) {
                    // Initialize Symfony Filesystem component
                    $filesystem = new Filesystem();

                    // Define the uploads directory path
                    $uploadsDirectory = dirname(__DIR__) . '/../public';

                    $oldFilenames = json_decode($article->getArticleImages());

                    // Iterate through each uploaded filename and delete the corresponding file
                    foreach ($oldFilenames as $filename) {
                        $filePath = $uploadsDirectory . '/' . basename($filename);

                        try {
                            // Check if file exists before attempting to delete
                            if ($filesystem->exists($filePath)) {
                                $filesystem->remove($filePath);
                            }
                        } catch (IOExceptionInterface $e) {
                            return new JsonResponse(['error' => 'Failed to delete image: ' . $e->getMessage()], 500);
                        }
                    }
                }

                // Insert new files
                foreach ($files as $file) {
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
                // Set uploaded filenames on article
                $article->setArticleImages(json_encode($uploadedFilenames));
            }

            // Set values on article object
            $article->setArticleName($articleName);
            $article->setDescription(json_encode($descriptions));
            $article->setArticlePrice((float)$articlePrice);
            $article->setArticleCategory($articleCategory);
            $article->setCategoryDescription($categoryDescription);
            //$article->setSizeAndQuantities($sizeAndQuantities);

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
                'success' => 'Article ' . $action . ' successfully'
            ]);
        }

        // Render the form template for GET request
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $allArticles
        ]);
    }


    #[Route('/success', name: 'success_route')]
    public function successRoute(Request $request): Response
    {
        // Retrieve the form data from route parameters
        $articleName = $request->query->get('articleName');
        $description = $request->query->get('description');
        $articleImages = $request->query->get('articleImages');
        $sizeAndQuantities = $request->query->get('sizeAndQuantities');

        return $this->render('success_page/index.html.twig', [
            'articleName' => $articleName,
            'description' => $description,
            'articleImages' => $articleImages,
            'sizeAndQuantities' => $sizeAndQuantities
        ]);
    }

    #[Route('/all-articles', name: 'get_all_articles')]
    public function showArticles(): JsonResponse
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        $data = [];

        foreach ($articles as $article) {
            $data[] = [
                'articleId' => $article->getArticleId(),
                'articleName' => $article->getArticleName(),
                'articlePrice' => $article->getArticlePrice(),
                'descriptions' => $article->getDescription(),
                'articleImages' => $article->getArticleImages(),
                'articleCategory' => $article->getArticleCategory(),
                'categoryDescription' => $article->getCategoryDescription()
            ];
        }

        return new JsonResponse($data);
    }

    // Controller action for deleting an article
    #[Route('article/delete/{id}', name: 'delete_article')]
    public function deleteArticle($id): JsonResponse
    {
        //$articleId = $request->query->get('id');
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        //echo "Article to deleted: " . $id;
        try {
            $this->articleManager->deleteArticle($article);
            return new JsonResponse(['message' => 'Article deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete article: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/update-article-quantity', name: 'update_article_quantity', methods: ['POST'])]
    public function updateArticleQuantity(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->debug(json_encode($data));
        $articleId = $data['articleId'];
        $action = $data['action'];
        $cartItemId = $data['cartItemId'];

        //$this->logger->debug('$data[articleId]: ' . $articleId);

        $cartItem = $this->entityManager->getRepository(CartItem::class)->find($cartItemId);

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

    #[Route('/article-detail/{id}', name: 'article_detail')]
    function articleDetail($id)
    {
        $user = $this->getUser();

        //print_r($user);
        if(!$user) {
            $this->redirectToRoute('app_index');
        }

        $article = $this->entityManager->getRepository(Article::class)->find($id);

        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        $allReviews = $this->entityManager->getRepository(Review::class)->findBy(['article' => $article]);

        return $this->render('article/article-detail.html.twig', [
            'controller_name' => 'CartController',
            'user' => $user,
            'allCartItems' => $allCartItems,
            'article' => $article,
            'allReviews' => $allReviews,

        ]);
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


    private function addComment(string $comment, int $articleId)
    {

        $newComment = new Comment();
        $newComment->setCommentText($comment);

        $currentUser = $this->getUser();
        $currentUser->addComment($newComment);

        $article = $this->entityManager->getRepository(Article::class)->find($articleId);
        $article->addComment($newComment);

        $this->entityManager->persist($currentUser);
        $this->entityManager->persist($article);
        $this->entityManager->persist($newComment);
        $this->entityManager->flush();
    }

    #[Route('/review/new', name: 'article_review', methods: ['POST'])]
    function addReview(Request $request)
    {
        $articleId = $request->request->get('article_id');
        $userPseudo = $request->request->get('name');
        $comment = $request->request->get('message');
        $rate = $request->request->get('rating');

        $userData['name'] = $userPseudo;
        $newReview = new Review();
        $newReview->setReviewText($comment);
        $newReview->setRating($rate);

        $newReview->setUserData(json_encode($userData));


        $currentUser = $this->getUser();
        $currentUser->addReview($newReview);

        $article = $this->entityManager->getRepository(Article::class)->find($articleId);
        $article->addReview($newReview);

        $this->entityManager->persist($currentUser);
        $this->entityManager->persist($article);
        $this->entityManager->persist($newReview);
        $this->entityManager->flush();

        return $this->json(['status' => 'Thank you for your review!'], Response::HTTP_OK);
    }

}
