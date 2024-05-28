<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Entity\Article;
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

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/product/create", name="create_product")
     */
    public function createProduct(ArticleManager $articleManager): Response
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

    #[Route('/article-form', name: 'article-form')]
    public function articleForm(Request $request, SluggerInterface $slugger)
    {
        // Create a new article object
        $article = new Article();

        // Create the article form
        $form = $this->createForm(ArticleType::class, $article);

        // Handle the form submission
        $form->handleRequest($request);

        //print_r($article);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the form data
            $articleName = $form->get('articleName')->getData();
            $description = $form->get('description')->getData();
            $articleImages = $form->get('articleImages')->getData();
            $sizeAndQuantities = $form->get('sizeAndQuantities')->getData();
            $uploadedFilenames = [];

            $article->setArticleName($articleName);
            $article->setDescription($description);
            $article->setArticleImages($articleImages);
            $article->setSizeAndQuantities($sizeAndQuantities);

            // Access the files from the request
            $files = $request->files->get('articleImage');

            echo 'form is submitted';
            if (!$files) {
                return new JsonResponse(['error' => 'No files uploaded'], 400);
            }

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            foreach ($files as $articleImage) {
                //print_r($articleImages);
                if ($articleImage) {
                    $originalFilename = pathinfo($articleImage->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    //$safeFilename = $slugger->slug($originalFilename);
                    $newFilename = md5(uniqid()).'.'.$articleImage->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $articleImage->move(dirname(__DIR__).'/../public/uploads', $newFilename);
                        $uploadedFilenames[] = $newFilename;
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        return new JsonResponse(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
                        //print_r('Error by store the image: ' . $e->getMessage());
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $article->setArticleImages($uploadedFilenames);
                }
            }


            // Log the form data
            $this->logger->info("ArticleName: " . $articleName . ", ArticleDescription: " . $description);

            // Save the form data to a file
            $filesystem = new Filesystem();
            try {
                $filesystem->dumpFile('Logs/logsfile.txt', $articleName . " " . $description);
            } catch (IOExceptionInterface $IOException) {
                $this->logger->error("An error occurred while creating the directory: " . $IOException->getMessage());
            }

            $routeName = 'success-route'; // Replace with your actual route name
            $routeParams = [
                'articleName' => $articleName,
                'description' => $description,
                'articleImages' => $articleImages,
                'sizeAndQuantities' => $sizeAndQuantities
            ];

            // Print uploaded image paths
            $uploadedFilePaths = [];
            foreach ($uploadedFilenames as $filename) {
                $uploadedFilePaths[] = dirname(__DIR__).'/../public/uploads' . '/' . $filename;
            }

            //print_r('articleName from post', $_POST['articleName']);
            // Redirect to the success route

            // Handle file uploads
            // Assuming your form data also contains files
            $uploadedFiles = $request->files->all();
            return new JsonResponse([
                'success' => 'Article created successfully',
                'files' => $uploadedFiles
            ]);
        }

        // Render the form template
        return $this->render('Forms/article-form.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/success', name: 'success-route')]
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

