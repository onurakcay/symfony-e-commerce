<?php

namespace App\Controller;

use App\Repository\Admin\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\Admin\CategoryRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,CategoryRepository $categoryRepository,SettingRepository $settingRepository): Response
    {
        $data = $settingRepository->findAll();
        $cats = $this ->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $sql = "SELECT  COUNT(shopcart.id) as sepetsayisi
              FROM shopcart, product
              WHERE shopcart.productid = product.id and userid= :userid ";
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('userid', $user->getid());
            $statement->execute();
            $sepet = $statement->fetchAll();


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
        }


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/index.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'cats' => $cats, 'sepet' => $sepet,'data'=>$data,'sliders'=>$sliders,'products'=>$products]);
        }
        else
        {
            return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'cats' => $cats]);

        }
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
