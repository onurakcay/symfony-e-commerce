<?php

namespace App\Controller;

use App\Entity\OrderDetail;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Admin\CategoryRepository;

/**
 * @Route("/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="orders_index", methods="GET")
     */
    public function index(OrdersRepository $ordersRepository,CategoryRepository $categoryRepository): Response
    {
        $user = $this->getUser();
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userid = $user->getid();
            $em=$this->getDoctrine()->getManager();
            $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement=$em->getConnection()->prepare($sql);
            $statement->bindValue('userid',$userid);
            $statement->execute();
            $sepet=$statement->fetchAll();
        }

        $cats = $this ->categorytree();
        $cats[0] = '<ul id="menu-v">';

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('orders/index.html.twig', ['orders' => $ordersRepository->findBy(['userid' => $userid]),'cats' => $cats,'sepet'=>$sepet]);
        }
        else
        {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/new", name="orders_new", methods="GET|POST")
     */
    public function new(Request $request, ShopcartRepository $shopcartRepository): Response
    {
        $orders = new Orders();
        $form = $this->createForm(OrdersType::class, $orders);
        $form->handleRequest($request);

        $users = $this->getUser();
        $userid = $users->getid();
        $total=$shopcartRepository->getUserShopCartTotal($userid);



        $cats = $this ->categorytree();
        $cats[0] = '<ul id="menu-v">';


        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $sql="SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
        $statement=$em->getConnection()->prepare($sql);
        $statement->bindValue('userid',$user->getid());
        $statement->execute();
        $sepet=$statement->fetchAll();


        $em=$this->getDoctrine()->getManager();
        $sql="SELECT SUM(product.sprice*shopcart.quantity) as total
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
        $statement=$em->getConnection()->prepare($sql);
        $statement->bindValue('userid',$users->getid());
        $statement->execute();
        $sonuc=$statement->fetchAll();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT product.title,product.sprice,product.image,shopcart.*
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ORDER BY ID DESC ";
        $statement=$em->getConnection()->prepare($sql);
        $statement->bindValue('userid',$user->getid());
        $statement->execute();
        $shopcarts=$statement->fetchAll();




        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            $orders->setUserid($userid);
            $orders->setAmount($total);
            $orders->setStatus("New");

            $em->persist($orders);
            $em->flush();

            $orderid=$orders->getId();

            $shopcart = $shopcartRepository->getUserShopCart($userid);

            foreach ($shopcart as $item){
                $orderdetail = new OrderDetail();
                $orderdetail -> setOrderid($orderid);
                $orderdetail -> setUserid($users->getid());
                $orderdetail -> setProductid($item["productid"]);
                $orderdetail -> setPrice($item["sprice"]);
                $orderdetail -> setQuantity($item["quantity"]);
                $orderdetail -> setAmount($item["total"]);
                $orderdetail -> setName($item["title"]);
                $orderdetail -> setStatus("Ordered");

                $em->persist($orderdetail);
                $em->flush();
            }

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('DELETE FROM App\Entity\Shopcart s WHERE s.userid=:userid')
                ->setparameter('userid',$userid);
            $query->execute();
            $this->addFlash('success',"Siparişiniz Başarıyla Gerçekleşti ");

            return $this->redirectToRoute('orders_index');
        }

        return $this->render('orders/new.html.twig', [
            'order' => $orders,
            'form' => $form->createView(),
            'cats'=>$cats,
            'sepet'=>$sepet,
            'sonuc' => $sonuc,
            'shopcarts' => $shopcarts,

        ]);
    }

    /**
     * @Route("/{id}", name="orders_show", methods="GET")
     */
    public function show(Orders $order,OrdersRepository $ordersRepository,OrderDetailRepository $orderDetailRepository): Response
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser();
            $userid = $user->getid();
            $cats = $this->categorytree();
            $cats[0] = '<ul id="menu-v">';
            $orderid = $order->getId();

            $userid = $user->getid();
            $em = $this->getDoctrine()->getManager();
            $sql = "SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('userid', $userid);
            $statement->execute();
            $sepet = $statement->fetchAll();

            $orderdetail  = $orderDetailRepository->findBy(
                ['orderid'=>$orderid]
            );
        }




        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('orders/show.html.twig',
                ['order' => $order,
                    'cats' => $cats,
                    'orderdetail' => $orderdetail,
                    'sepet' => $sepet,]);
        }
        else
        {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}/edit", name="orders_edit", methods="GET|POST")
     */
    public function edit(Request $request, Orders $order): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('orders_edit', ['id' => $order->getId()]);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orders_delete", methods="DELETE")
     */
    public function delete(Request $request, Orders $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($order);
            $em->flush();
        }

        return $this->redirectToRoute('orders_index');
    }
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
}
