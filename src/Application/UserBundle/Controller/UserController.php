<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\UserBundle\Entity\User;
use Application\UserBundle\Entity\Contact;
use Application\UserBundle\Entity\Comment;
use Application\CityBundle\Entity\Country;
use Application\CityBundle\Entity\City;
use Application\UserBundle\Form\UserType;
use Application\UserBundle\Form\CommentType;
use Application\UserBundle\Form\ContactType;
use Application\UserBundle\Form\ForgotPassType;
use Application\UserBundle\Form\ForgotEmailType;
use Application\UserBundle\Form\RegisterType;
use Application\UserBundle\Form\LoginType;
use Symfony\Component\Form as SymfonyForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\DefaultView;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Application\ApiBundle\Util\Util;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Template()
     */
    public function indexAction()
    {
    $request = $this->getRequest();
    $page = $request->query->get('page');
    if ( !$page ) $page = 1;

        $em = $this->getDoctrine()->getEntityManager();



    $query = $em->createQueryBuilder();
    $query->add('select', 'u')
       ->add('from', 'ApplicationUserBundle:User u')
       ->add('where', "u.body != ''")
       ->add('orderBy', 'u.id DESC');

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->add('where', 'u.category_id = :category_id')->setParameter('category_id', $category_id);

    }


        $adapter = new DoctrineORMAdapter($query);

    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(10); // 10 by default
    $maxPerPage = $pagerfanta->getMaxPerPage();

    $pagerfanta->setCurrentPage($page); // 1 by default
    $entities = $pagerfanta->getCurrentPageResults();
    $routeGenerator = function($page, $category_id) {
      $url = '?page='.$page;
      if ( $category_id ) $url .= '&c=' . $category_id;
        return $url;
    };
    $view = new DefaultView();
    $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));


    $date = new \DateTime("-1 week");
    $query = "SELECT u.*, (SELECT COUNT(*) FROM User u2 WHERE u2.ref_id = u.id AND u2.date > '" . $date->format('Y-m-d H:i:s') . "') AS total FROM User u GROUP BY u.id HAVING total > 0 ORDER BY total DESC LIMIT 10";
    $db = $this->get('database_connection');
        $users_ref = $db->fetchAll($query);



        return array('category_id' => $category_id, 'entities' => $entities, 'pager' => $html, 'nav_user' => 1, 'users_ref' => $users_ref);

    }


    /**
     * Lists all User entities from city.
     *
     * @Route("/city/{id}", name="user_city")
     * @Template()
     */
    public function cityAction($id)
    {
    $request = $this->getRequest();
    $page = $request->query->get('page');
    if ( !$page ) $page = 1;

        $em = $this->getDoctrine()->getEntityManager();

    $city = $em->getRepository('ApplicationCityBundle:City')->find($id);

    if (!$city) {
      throw $this->createNotFoundException('Unable to find Post entity.');
    }



    $query = $em->createQuery("SELECT c.name FROM ApplicationCityBundle:Country c WHERE c.code = :code");
    $query->setParameters(array(
      'code' => $city->getCode()
    ));
    $country = current( $query->getResult() );



    $query = $em->createQueryBuilder();
    $query->add('select', 'u')
       ->add('from', 'ApplicationUserBundle:User u')
       ->andWhere('u.city_id = :city_id')->setParameter('city_id', $id)
       ->andWhere("u.body != ''")
       ->add('orderBy', 'u.id DESC');

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->andWhere('u.category_id = :category_id')->setParameter('category_id', $category_id);

    }


        $adapter = new DoctrineORMAdapter($query);

    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(10); // 10 by default
    $maxPerPage = $pagerfanta->getMaxPerPage();

    $pagerfanta->setCurrentPage($page); // 1 by default
    $entities = $pagerfanta->getCurrentPageResults();
    $routeGenerator = function($page, $category_id) {
      $url = '?page='.$page;
      if ( $category_id ) $url .= '&c=' . $category_id;
        return $url;
    };
    $view = new DefaultView();
    $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));

        return array('category_id' => $category_id, 'city' => $city, 'country' => $country, 'entities' => $entities, 'pager' => $html);

    }

    /**
     * Lists all freelancers.
     *
     * @Route("/freelance", name="user_freelance")
     * @Template("ApplicationUserBundle:User:index_freelance.html.twig")
     */
    public function freelanceAction()
    {
    $request = $this->getRequest();
    $page = $request->query->get('page');
    if ( !$page ) $page = 1;

        $em = $this->getDoctrine()->getEntityManager();


    $query = $em->createQueryBuilder();
    $query->add('select', 'u')
       ->add('from', 'ApplicationUserBundle:User u')
       ->add('where', 'u.freelance = 1')
       ->add('orderBy', 'u.votes DESC, u.date_login DESC');

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->andWhere('u.category_id = :category_id')->setParameter('category_id', $category_id);
    }

        $adapter = new DoctrineORMAdapter($query);

    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(10); // 10 by default
    $maxPerPage = $pagerfanta->getMaxPerPage();

    $pagerfanta->setCurrentPage($page); // 1 by default
    $entities = $pagerfanta->getCurrentPageResults();
    $routeGenerator = function($page, $category_id) {
        $url = '?page='.$page;
      if ( $category_id ) $url .= '&c=' . $category_id;
      return $url;
    };
    $view = new DefaultView();
    $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));



        return array('entities' => $entities, 'pager' => $html, 'nav_user' => 1);
    }


    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}/show", name="user_show2")
     * @Template()
     */
    public function show2Action($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
    return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getID(),
      'slug' => $entity->getSlug() )),301);
    }


    /**
     * User login
     *
     * @Route("/login", name="user_login")
     * @Template()
     */
    public function loginAction()
    {

        $entity  = new User();
        $form    = $this->createForm(new LoginType(), $entity);

        $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {

      $form->bindRequest($request);

      // existe usuario?
      $em = $this->getDoctrine()->getEntityManager();
      $post = $form->getData();
      $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => $post->getEmail()));

      $pass = $post->getPass();

      if ( !$user ) {
              $error_text = "El email no es valido";
              $form['email']->addError( new SymfonyForm\FormError( $error_text ));
      }else if ( $pass && $user->getPass() != md5( $pass ) ) {
              $error_text = "La contraseña no es correcta";
              $form['pass']->addError( new SymfonyForm\FormError( $error_text ));
      }

          if ($form->isValid()) {

        // guardar ultimo login
        $user->setDateLogin( new \DateTime("now") );

        // guardar ip
        $user->setIp();

        // guardar total logins
        $total_logins = (int)$user->getTotalLogins() + 1;
        $user->setTotalLogins( $total_logins );

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();

        // autologin?
        $session = $this->getRequest()->getSession();
        $session->set('id', $user->getId());
        $session->set('name', $user->getShortName());
        $session->set('slug', $user->getSlug());
        $session->set('admin', $user->getAdmin());
        $session->set('moderator', $user->getModerator());

        // cookie
        $pass = $user->getPass();
        if ( !$pass ) $pass = md5( $user->getDate()->format('Y-m-d H:i:s') );
        setcookie("login", $user->getId() . ':' . $pass, ( time() + 3600 * 24 * 31 ), "/");


        $back = $session->get('back');
        if ( $back ) {
          $url = $back;
          $session->set('back','');
        }else{
          $url = $this->generateUrl('user_show', array('id' => $user->getId(), 'slug' => $user->getSlug()));
        }

              return $this->redirect($url);
          }
    }else{
		$back = $request->query->get('back');
	    if ( $back ) {
	      $session = $this->getRequest()->getSession();
	      $session->set('back', $back);
	    }

	}

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
  }

    /**
     * Creates a new User entity.
     *
     * @Route("/register", name="user_register")
     * @Template("ApplicationUserBundle:User:register.html.twig")
     */
    public function registerAction()
    {

        $entity  = new User();
        $form    = $this->createForm(new RegisterType(), $entity);


        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {

          $form->bindRequest($request);

          // existe usuario?
          $em = $this->getDoctrine()->getEntityManager();
          $post = $form->getData();
          $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => $post->getEmail()));

          if ( $user ) {
                  $error_text = "El email ya esta registrado";
                  $form->addError( new SymfonyForm\FormError( $error_text ));
          }else if ( strlen( $post->getPass() ) < 6 ) {
                  $error_text = "El password tiene que tener como minimo 6 caracteres";
                  $form->addError( new SymfonyForm\FormError( $error_text ));
          }

          if ($form->isValid()) {

        // usuario referido existe?
        $session = $this->getRequest()->getSession();
        $ref_id = $session->get('ref_id');
        if ( $ref_id ) {
          $user_ref = $em->getRepository('ApplicationUserBundle:User')->find($ref_id);
          if ( !$user_ref ) $ref_id = null;
        }
        if ( !$ref_id ) $ref_id = null;

        $entity->setRefId($ref_id);
        $entity->setDate( new \DateTime("now") );
        $entity->setDateLogin( new \DateTime("now") );
        $entity->setPass( md5( $post->getPass() ) );
        $entity->setTotalLogins( 1 );
        $entity->setCanContact( 1 );
        $entity->setIp();
        $entity->setSlug(Util::slugify($entity->getName()));

        // bug corregir location
        $this->fixLocation(&$post, &$entity, &$em);

        $em->persist($entity);
        $em->flush();

        // autologin?
        $session->set('id', $entity->getId());
        $session->set('name', $entity->getName());
        $session->set('slug', $entity->getSlug());
        $session->set('admin', $entity->getAdmin());
        $session->set('moderator', $entity->getModerator());

        // cookie
        $pass = $entity->getPass();
        if ( !$pass ) $pass = md5( $entity->getDate()->format('Y-m-d H:i:s') );
        setcookie("login", $entity->getId() . ':' . $pass, ( time() + 3600 * 24 * 31 ), "/");

              return $this->redirect( $this->generateUrl('user_edit', array('id' => $entity->getId())) . '#register' );
          }
    }else{
		  $back = $request->query->get('back');
	    if ( $back ) {
	      $session = $this->getRequest()->getSession();
	      $session->set('back', $back);
	    }
	
	}

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/edit", name="user_edit")
     * @Template()
     */
    public function editAction()
    {


    // esta logueado?
    $session = $this->getRequest()->getSession();
    $id = $session->get('id');
    if ( !$id ) {
      return $this->redirect('/');
    }



        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        $location = $entity->getLocation();


    $token = $entity->getPass();
    if ( !$token ) $token = md5( $entity->getDate()->format('Y-m-d H:i:s') );

        $editForm = $this->createForm(new UserType(), $entity);


    $old_email = $entity->getEmail();



    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
          $editForm->bindRequest($request);

      // el mail esta registrado?
      $post = $editForm->getData();
      $post_email = $post->getEmail();



      if ( $old_email != $post_email ) {
        $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => $post_email));

        if ( $user ) {
                $error_text = "El email ya esta registrado";
                $editForm['email']->addError( new SymfonyForm\FormError( $error_text ));
        }


      }


          if ($editForm->isValid()) {

        if ( $entity->getAvatarType() == AVATAR_TWITTER && !$entity->getTwitterURL() ) {
          $entity->setAvatarType( AVATAR_GRAVATAR );
        }

        // gravatar fix
        $entity->setEmail( strtolower( trim( $entity->getEmail() ) ) );

        // appannie fix
        $entity->setItunesUrl( str_replace(' ','-', strtolower( trim( $entity->getItunesUrl() ) ) ) );
        $entity->setAndroidUrl( str_replace(' ','+', trim( $entity->getAndroidUrl() ) ) );
        $entity->setTwitterUrl( str_replace(array('http://','twitter.com/','@','www.'),'', trim( $entity->getTwitterUrl() ) ) );

        $entity->setSlug(Util::slugify($entity->getName()));

        // bug corregir location
        if( $post->getLocation() != $location ){

          $this->fixLocation(&$post, &$entity, &$em);
        }
        
        // corregir descripcion
        $entity->setBody( strip_tags( $entity->getBody() ) );

        $em->persist($entity);
        $em->flush();

        $session->set('name', $entity->getShortName());
        $session->set('slug', $entity->getSlug());



        return $this->redirect($this->generateUrl('user_edit') . '?updated');
          }
    }


    // usuarios invitados
    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.ref_id = " . $id;
    $db = $this->get('database_connection');
    $result = $db->query($query)->fetch();
    $total = $result['total'];


    $avatars[AVATAR_GRAVATAR] = 'Gravatar';
    if ( $entity->getTwitterUrl() ) $avatars[AVATAR_TWITTER] = "Twitter";
    if ( $entity->getFacebookId() ) $avatars[AVATAR_FACEBOOK] = "Facebook";




        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
      'total'     => $total,
      'avatars'     => $avatars,
      'updated'     => isset($_GET['updated']),
      'token'     => $token
        );
    }

    /**
     * Search User entities.
     *
     * @Route("/search", name="user_search")
     * @Template()
     */
    public function searchAction()
    {
      $request = $this->getRequest();
      $search = strip_tags( $request->query->get('q') );

      $em = $this->getDoctrine()->getEntityManager();

      $qb = $em->createQueryBuilder()
         ->add('select', 'u')
         ->add('from', 'ApplicationUserBundle:User u')
         ->add('orderBy', 'u.id DESC');

      if( strpos( $search, '@' ) > 1 ){
        $qb->add('where', "u.email = :search")->setParameter('search', $search);

      }else if( $search[0] == '@' ){
        $qb->add('where', "u.twitter_url = :search")->setParameter('search', str_replace( '@', '', $search ) );

      }else{
        $qb->add('where', "u.name like '%".$search."%' or u.body like '%".$search."%'");
      }

      $query = $qb->getQuery();
      $entities = $query->getResult();

      if( count( $entities ) == 1 ){
        $user = $entities[0];
        $url = $this->generateUrl('user_show', array('id' => $user->getId(), 'slug' => $user->getSlug()));
        return $this->redirect( $url );
      }

      return array('entities' => $entities, 'search' => $search);
    }


    /**
     * Contact form
     *
     * @Route("/{id}/contact", name="user_contact")
     * @Template()
     */
    public function contactAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
    $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

    $form = $this->createForm(new ContactType());
    $result = 'no';

    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
          $form->bindRequest($request);




          if ($form->isValid()) {


        $values = $form->getData();
        

        $toEmail = $entity->getEmail();

        extract( $values );

        if ( filter_var($email, FILTER_VALIDATE_EMAIL) && !strstr( $body, '<a href=' ) ) {

          $header = 'From: ' . $email . " \r\n";
          $header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
          $header .= "Mime-Version: 1.0 \r\n";
          $header .= "Content-Type: text/html; charset=UTF-8";

          $mensaje = 'Enviado por ';

          // get perfil usuario
          $user_id = $this->getRequest()->getSession()->get('id');
          if( $user_id ){
          	$user = $em->getRepository('ApplicationUserBundle:User')->find( $user_id );
          	$url = $this->generateUrl('user_show', array('id' => $user->getId(), 'slug' => $user->getSlug()), true);
          	$mensaje .= '<a href="' . $url . '">' . $name . '</a>';
          }else{
          	$mensaje .= $name;
          }
          
          
          $mensaje .= ' (' . $email . ')<br/><br/>';
          $mensaje .= $body;
          
          //$mensaje .= "Enviado el " . date('d/m/Y', time());




          $result = @mail($toEmail, $subject, $mensaje, $header);

          // backup
          @mail("gafeman@gmail.com", $subject, $mensaje, $header);

        }else{
          return new Response("SPAM!");
        }

          }
      }



        return array(
      'form' => $form->createView(),
            'entity'      => $entity,
      'result'      => $result
      );


    }



    /**
     * Facebook connect login
     *
     * @Route("/fblogin", name="user_fblogin")
     * @Template()
     */
    public function fbloginAction()
    {
	
	$request = $this->getRequest();
	$back = $request->query->get('back');
    if ( $back ) {
      $session = $this->getRequest()->getSession();
      $session->set('back', $back);
    }

    require __DIR__ . '/../../../../vendor/facebook/examples/example.php';

    // login ok ?
    if ( isset( $user_profile['id'] ) ) {

      $session = $request->getSession();

      // existe usuario en la bd?
      $em = $this->getDoctrine()->getEntityManager();
      $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('facebook_id' => $user_profile['id']));

      if ( !$user ) {
        $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => $user_profile['email']));
      }

      if ( !$user ) {

        if ( !isset( $user_profile['location']['name'] ) ) $user_profile['location']['name'] = '';
        if ( !isset( $user_profile['website'] ) ) $user_profile['website'] = '';

        // usuario referido existe?
        $ref_id = $session->get('ref_id');
        if ( $ref_id ) {
          $user_ref = $em->getRepository('ApplicationUserBundle:User')->find($ref_id);
          if ( !$user_ref ) $ref_id = null;
        }
        if ( !$ref_id ) $ref_id = null;

        $user = new \Application\UserBundle\Entity\User;
        $user->setFacebookId($user_profile['id']);
        $user->setCategoryId(13);
        $user->setEmail($user_profile['email']);
        $user->setName($user_profile['name']);
        $user->setLocation($user_profile['location']['name']);
        $user->setDate( new \DateTime("now") );
        $user->setUrl( $user_profile['website'] );
        $user->setRefId($ref_id);
        $user->setCanContact(1);
        $user->setAvatarType(AVATAR_FACEBOOK);
        $user->setSlug(Util::slugify($user->getName()));

        $url = $this->generateUrl('user_edit') . '#fblogin';

      }else{


 

        // cookie
        $pass = $user->getPass();
        if ( !$pass ) $pass = md5( $user->getDate()->format('Y-m-d H:i:s') );
        setcookie("login", $user->getId() . ':' . $pass, ( time() + 3600 * 24 * 31 ), "/");







        $back = $session->get('back');
        if ( $back ) {
          $url = $back;
          $session->set('back','');
        }else{
          $url = $this->generateUrl('user_show', array('id' => $user->getId(), 'slug' => $user->getSlug()));
        }


      }


      // guardar ultimo login
      $user->setDateLogin( new \DateTime("now") );

      // guardar facebook id
      if ( isset( $user_profile['id'] ) &&  !$user->getFacebookId() ) {
        $user->setFacebookId( $user_profile['id'] );
      }

      // guardar ip
      $user->setIp();

      // guardar total logins
      $total_logins = (int)$user->getTotalLogins() + 1;
      $user->setTotalLogins( $total_logins );


      $em = $this->get('doctrine.orm.entity_manager');
      $em->persist($user);
      $em->flush();



      $session->set('id', $user->getId());
      $session->set('name', $user->getShortName());
      $session->set('slug', $user->getSlug());
      $session->set('admin', $user->getAdmin());
      $session->set('moderator', $user->getModerator());


    }else{
      $url = $loginUrl;
    }

    return $this->redirect($url);
    }


    /**
     * Facebook connect logout
     *
     * @Route("/logout", name="user_logout")
     * @Template()
     */
    public function logoutAction()
    {
    $session = $this->getRequest()->getSession();
    $session->set('id',null);
    //$session->set('facebook_id',null);
    $session->set('name',null);
    $session->set('slug', null);
    $session->set('admin',null);
    $session->set('moderator', null);


    setcookie("login", false, ( time() - 3600 ), "/");

    return $this->redirect( $this->generateUrl('post') );
  }


    /**
     * Invite contacts
     *
     * @Route("/invite", name="user_invite")
     * @Template()
     */
    public function inviteAction()
    {
    // esta logueado?
    $session = $this->getRequest()->getSession();
    $id = $session->get('id');
    if ( !$id ) {
      return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
    }

        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);





    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.ref_id = " . $id;
    $db = $this->get('database_connection');
    $result = $db->query($query)->fetch();
    $total = $result['total'];




        return array('entity' => $entity, 'total' => $total);
    }

    /**
     * Welcome invite
     *
     * @Route("/welcome", name="user_welcome")
     * @Template()
     */
    public function welcomeAction()
    {
	
	
      $request = $this->getRequest();
      $em = $this->getDoctrine()->getEntityManager();

		$back = $request->query->get('back');

      // esta logueado?
      $session = $request->getSession();
      $session_id = $session->get('id');
      if ( $session_id ) {
		    return $this->redirect( $this->generateUrl('post') );
      }




	
	

	
	
    
      $ref_id = $request->query->get('ref_id');
      
      if ( $ref_id ) {

            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ApplicationUserBundle:User')->find($ref_id);

        if ( $entity ) {
          
          $session->set('ref_id', $ref_id);
        }
      }
      if ( $back ) {
        
        $session->set('back', $back);
      }



        return array();
    }


    /**
     * User scrapper
     *
     * @Route("/scrapper", name="user_scrapper")
     * @Template()
     */
    public function scrapperAction()
    {
    require __DIR__ . '/../../../../vendor/scrapper/get.php';
    die($result);
  }


    /**
     * Recomend form
     *
     * @Route("/{id}/recommend", name="user_recommend")
     * @Template()
     */
    public function recommendAction($id)
    {
	    // esta logueado?
	    $session = $this->getRequest()->getSession();
	    $session_id = $session->get('id');
	    if ( !$session_id ) {
	      return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
	    }

	    // existe usuario?
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('ApplicationUserBundle:User')->find($id);
		if (!$user) {
			throw $this->createNotFoundException('Unable to find User entity.');
		}

	    // me quiero votar a mi mismo?
	    //if ( $session_id == $id ) {
	    //  return $this->redirect($this->generateUrl('user_show', array('id' => $id, 'slug' => $user->getSlug())));
	    //}



	    // setear comentario si ya he escrito anteriormente
	    $query = $em->createQuery('SELECT c FROM ApplicationUserBundle:Comment c WHERE c.from_id = :from_id AND c.to_id = :to_id');
	    $query->setMaxResults(1);
	    $query->setParameters(array(
	        'from_id' => $session_id,
	        'to_id' => $id
	    ));
	    $entity = current( $query->getResult() );

	    if ( !$entity ) {
	      $entity = new Comment();
		  $new = true;
	    }

	    $form = $this->createForm(new CommentType(), $entity);

	    $request = $this->getRequest();
	    if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			if ($form->isValid()) {
		        $entity->setFromId( $session_id );
		        $entity->setToId( $user->getId() );
		        $entity->setDate( new \DateTime("now") );
		        $em->persist($entity);
		        //$em->flush();

		        // guardar total recomendaciones
		        $query = $em->createQuery("SELECT COUNT(c) as total FROM ApplicationUserBundle:Comment c WHERE c.to_id = :id");
		        $query->setParameter('id', $id);
		        $votes = current($query->getResult());

		        $user->setVotes( $votes['total'] );
		        $em->persist($user);
		        $em->flush();

				$url = $this->generateUrl('user_comment', array('user_id' => $id, 'comment_id' => $entity->getId() ),true);
				if( isset( $new ) ){
		        	// enviar email
					$user_from = $em->getRepository('ApplicationUserBundle:User')->find($session_id);
			        $toEmail = $user->getEmail();
			        $email = $user_from->getEmail();

			        $header = 'From: ' . $email . " \r\n";
			        $header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
			        $header .= "Mime-Version: 1.0 \r\n";
			        $header .= "Content-Type: text/html; charset=utf-8";


			        $mensaje = 'Enviado por ';

			        // get perfil usuario
		          	$url = $this->generateUrl('user_show', array('id' => $user_from->getId(), 'slug' => $user_from->getSlug()), true);
		          	$mensaje .= '<a href="' . $url . '">' . $name . '</a>';
		          	$mensaje .= ' (' . $email . ')<br/><br/>';


			        $mensaje .= "Haz clic en el siguiente enlace para ver la recomendación<br/>";
			        $mensaje .= '<a href="' . $url . '" target="_blank">' . $url . '</a>';

			        $subject = sprintf( "%s te ha recomendado en betabeers", $user_from->getName() );
			        $result = @mail($toEmail, $subject, $mensaje, $header);
		
			        // backup
			        @mail("gafeman@gmail.com", $subject, $mensaje, $header);
				}
	
				// redirigir al comentario
		        return $this->redirect($url);
	          }
	      }

	    return array(
	      'form' => $form->createView(),
	      'user' => $user
	      );

    }


    /**
     * User all recommendations
     *
     * @Route("/comments", name="user_comments_all")
     * @Template("ApplicationUserBundle:User:comments_all.html.twig")
     */
    public function commentsallAction()
    {
    $request = $this->getRequest();
    $page = $request->query->get('page');
    if ( !$page ) $page = 1;

        $em = $this->getDoctrine()->getEntityManager();


    $category_id = $request->query->get('c');
    if ( $category_id ) {
      $query = $em->createQuery("SELECT c.id as comment_id, c.body, c.date, u.id as user_id, u.name, u.slug, u.category_id, u.avatar_type, u.twitter_url, u.facebook_id, u.email FROM ApplicationUserBundle:User u, ApplicationUserBundle:Comment c WHERE u.id = c.to_id AND u.category_id = :category_id ORDER BY c.id DESC")
            ->setParameter('category_id', $category_id);
    }else{
      $query = $em->createQuery("SELECT c.id as comment_id, c.body, c.date, u.id as user_id, u.name, u.slug, u.category_id, u.avatar_type, u.twitter_url, u.facebook_id, u.email FROM ApplicationUserBundle:User u, ApplicationUserBundle:Comment c WHERE u.id = c.to_id ORDER BY c.id DESC");
    }



        $adapter = new DoctrineORMAdapter($query);

    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(10); // 10 by default
    $maxPerPage = $pagerfanta->getMaxPerPage();

    $pagerfanta->setCurrentPage($page); // 1 by default
    $entities = $pagerfanta->getCurrentPageResults();
    $routeGenerator = function($page) {
      $url = '?page='.$page;
        return $url;
    };
    $view = new DefaultView();
    $html = $view->render($pagerfanta, $routeGenerator);




        return array('entities' => $entities, 'pager' => $html, 'nav_user' => 1);
  }

    /**
     * User recommendations
     *
     * @Route("/{id}/comments", name="user_comments")
     * @Template()
     */
    public function commentsAction($id)
    {
    // existe usuario?
        $em = $this->getDoctrine()->getEntityManager();
    $user = $em->getRepository('ApplicationUserBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

    $query = $em->createQuery("SELECT u.name, u.category_id, u.slug, c.id, c.from_id, c.body, c.type, c.date FROM ApplicationUserBundle:User u, ApplicationUserBundle:Comment c WHERE u.id = c.from_id AND c.to_id = :id ORDER BY c.id DESC");
    $query->setParameter('id', $id);
    $comments = $query->getResult();

    $total = count($comments);

    return array(
      'user' => $user,
      'comments' => $comments,
      'total' => $total
      );
  }


    /**
     * User recommendation
     *
     * @Route("/{user_id}/comments/{comment_id}", name="user_comment")
     * @Template("ApplicationUserBundle:User:comment.html.twig")
     */
    public function commentAction($user_id, $comment_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

    	// existe usuario?
    	$user = $em->getRepository('ApplicationUserBundle:User')->find($user_id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

	    // existe comentario?
	    $query = $em->createQuery("SELECT u.name, u.category_id, u.slug, c.id, c.from_id, c.body, c.type, c.date FROM ApplicationUserBundle:User u, ApplicationUserBundle:Comment c WHERE u.id = c.from_id AND c.to_id = :to_id AND c.id = :id ORDER BY c.id DESC");
	    $query->setParameters(array(
	      'id' => $comment_id,
	      'to_id' => $user_id
	    ));
	    $comments = $query->getResult();
        if (!$comments) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

	    $query = "SELECT COUNT(c.id) AS total FROM Comment c WHERE c.to_id = " . $user_id;
	    $db = $this->get('database_connection');
	    $result = $db->query($query)->fetch();
	    $total = $result['total'];

	    return array(
		  'comment_id' => $comment_id,
	      'user' => $user,
	      'comments' => $comments,
	      'total' => $total
	      );
	  }



    /**
     * Forgot pass form
     *
     * @Route("/forgotpass/{token}/{id}", name="user_forgotpass")
     * @Template()
     */
    public function forgotpassAction($token,$id)
    {
        $em = $this->getDoctrine()->getEntityManager();
    $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);

    // existe usuario?
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

    // token coincide? si no tiene pass asignado es la fecha
    $user_token = $entity->getPass();
    if ( !$user_token ) $user_token = md5( $entity->getDate()->format('Y-m-d H:i:s') );

    if ( $user_token != $token ) {
            throw $this->createNotFoundException('Unable to find User entity.');
    }

    // constuir formulario
    $form = $this->createForm(new ForgotPassType());

    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
          $form->bindRequest($request);


      $post = $form->getData();

      if ( strlen( $post->getPass() ) < 6 ) {
              $error_text = "El password tiene que tener como minimo 6 caracteres";
              $form->addError( new SymfonyForm\FormError( $error_text ));
      }

          if ($form->isValid()) {

        // cambiar contraseña
        $entity->setPass( md5( $post->getPass() ) );
              $em->persist($entity);
              $em->flush();

        // autologin
        $session = $this->getRequest()->getSession();
        if ( !$session->get('id') ) {
          $session->set('id', $entity->getId());
          $session->set('name', $entity->getShortName());
          $session->set('slug', $entity->getSlug());
          $session->set('admin', $entity->getAdmin());
        }

        // redirigir perfil
        $url = $this->generateUrl('user_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
        return $this->redirect($url);
          }
      }

        return array(
      'form'      => $form->createView(),
            'entity'      => $entity,
      'token'     => $token
      );


    }


    /**
     * Forgot email form
     *
     * @Route("/forgotemail", name="user_forgotemail")
     * @Template()
     */
    public function forgotemailAction()
    {
    $result = 0;

    // constuir formulario
    $form = $this->createForm(new ForgotEmailType());

    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
          $form->bindRequest($request);
      $post = $form->getData();

      // existe usuario?
          $em = $this->getDoctrine()->getEntityManager();
      $entity = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => $post->getEmail()));

          if (!$entity) {
              $error_text = "El email no esta registrado";
              $form['email']->addError( new SymfonyForm\FormError( $error_text ));
          }


          if ($form->isValid()) {

        // enviar enlace por email
        $toEmail = $entity->getEmail();
        $email = 'noreply@betabeers.com';

        $header = 'From: ' . $email . " \r\n";
        $header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
        $header .= "Mime-Version: 1.0 \r\n";
        $header .= "Content-Type: text/html; charset=utf-8";

        $token = $entity->getPass();
        if ( !$token ) $token = md5( $entity->getDate()->format('Y-m-d H:i:s') );

        $url = $this->generateUrl('user_forgotpass', array('token' => $token, 'id' => $entity->getId()),true);
        $mensaje = "Haz clic en el siguiente enlace para cambiar tu contraseña<br/>" . " \r\n";
        $mensaje .= '<a href="' . $url . '" target="_blank">' . $url . '</a>';

        $subject = "Cambiar contraseña";

        $result = @mail($toEmail, $subject, $mensaje, $header);



          }
      }

        return array(
      'form'      => $form->createView(),
            'result'    => $result
      );


    }


    /**
     * Admin User entities.
     *
     * @Route("/admin", name="user_admin")
     * @Template()
     */
    public function adminAction()
    {

    $session = $this->getRequest()->getSession();
    if ( !$session->get('admin') ) {
      return $this->redirect('/');
    }


    $request = $this->getRequest();
    $page = $request->query->get('page');
    if ( !$page ) $page = 1;

        $em = $this->getDoctrine()->getEntityManager();




    $query = $em->createQueryBuilder();
    $query->add('select', 'u')
       ->add('from', 'ApplicationUserBundle:User u')
       ->add('orderBy', 'u.id DESC');

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->add('where', 'u.category_id = :category_id')->setParameter('category_id', $category_id);
    }


        $adapter = new DoctrineORMAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(20); // 10 by default
    $maxPerPage = $pagerfanta->getMaxPerPage();

    $pagerfanta->setCurrentPage($page); // 1 by default
    $entities = $pagerfanta->getCurrentPageResults();
    $routeGenerator = function($page,$category_id) {
      $url = '?page='.$page;
      if ( $category_id ) $url .= '&c=' . $category_id;
        return $url;
    };
    $view = new DefaultView();
    $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));

    // estadisticas de anuncios
    $query = "SELECT COUNT(u.id) AS total, u.category_id FROM User u GROUP BY u.category_id ORDER BY total DESC";
    $db = $this->get('database_connection');
        $categories = $db->fetchAll($query);


        return array('categories_aux' => $categories, 'pager' => $html, 'entities' => $entities);
    }




    /**
     * Ajax get location
     *
     * @Route("/getlocation", name="getlocation")
     * @Template()
     */
    public function getlocationAction()
    {
    $request = $this->getRequest();
    $city = $request->query->get('city');
    $callback = $request->query->get('callback');

        $em = $this->getDoctrine()->getEntityManager();

    $query = $em->createQuery("SELECT c1.id AS cit_id, c2.id AS cou_id, c1.name AS city, c2.name AS country FROM ApplicationCityBundle:City c1, ApplicationCityBundle:Country c2 WHERE c1.code = c2.code AND c1.name LIKE '" . $city . "%' ORDER BY c1.name ASC, c1.population DESC");
  
    $query->setMaxResults(5);
    $cities = $query->getResult();

    return array('callback' => $callback, 'result' => json_encode(array('geonames' => $cities)));
  }


    /**
     * Set slug users
     *
     * @Route("/slugs", name="user_slugs")
     */
    public function slugs()
    {
    $em = $this->getDoctrine()->getEntityManager();
    $qb = $em->createQueryBuilder()
       ->add('select', 'u')
       ->add('from', 'ApplicationUserBundle:User u')
       ->add('where', "u.slug = ''")
       ->add('orderBy', 'u.id ASC');

    $entities = $qb->getQuery()->getResult();
    $total = count( $entities );

    for ( $i = 0; $i < $total; $i++ ) {
      $entities[$i]->setSlug(Util::slugify($entities[$i]->getName()));
      $em->persist($entities[$i]);
      $em->flush();
    }
    die();
  }


    /**
     * Finds and displays a User entity.
     *
     * @Route("/{slug}-{id}/", requirements={"slug" = "[a-z0-9\-]+", "id" = "^\d+$"}, name="user_show")
     * @Template()
     */
    public function showAction($slug, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }



    // es diferencia usuario, visitas + 1
    $session = $this->getRequest()->getSession();
    $session_id = $session->get('id');

    if ( $session_id != $entity->getId() ) {
      $entity->setVisits($entity->getVisits() + 1 );

      if( isset( $_SERVER['HTTP_REFERER'] ) ){
        if( stristr($_SERVER['HTTP_REFERER'],'google') ){
          $entity->setVisitsGoogle($entity->getVisitsGoogle() + 1 );
        }

        if( stristr($_SERVER['HTTP_REFERER'],'/user/search') ){
          $entity->setVisitsFinder($entity->getVisitsFinder() + 1 );
        }
      }

      $em->persist($entity);
      $em->flush();
    }

    $contact_form_html = false;
    if ( $entity->getCanContact() ) {
      $contact = new \Application\UserBundle\Entity\Contact;
      if ( $session_id && $session_id != $id ) {
        $user_login = $em->getRepository('ApplicationUserBundle:User')->find($session_id);
        $contact->setName( $user_login->getName() );
        $contact->setEmail( $user_login->getEmail() );
      }
      $contact->setSubject( "Contacto desde betabeers" );
      $contact_form = $this->createForm(new ContactType(), $contact);
      $contact_form_html = $contact_form->createView();
    }

    // contadores
    $query = $em->createQuery("SELECT COUNT(c) as total FROM ApplicationUserBundle:Comment c WHERE c.to_id = :id AND c.type = 0");
    $query->setParameter('id', $id);
    $total = current($query->getResult());
    $total_like = $total['total'];

    $query = $em->createQuery("SELECT COUNT(c) as total FROM ApplicationUserBundle:Comment c WHERE c.to_id = :id AND c.type = 1");
    $query->setParameter('id', $id);
    $total = current($query->getResult());
    $total_wannawork = $total['total'];

    $query = $em->createQuery("SELECT COUNT(c) as total FROM ApplicationUserBundle:Comment c WHERE c.to_id = :id AND c.type = 2");
    $query->setParameter('id', $id);
    $total = current($query->getResult());
    $total_work = $total['total'];

    // usuarios registrados
    $db = $this->get('database_connection');
    $query = "SELECT COUNT(u.id) AS total FROM User u";
    $result = $db->query($query)->fetch();
    $total_users = $result['total'];


    $query = $em->createQuery("SELECT u FROM ApplicationUserBundle:User u WHERE u.category_id = :category_id AND u.id != :id AND u.body IS NOT NULL ORDER BY u.date_login DESC")
    	->setParameter('category_id', $entity->getCategoryId())
    	->setParameter('id', $id)
    	->setMaxResults(6);
    $related_users = $query->getResult();


	
	if( $entity->getSearchTeam() ){
	    $query = $em->createQuery("SELECT u FROM ApplicationUserBundle:User u WHERE u.category_id != :category_id AND u.category_id != 13 AND u.search_team = 1 AND u.id != :id AND u.body IS NOT NULL ORDER BY u.date_login DESC")
	    ->setParameter('category_id', $entity->getCategoryId())
	    ->setParameter('id', $id)
	    ->setMaxResults(6);
	    $team_users = $query->getResult();
	}else{
		$team_users = array();
	}


    $badges = $em->createQuery("SELECT t FROM ApplicationTestBundle:Test t, ApplicationTestBundle:TestUser tu WHERE t.id = tu.test_id AND tu.user_id = :id ORDER BY tu.date ASC")
        ->setParameter('id', $id)
        ->setMaxResults(6)
        ->getResult();


    return array(
      'entity'       => $entity,
      'contact_form' => $contact_form_html,
      'total_users' => $total_users,
      'related_users' => $related_users,
	  'team_users' => $team_users,
      'badges' => $badges
      );
    }


    function fixLocation( $post, $entity, $em ){        
        $location = $post->getLocation();
        if( $location ){
          $query = $em->createQuery("SELECT c1.id AS cit_id, c2.id AS cou_id, c1.name AS city, c2.name AS country FROM ApplicationCityBundle:City c1, ApplicationCityBundle:Country c2 WHERE c1.code = c2.code AND c1.name = :city ORDER BY c1.name ASC, c1.population DESC");
          $city = current( $query->setParameter('city', $location)->setMaxResults(1)->getResult() );
          if( $city ){
            $entity->setCityId( $city['cit_id'] );
            $entity->setCountryId( $city['cou_id'] );
            $entity->setLocation( $city['city'] . ', ' . $city['country'] );
          }
        }
    }

}
