<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Admin\User;
use App\Form\Admin\MessagesType;
use App\Form\Admin\UserType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\Admin\UserRepository;
use App\Repository\Admin\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository)
    {

        $data = $settingRepository->findAll();
        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status = 'True' ORDER BY ID DESC LIMIT 5";
        $statement=$em->getConnection()->prepare($sql);
        //$statement->bindValue('pid',$parent);
        $statement->execute();
        $sliders=$statement->fetchAll();


        $em= $this->getDoctrine()->getManager();
        $sql='SELECT * FROM product WHERE status = "True"';
        $statement=$em->getConnection()->prepare($sql);
        $statement->execute();
        $products=$statement->fetchAll();






        $cats = $this ->categorytree();
        $cats[0] = '<ul id="menu-v">';
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->render('home/index.html.twig', [
            'data' => $data,
            'cats' => $cats,
            'sliders' => $sliders,
            'products'=>$products,
            'sepet'=>$sepet,
        ]);}
        else{
            return $this->render('home/index.html.twig', [
                'data' => $data,
                'cats' => $cats,
                'sliders' => $sliders,
                'products'=>$products,

            ]);
        }
    }
    /**
     * @Route("/hakkimizda", name="hakkimizda")
     */
    public function hakkimizda(SettingRepository $settingRepository)
    {
        $cats = $this ->categorytree();
        //print_r($cats);
        //die();
        $cats[0] = '<ul id="menu-v">';
        $data = $settingRepository->findAll();

        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/hakkimizda.html.twig', [
                'data' => $data,
                'cats' => $cats,
                'sepet'=>$sepet,
            ]);}
        else{
            return $this->render('home/hakkimizda.html.twig', [
                'data' => $data,
                'cats' => $cats,
            ]);
        }
    }
    /**
     * @Route("/iletisim", name="iletisim", methods="GET|POST")
     */
    public function iletisim(SettingRepository $settingRepository,Request $request)
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
                $this->addFlash('success', 'Mesajın başarıyla Gönderildi.');
                return $this->redirectToRoute('iletisim');
            }
        }


        $data = $settingRepository->findAll();
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/iletisim.html.twig', [
                'data' => $data,
                'cats' => $cats,
                'message' => $message,
                'sepet'=>$sepet,
            ]);}
        else{
            return $this->render('home/iletisim.html.twig', [
                'data' => $data,
                'message' => $message,
                'cats' => $cats,
            ]);
        }
    }
    /**
     * @Route("/referanslar", name="referanslar")
     */
    public function referanslar(SettingRepository $settingRepository)
    {
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $data = $settingRepository->findAll();

        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }


        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/referanslar.html.twig', [
                'data' => $data,
                'cats' => $cats,
                'sepet'=>$sepet,
            ]);}
        else{
            return $this->render('home/referanslar.html.twig', [
                'data' => $data,
                'cats' => $cats,
            ]);
        }
    }
    //Recursive category tree
    public function  categorytree($parent = 0, $user_tree_array = '')
            {
                if(!is_array($user_tree_array))
                    $user_tree_array=array();

                $em=$this->getDoctrine()->getManager();
                $sql="SELECT * FROM category WHERE status = 'True' AND parentid=".$parent;
                $statement=$em->getConnection()->prepare($sql);
                //$statement->bindValue('pid',$parent);
                $statement->execute();
                $result=$statement->fetchAll();

                if(count($result)>0){
                    $user_tree_array[] = "<ul>";
                    foreach ($result as $row){
                        $user_tree_array[] = "<li> <a href='/category/".$row['id']."'>".$row['title']."</a>";
                        $user_tree_array = $this->categorytree($row['id'],$user_tree_array);
                    }
            $user_tree_array[]="</li></ul>";
        }
        return $user_tree_array;
    }
    /**
     * @Route("/category/{catid}", name="category_products", methods="GET")
     */
    public function CategoryProducts($catid, CategoryRepository $categoryRepository)
    {
        $data = $categoryRepository->findBy(['id'=>$catid]);
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $em= $this->getDoctrine()->getManager();
        $sql='SELECT * FROM product WHERE status = "True" AND category_id= :catid';
        $statement=$em->getConnection()->prepare($sql);
        $statement->bindValue('catid',$catid);
        $statement->execute();
        $products=$statement->fetchAll();


        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user=$this->getUser();
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();

            return $this->render('home/products.html.twig', [
                'data' => $data,
                'products' => $products,
                'cats' => $cats,
                'sepet'=>$sepet,
            ]);
        }
        else{
            return $this->render('home/products.html.twig', [
                'data' => $data,
                'products' => $products,
                'cats' => $cats,

            ]);
        }

        /*dump($result);
        die();*/

    }

    /**
     * @Route("/product/{id}", name="product_detail", methods="GET")
     */
    public function ProductDetail($id, ProductRepository $productRepository,ImageRepository $imageRepository)
    {
        $data = $productRepository->findBy(['id'=>$id]);
        $images = $imageRepository->findBy(['product_id'=>$id]);
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';

        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid=$user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/product_detail.html.twig', [
                'data' => $data,
                'cats' => $cats,
                'images' => $images,
                'sepet'=>$sepet,
            ]);}
        else{
            return $this->render('home/product_detail.html.twig', [
                'data' => $data,
                'images' => $images,
                'cats' => $cats,
            ]);
        }
    }

    /**
     * @Route("/newuser", name="new_user", methods="GET|POST")
     */
    public function newuser(Request $request,UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';




        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $users = $this->getUser();
            $userid=$users->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        //SALDIRILARA KARŞI CSRF KONTROLÜ YAPARIZ. FORMDA TOKEN KISMINDA NE İSMİ GİRDİYSEK BURADA ONUN KONTROLÜNÜ YAPIYORUZ
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('user-form', $submittedToken)) {
            if ($form->isSubmitted()) {
                $emaildata=$userRepository->findBy(['email'=>$user->getEmail()]
                );

             if($emaildata==null){
                $em = $this->getDoctrine()->getManager();
                //KULLANICI KAYIT OLURKEN BİZ ONA ROL SORMUYORUZ. BURADA SOLÜ KENDİMİZ AYARLIYORUZ.

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Üye Kaydı Gerçekleşti');

                return $this->redirectToRoute('app_login');
             }
             else
             {
                 $this->addFlash('error', 'Bu mail adresi zaten kayıtlı');

                 return $this->redirectToRoute('new_user');

             }
            }
        }
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->render('home/newuser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'cats' => $cats,
            'sepet' => $sepet,
        ]);}
        else
        {
            return $this->render('home/newuser.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'cats' => $cats,


            ]);
        }
    }

}