<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Answers;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\HomepageSiteSettings;
use App\Entity\Image;
use App\Entity\Orders;
use App\Entity\Page;
use App\Entity\Product;
use App\Entity\Questions;
use App\Entity\User;
use App\Form\AddressType;
use App\Form\AnswerType;
use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shopping-cart")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index() : Response
    {
        $em = $this->getDoctrine()->getManager();

        $brands = $em->getRepository(Brand::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        $homepageSetting = $em->getRepository(HomepageSiteSettings::class)->findAll();
        $pages = $em->getRepository(Page::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();

        return $this->render('default/index.html.twig',[
            'brands' => $brands,
            'categories' => $categories,
            'data' => $data,
            'homepageSetting' => $homepageSetting,
            'electronics' => $category,
            'soldoutcheck' => $soldOutCheck,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/products", name="product")
     * @Route("/products/category/{slug}", name="category")
     */
    public function product(Request $request) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $searchData = $request->request->get('searchText');
        $pages = $em->getRepository(Page::class)->findAll();

        $categoryTwig = $em->getRepository(Category::class)->findbycategory();
        $categories = $em->getRepository(Category::class)->findAll();
        $brands = $em->getRepository(Brand::class)->findAll();

        $slug =  $request->attributes->get('slug');
        if($searchData != null)
        {
            $data= $em->getRepository(Product::class)->findBySearchData($searchData);
            if($data == null)
            {
                $error = "no such product found";
                return $this->redirectToRoute('default',['error'=>$error]);
            }
            $categoryAll = $em->getRepository(Category::class)->findAll();
        }
        else if($slug !== null)
        {
            if($slug == 'Electronics' || 'Books')
            {
                $category1 = $em->getRepository(Category::class)->findOneBy(['name' => $slug]);
                $category = $em->getRepository(Category::class)->findBy(['parent' => $category1]);
                $category2 = array();
                foreach ($category as $k => $v)
                {
                    $categoryAll = $em->getRepository(Category::class)->findBy(['parent' => $v]);
                    $category2 = array_merge($category2,$categoryAll);
                }
                $categoryAll = array_merge($category2,$category);
            }
            else
            {
                $category1 = $em->getRepository(Category::class)->findOneBy(['name' => $slug]);
                $categoryAll = $em->getRepository(Category::class)->findBy(['parent' => $category1]);
            }

            if($categoryAll != null)
            {
                $data = $em->getRepository(Product::class)->findAll();
            }
            else{
                $data = $em->getRepository(Product::class)->findBy(['category' => $category1->getId()]);
            }
        }
        else {
            $categoryAll = $em->getRepository(Category::class)->findAll();
            $data = $em->getRepository(Product::class)->findAll();
        }
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
        return $this->render('default/product.html.twig', [
            'categories' => $categories,
            'data' => $data,
//            'cart' => $orderData,
            'soldoutcheck' => $soldOutCheck,
            'brands' => $brands,
            'categoryAll' =>$categoryAll,
            'electronics' => $categoryTwig,
            'searchData' => $searchData,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/cart", name="addtocart")
     */
    public function addToCart(Request $request) : Response
    {
        if($this->getUser() == null)
        {
            return $this->redirectToRoute('app_login');
        }
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $address->setUserId($this->getUser());

            $em->persist($address);
            $em->flush();

            return $this->redirectToRoute('payment');
        }
        $pages = $em->getRepository(Page::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $brands = $em->getRepository(Brand::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();

        $userId = $this->getUser()->getId();
        $user = $em->getRepository(User::class)->find($userId);

        $req = $request->request->get('qty') ;
        $reqQty = (int) $req;

        $quantity = (int) $request->request->get('quantity');
        $id = (int) $request->request->get('productId');
        $orderId = (int) $request->request->get('orderId');

        $orderData = $em->getRepository(Product::class)->findOneBy(['id'=>$id]);

        $order = new Orders();
        $findById = $em->getRepository(Orders::class)->findOneBy(['productId' => $id,'userId'=>$userId]);

        if($quantity == null)
        {
            if($req == null)
            {
                $cart = $em->getRepository(Orders::class)->findBy(['userId' => $userId]);
                $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
            }
            elseif ($reqQty == 0 ){
                return $this->redirectToRoute('remove',['id'=>$orderId]);
            }
            else {
//               $orderQty = $findById->getQuantity();
//                if($reqQty > $orderQty) {
//                    $value = $reqQty - $orderQty;
                    $findById->setQuantity($reqQty);
                    $findById->setProductId($orderData);
                    $findById->setUserId($user);

//                    $orderData->setAvailavleQty($orderData->getAvailavleQty() - $value);

                    $em->flush();
//                }
//                elseif ($reqQty < $findById->getQuantity())
//                {
//                    $value = $orderQty - $reqQty;

                    $findById->setQuantity($reqQty);
                    $findById->setProductId($orderData);
                    $findById->setUserId($user);

//                    $orderData->setAvailavleQty($orderData->getAvailavleQty() + $value);

                    $em->flush();
//                }
                $cart = $em->getRepository(Orders::class)->findBy(['userId' => $userId]);
                $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
//                $orderData = $productRepository->findOneBy(['id'=>$id]);
                }

            return $this->render('default/checkout.html.twig', [
                    'cart' => $cart,
                    'soldoutcheck' => $soldOutCheck,
                    'categories' => $categories,
                    'brands' => $brands,
                    'electronics' => $category,
                    'pages' => $pages,
                    'checkoutForm' => $form->createView(),
                    'products'=>$productName,
            ]);
        }
        else {
            if ($findById !== null) {
                $quantity += $findById->getQuantity();
                    if($quantity <= $orderData->getMaxQtyPerUserPerCart()) {
                        $findById->setQuantity($quantity);
                        $findById->setProductId($orderData);
                        $findById->setUserId($user);
//                        $orderData->setAvailavleQty($orderData->getAvailavleQty() - $quantity);
                        $em->flush();
                    }
                    else{
                        throw new \Exception('Quantity must be less than or  equal to '.$orderData->getMaxQtyPerUserPerCart().'. You have already '. $findById->getQuantity() .' in your cart.');
                    }
            }
            else {
                $order->setQuantity($quantity);
                $order->setProductId($orderData);
                $order->setUserId($user);

//                $orderData->setAvailavleQty($orderData->getAvailavleQty() - $quantity);

                $em->persist($order);
                $em->flush();
            }
            $cart = $em->getRepository(Orders::class)->findBy(['userId' => $userId]);
            $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
//            $orderData = $productRepository->findOneBy(['id'=>$id]);

            return $this->render('default/checkout.html.twig', [
                'cart' => $cart,
                'soldoutcheck' => $soldOutCheck,
                'categories' => $categories,
                'brands' => $brands,
                'electronics' => $category,
                'pages' => $pages,
                'checkoutForm' => $form->createView(),
                'products' => $productName,
//                'orderData' => $orderData,
            ]);
            }
    }

    /**
     * @Route("/remove/{id}", name="remove")
     */
    public function remove(Orders $id) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $orderRecord = $em->getRepository(Orders::class)->find($id);
//        $product = $productRepository->findOneBy(['id'=>$orderRecord->getProductId()->getId()]);
        $em->remove($orderRecord);
//        $product->setAvailavleQty($product->getAvailavleQty()+$orderRecord->getQuantity());
        $em->flush();

        return $this->redirectToRoute('addtocart');
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkout(Request $request) : Response
    {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUser()->getId();
        $categories = $em->getRepository(Category::class)->findAll();
        $pages = $em->getRepository(Page::class)->findAll();

        $brands = $em->getRepository(Brand::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $orderData = $em->getRepository(Orders::class)->findBy(['userId' => $userId]);
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $address->setUserId($this->getUser());

            $em->persist($address);
            $em->flush();

            return $this->redirectToRoute('checkout');
        }

        return $this->render('default/checkout.html.twig',[
            'addressForm' => $form->createView(),
            'categories' => $categories,
            'cart' => $orderData,
            'brands' => $brands,
            'electronics' => $category,
            'pages' => $pages,
        ]);
    }

    /**
     * @Route("/product-detail/{id}", name="detail")
     */
    public function details(Request $request, Product $id) : Response
    {

        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($id);
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $user = $this->getUser();
        if($user == null)
        {
            $this->redirectToRoute('app_login');
        }
        $question = new Questions();
        $questionForm = $this->createForm(QuestionType::class, $question);

        $questionForm->handleRequest($request);
        $productId = $product->getId();

        if($questionForm->isSubmitted() && $questionForm->isValid())
        {

            $question->setUser($user);
            $question->setProduct($product);
            $em->persist($question);

            $em->flush();
            return $this->redirectToRoute("detail",array('id'=>$productId));
        }

        $brands = $em->getRepository(Brand::class)->findAll();

        $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
        $image = $em->getRepository(Image::class)->findmainImage($product);

        $questionList = $em->getRepository(Questions::class)->findBy(['product'=>$product]);
        $answerList = $em->getRepository(Answers::class)->findAll();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        return $this->render('default/single.html.twig',[
            'product' => $product,
            'soldoutcheck' => $soldOutCheck,
            'questionForm' => $questionForm->createView(),
            'questionList' => $questionList,
            'answerList' => $answerList,
            'categories' => $categories,
            'brands' => $brands,
            'electronics' => $category,
            'pages' => $pages,
            'image' => $image,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/answer/{id}", name="answer")
     */
    public function answer(Request $request, Questions $questionId) : Response
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $question = $em->getRepository(Questions::class)->find($questionId);
        $id = $question->getProduct()->getId();
        $product = $em->getRepository(Product::class)->find($id);

        $categories = $em->getRepository(Category::class)->findAll();
        $brands = $em->getRepository(Brand::class)->findAll();

        $answer = new Answers();
        $answerForm = $this->createForm(AnswerType::class, $answer);
        $answer->setQuestion($question);
        $answerForm->handleRequest($request);

        if($answerForm->isSubmitted() && $answerForm->isValid())
        {
            $answer->setUser($user);
            $answer->setQuestion($question);

            $em->persist($answer);
            $em->flush();

            return $this->redirectToRoute('detail',array('id'=>$id));
        }
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        return $this->render("default/answer.html.twig",[
            'question' => $question,
            'answerForm' => $answerForm->createView(),
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/brand/{slug}", name="brand")
     */
    public function brand(Request $request) : Response
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository(Category::class)->findAll();
        $brands = $em->getRepository(Brand::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $slug = $request->attributes->get('slug');
        $brand = $em->getRepository(Brand::class)->findOneBy(['name'=>$slug]);

        $products = $em->getRepository(Product::class)->findBy(['brand'=>$brand]);
        $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();
        $pages = $em->getRepository(Page::class)->findAll();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        return $this->render('default/brand.html.twig',[
            'categories' => $categories,
            'products' => $products,
            'soldoutcheck' => $soldOutCheck,
            'brands' => $brands,
            'electronics' => $category,
            'pages' => $pages,
            'productsearch' => $productName,
        ]);
    }

    /**
     * @Route("/about-us" , name="about")
     */
    public function about() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        return $this->render('default/about.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/contact-us" , name="contact")
     */
    public function contact()
    {
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/contact.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/new-arrival", name="new_arrival")
     */
    public function newArrival() : Response
    {
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();
        $brands = $em->getRepository(Brand::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }
        $homepageSetting = $em->getRepository(HomepageSiteSettings::class)->findAll();

        $category = $em->getRepository(Category::class)->findbycategory();
        $soldOutCheck = $em->getRepository(Orders::class)->findWithProduct();

        return $this->render('default/newarraival.html.twig',[
            'brands' => $brands,
            'categories' => $categories,
            'data' => $data,
            'homepageSetting' => $homepageSetting,
            'electronics' => $category,
            'soldoutcheck' => $soldOutCheck,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/{slug}", name="page")
     */
    public function pages(Request $request) : Response
    {
        $slug = $request->attributes->get('slug');

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->findOneBy(['slug'=>$slug]);
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/'.$slug.'.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'page' => $page,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/faqs", name="faqs")
     */
    public function faqs()
    {

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/faqs.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/help", name="help")
     */
    public function help()
    {

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/help.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/terms-of-use", name="terms")
     */
    public function terms()
    {

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/terms.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/privacy-policy", name="privacy")
     */
    public function privacy()
    {
        $em = $this->getDoctrine()->getManager();

        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('privacy-policy.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }

    /**
     * @Route("/payment", name="payment")
     */
    public function payment()
    {
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(Page::class)->findAll();

        $categories = $em->getRepository(Category::class)->findAll();
        $category = $em->getRepository(Category::class)->findbycategory();

        $data = $em->getRepository(Product::class)->findAll();
        $productName = array();
        foreach ($data as $k=>$v)
        {
            $productDesc = array($v->getDescription());
            $productName = array_merge($productName,$productDesc);
        }

        return $this->render('default/payment.html.twig',[
            'categories' => $categories,
            'electronics' => $category,
            'pages' => $pages,
            'products' => $productName,
        ]);
    }
}
