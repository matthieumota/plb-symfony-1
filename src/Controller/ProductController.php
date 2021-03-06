<?php

namespace App\Controller;

use App\Form\ProductType;
use App\Model\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Route("/prefix")
 */
class ProductController extends AbstractController
{
    private $products = [];

    public function __construct()
    {
        $this->products = [
            new Product('iPhone X', 'iphone-x', 'Un iPhone de 2017', 999),
            new Product('iPhone XR', 'iphone-xr', 'Un iPhone de 2018', 1099),
            new Product('iPhone XS', 'iphone-xs', 'Un iPhone de 2019', 1199),
        ];
    }

    /**
     * @Route("/product/create", name="product_create")
     */
    public function create(Request $request)
    {
        $product = new Product();

        /** @var FormInterface $form */
        /* $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->getForm(); */
        
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($product);
            dump($form->getData() === $product);
        }

        return $this->render('product/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/random", name="product_random")
     */
    public function random()
    {
        $product = $this->products[array_rand($this->products)];

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product/{page}", name="product_list", requirements={"page"="\d+"})
     */
    public function list(int $page = 1)
    {
        $itemByPage = 2;
        $maxPage = ceil(count($this->products) / $itemByPage);
        $offset = (1 === $page) ? 0 : $page;

        if ($page <= 0 || $page > $maxPage) {
            throw $this->createNotFoundException('Cette page n\'existe pas.');
        }

        $products = array_slice($this->products, $offset, $itemByPage);

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'page' => $page,
            'max_page' => $maxPage,
        ]);
    }

    /**
     * @Route("/product/{slug}", name="product_show")
     */
    public function show(string $slug): Response
    {
        foreach ($this->products as $product) {
            if ($slug === $product->getSlug()) {
                return $this->render('product/show.html.twig', [
                    'product' => $product,
                ]);
            }
        }

        throw $this->createNotFoundException('Le produit n\'existe pas.');
    }

    /**
     * @Route("/product.json", name="product_api")
     */
    public function api(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        return $this->json($this->products);
    }

    /**
     * @Route("/product/order/{slug}", name="product_order")
     */
    public function order(string $slug)
    {
        // $product = array_filter($this->products, fn ($product) => $product->getSlug() === $slug)[0] ?? false;
        $product = array_filter($this->products, function ($product) use ($slug) {
            return $product->getSlug() === $slug;
        })[0] ?? false;

        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        $this->addFlash('success', "Le produit {$product->getName()} a été commandé.");
        // $this->addFlash('danger', 'Test');

        return $this->redirectToRoute('product_list');
    }
}
