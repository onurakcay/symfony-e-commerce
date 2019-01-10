<?php

namespace App\Controller\Userpanel;

use App\Entity\Admin\User;
use App\Form\Admin\UserType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/**
 * @Route("/userpanel")
 */
class UserpanelController extends AbstractController
{
    /**
     * @Route("/", name="userpanel")
     */
    public function index()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $cats = $this->categorytree();
            $cats[0] = '<ul id="menu-v">';
            $user = $this->getUser();
            $userid = $user->getid();
            $em = $this->getDoctrine()->getManager();
            $sql = "SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('userid', $userid);
            $statement->execute();
            $sepet = $statement->fetchAll();
            return $this->render('userpanel/show.html.twig', [
                'cats' => $cats,'sepet'=>$sepet,
            ]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/show", name="userpanel_show", methods="GET")
     */
    public function show()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $cats = $this->categorytree();
            $cats[0] = '<ul id="menu-v">';
            $user = $this->getUser();
            $userid = $user->getid();
            $em = $this->getDoctrine()->getManager();
            $sql = "SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('userid', $userid);
            $statement->execute();
            $sepet = $statement->fetchAll();
            return $this->render('userpanel/show.html.twig', ['cats' => $cats,'sepet'=>$sepet,]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }


    /**
     * @Route("/edit", name="userpanel_edit", methods="GET|POST")
     */
    public function edit(Request $request)
    {
        $cats=$this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $usersession = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($usersession->getid());

        //İS SUBMİTTED İLE AYNI GÖREVDEDİR.
        if ($request->isMethod('POST')) {


            //HERHANGİ BİR SALDIRIYA KARŞI DEVAMLI CSRF KONTROLÜ YAPACAKTIR.İLETİŞİM KISMINDAKİ İNPUT HİDDEN BURAYA AİTTİR.
            $submittedToken = $request->request->get('token');


            if ($this->isCsrfTokenValid('user-form', $submittedToken)) {
                $user->setName($request->request->get("name"));
                /*$user->setPassword($request->request->get("password"));*/
                $user->setAddress($request->request->get("address"));
                $user->setEmail($request->request->get("email"));
                $user->setPhone($request->request->get("phone"));
                $this->getDoctrine()->getManager()->flush();
                //SADECE BİR KERE GÖRÜNMESİ İÇİN FLAŞ UYARILARI
                $this->addFlash('success', 'Güncelleme Başarılı');
                return $this->redirectToRoute('userpanel_show');
            }
        }


        $user = $this->getUser();
        $userid = $user->getid();
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('userid', $userid);
        $statement->execute();
        $sepet = $statement->fetchAll();

        return $this->render('userpanel/edit.html.twig', ['user' => $user,'cats'=>$cats,'sepet'=>$sepet,]);
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
}
