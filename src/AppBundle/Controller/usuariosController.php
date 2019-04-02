<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\usuarios;
use AppBundle\Form\usuariosType;
use Symfony\Component\HttpFoundation\Session\Session;

class usuariosController extends Controller
{
	# Variables #
	private $session; 

	# Constructor # 
	public function __construct()
	{
		$this->session = new Session();
	}

	# Funcion para iniciar sesion #
	public function loginAction()
	{
		$autenticacion = $this->get("security.authentication_utils");
		$error = $autenticacion->getLastAuthenticationError();
		$email_usuario = $autenticacion->getLastUsername();
		if ($error == true) 
		{ 
			$recuperar_email_usuario = $this->recuperar_email_usuario($email_usuario);
			if(!count($recuperar_email_usuario) == 0)
			{
				$_SESSION['contador_login'] = 0;
				$recuperar_estado_usuario = $this->recuperar_estado_usuario($email_usuario);
				if($recuperar_estado_usuario[0]['estado'] === false)
				{
					$estatus="Your user has been blocked. Communicate with the company to be unlocked.";
					$this->session->getFlashBag()->add("estatus",$estatus);
					return $this->render('@App/Default/index.html.twig', array('error'=> $error, 'last_username' => $email_usuario));
				}
				$_SESSION['contador_login'] = $_SESSION['contador_login'] + 1;
				$contador = $_SESSION['contador_login'] + $recuperar_estado_usuario[0]['intentos'];
				$actualizar_intentos_usuario = $this->actualizar_intentos_usuario($contador, $email_usuario);
				if($contador > 4)
				{
					$estatus="Your user is blocked. They have made several unsuccessful attempts to login. Communicate with the company to be unlocked.";
					$this->session->getFlashBag()->add("estatus",$estatus);
					$actualizar_estado_usuario = $this->actualizar_estado_usuario($email_usuario);
				}
			}	
		}
		return $this->render('@App/Default/index.html.twig', array(
			'error'=> $error, 
			'last_username' => $email_usuario
		));
	}

	# Funcion para validar el inicio de session #
	public function validacion_loginAction()
	{
		$usuario = $this->getUser();
		$email=$usuario->getEmail();
		$recuperar_estado_usuario = $this->recuperar_estado_usuario($email);
		if($recuperar_estado_usuario[0]['estado'] == false)
		{
			echo '<script> alert("Your user has been blocked. Communicate with the company to be unlocked.");window.location.href="logout";</script>';
		}
		else
		{
			$actualizar_intentos_usuario = $this->actualizar_intentos_usuario($contador = 0, $email);
			return $this->redirectToRoute("dashboard");	
		}
	}

	# Funcion para mostrar el dashboard #
	public function dashboardAction()
	{
		return $this->render('@App/usuarios/dashboard.html.twig');
	}

	# Funcion para mostrar los usuarios #
	public function lista_usuariosAction()
	{
		$u = $this->getUser();
		if($u != null)
		{
	        $role=$u->getRole();
	        if($role == "ROLE_SUPERUSER")
	        {
				$recuperar_todos_usuarios_superuser = $this->recuperar_todos_usuarios_superuser();
				return $this->render('@App/usuarios/lista_usuarios.html.twig', array(
					"usuarios"=>$recuperar_todos_usuarios_superuser
				));
	        }
	        if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
	        {
	        	$recuperar_todos_usuarios_administrador = $this->recuperar_todos_usuarios_administrador($u->getGrupo());
				return $this->render('@App/usuarios/lista_usuarios.html.twig', array(
					"usuarios"=>$recuperar_todos_usuarios_administrador
				));
	        }
	    }
	}

	# Funcion para agregar un nuevo usuario #
	public function registro_usuario_grupoAction(Request $request, $grupo)
	{
		$usuarios =  new usuarios();
		$form = $this->createForm(usuariosType::class,$usuarios);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getEntityManager();
			$recuperar_email_usuario = $this->recuperar_email_usuario($form->get("email")->getData());
			if(count($recuperar_email_usuario)==0)
			{
				$usuarios->setEstado(true);
				$usuarios->setIntentos(0);
				$usuarios->setGrupo($grupo);
				$factory = $this->get("security.encoder_factory");
				$encoder = $factory->getEncoder($usuarios);
				$password = $encoder->encodePassword($form->get("password")->getData(),$usuarios->getSalt());
				$usuarios->setPassword($password);
				$em->persist($usuarios);
				$flush=$em->flush();
				if($flush == null)
				{
					$estatus="Successfully registration.";
					$this->session->getFlashBag()->add("estatus",$estatus);
					return $this->redirectToRoute("lista_usuarios");
				}
				else
				{
					$estatus="Problems with the server try later.";
					$this->session->getFlashBag()->add("estatus",$estatus);
				}
			}
			else
				echo '<script>alert("The email you are trying to register already exists. Try again.");window.history.go(-1);</script>';
		}
		return $this->render("@App/usuarios/registro_usuarios.html.twig",
			array(
				"form"=>$form->createView()
		));
	}

	# Area de consultas #
	# Funcion utilizada en login #
	private function recuperar_email_usuario($email)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT u.email FROM AppBundle:usuarios u WHERE  u.email = :email')->setParameter('email', $email);
		$usuario_email = $query->getResult();
		return $usuario_email;
	}
	# Funcion utilizada en login #
	private function recuperar_estado_usuario($email)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT u.estado, u.intentos FROM AppBundle:usuarios u WHERE  u.email = :email')->setParameter('email', $email);
		$estado = $query->getResult();
		return $estado;
	}
	# Funcion utilizada en login, validacion_login #
	private function actualizar_intentos_usuario($contador, $email)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('UPDATE AppBundle:usuarios u SET u.intentos = :intentos WHERE  u.email = :email')->setParameter('intentos', $contador)->setParameter('email', $email);
		$actualizar_intentos = $query->getResult();
		return $actualizar_intentos;
	}
	# Funcion utilizada en login #
	private function actualizar_estado_usuario($email)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('UPDATE AppBundle:usuarios u SET u.estado = :estado WHERE  u.email = :email')->setParameter('estado', false)->setParameter('email', $email);
		$actualizar_estado = $query->getResult();
		return $actualizar_estado;
	}
	# Funcion utilizada en lista_usuarios #
	private function recuperar_todos_usuarios_superuser()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT u.id, u.nombre, u.apellidos, u.email, u.role, u.estado, u.grupo FROM PrincipalBundle:Usuarios u');
		$usuarios = $query->getResult();
		return $usuarios;
	}
	# Funcion utilizada en lista_usuarios #
	private function recuperar_todos_usuarios_administrador($grupo)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT u.id, u.nombre, u.apellidos, u.email, u.role, u.estado, u.grupo FROM PrincipalBundle:Usuarios u
			WHERE  u.role = :roleAdministrator AND u.grupo = :grupo Or u.role = :roleUser AND u.grupo = :grupo'
		)->setParameter('roleAdministrator', 'ROLE_ADMINISTRATOR')
		->setParameter('grupo', $grupo)->setParameter('roleUser', 'ROLE_USER')->setParameter('grupo', $grupo);
		$usuarios = $query->getResult();
		return $usuarios;
	}
}