<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class targetController extends Controller
{
	# Variables #
	private $session; 

	# Constructor # 
	public function __construct()
	{
		$this->session = new Session();
	}

	public function grupos_targetAction()
	{
		$u = $this->getUser();
		if($u != null)
		{
	        $role=$u->getRole();
	        $grupo=$u->getGrupo();
	        if($role == "ROLE_SUPERUSER")
	        {
	        	$grupos = $this->obtener_grupo_nombre();
				return $this->render("@App/target/grupos_target.html.twig", array(
					"grupo"=>$grupos
				));
	        }
	        if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
	        	return $this->redirectToRoute("lista_target");
	    }
	}

	public function lista_targetAction()
	{
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == 'ROLE_SUPERUSER')
			$grupo=$_REQUEST['grupo'];
		else
			$grupo=$u->getGrupo();
		$informacion_grupos_descripcion = $this->informacion_grupos_descripcion($grupo);
		if(isset($_POST['solicitar']))
		{
			$ubicacion = $_POST['ubicacion'];
			$xml = simplexml_load_file("clients/$grupo/$ubicacion/info_squidguarddest.xml");
			return $this->render('@App/target/lista_target.html.twig', array(
				'grupo'=>$grupo,
				'ubicacion'=>$ubicacion,
				'xmls'=>$xmls= $xml->config
			));
		}
	    return $this->render('@App/plantillas/solicitud_grupo.html.twig', array(
			'informacion_grupos_descripcion'=>$informacion_grupos_descripcion
		));
	}

	public function editar_targetAction()
	{
		$plantel=$_POST['plantel'];
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == 'ROLE_SUPERUSER')
			$grupo=$_POST['grupo'];
		else
			$grupo=$u->getGrupo();
		$xml = simplexml_load_file("clients/$grupo/$plantel/info_squidguarddest.xml");
		if(isset($_POST['guardar']))
		{
			foreach($xml->config as $config)
			{
				if($config->name==$_POST['nombre'])
				{
					$config->domains = $_POST['lista_dominios'];
					$config->urls = $_POST['lista_url'];
					$config->expressions = $_POST['expression_regular'];
					$config->redirect_mode = $_POST['modo_redireccion'];
					$config->redirect = $_POST['redireccion'];
					$config->description = $_POST['descripcion'];
					$config->enablelog = $_POST['log'];
				}
			}
			$xml->asXML("clients/$grupo/$plantel/info_squidguarddest.xml");
			$contenido = "\t\t<squidguarddest>\n";
			foreach($xml->config as $config)
			{
			    $contenido .= "\t\t\t<config>\n";
			    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
			    $contenido .= "\t\t\t\t<domains>" . $config->domains . "</domains>\n";
			    $contenido .= "\t\t\t\t<urls>" . $config->urls . "</urls>\n";
			    $contenido .= "\t\t\t\t<expressions>" . $config->expressions . "</expressions>\n";
			    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
			    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
			    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
			    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
			    $contenido .= "\t\t\t</config>\n";
			}
		    $contenido .= "\t\t</squidguarddest>";
			$archivo = fopen("clients/$grupo/$plantel/info_squidguarddest.xml", 'w');
			fwrite($archivo, $contenido);
			fclose($archivo);
			# Archivo de cambios #
			$archivo_cambio = fopen("clients/$grupo/$plantel/change_squidguarddest.xml", 'w');
			fwrite($archivo_cambio, $contenido);
			fclose($archivo_cambio);
			return $this->redirectToRoute("grupos_target");
		}
		foreach($xml->config as $config)
		{
			if($config->name== $_POST['valor'] )
			{
				$nombre = $config->name;
				$lista_dominios = $config->domains;
				$lista_url = $config->urls;
				$expression_regular = $config->expressions;
				$modo_redireccion = $config->redirect_mode;
				$redireccion = $config->redirect;
				$descripcion = $config->description;
				$log = $config->enablelog;
				break;
			}
		}
		return $this->render("@App/target/editar_target.html.twig",array(
			"ubicacion"=>$plantel,
			"nombre"=>$nombre,
			"lista_dominios"=>$lista_dominios,
			"lista_url"=>$lista_url,
			"expression_regular"=>$expression_regular,
			"modo_redireccion"=>$modo_redireccion,
			"redireccion"=>$redireccion,
			"descripcion"=>$descripcion,
			"log"=>$log,
			"grupo"=>$grupo
		));
	}

	public function eliminar_targetAction()
	{
		$plantel=$_POST['plantel'];
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == 'ROLE_SUPERUSER')
			$grupo=$_POST['grupo'];
		else
			$grupo=$u->getGrupo();
		$libreria_dom = new \DOMDocument; 
	    $libreria_dom->load("clients/$grupo/$plantel/info_squidguarddest.xml");
	    $squidguarddest = $libreria_dom->documentElement;
	    $config = $squidguarddest->getElementsByTagName('config');
	    foreach ($config as $nodo) 
	    {
	    	$uri = $nodo->getElementsByTagName('name');
        	$valor = $uri->item(0)->nodeValue;
	        if($valor == $_POST['valor'])
	            $squidguarddest->removeChild($nodo);
	    }
	    $libreria_dom->save("clients/$grupo/$plantel/info_squidguarddest.xml");
	    $xml = simplexml_load_file("clients/$grupo/$plantel/info_squidguarddest.xml");
		$contenido = "\t\t<squidguarddest>\n";
		foreach($xml->config as $config)
		{
		    $contenido .= "\t\t\t<config>\n";
		    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
		    $contenido .= "\t\t\t\t<domains>" . $config->domains . "</domains>\n";
		    $contenido .= "\t\t\t\t<urls>" . $config->urls . "</urls>\n";
		    $contenido .= "\t\t\t\t<expressions>" . $config->expressions . "</expressions>\n";
		    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
		    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
		    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
		    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
		    $contenido .= "\t\t\t</config>\n";
		}
	    $contenido .= "\t\t</squidguarddest>";
		$archivo = fopen("clients/$grupo/$plantel/info_squidguarddest.xml", 'w');
		fwrite($archivo, $contenido);
		fclose($archivo);
		# Archivo de cambios #
		$archivo_cambio = fopen("clients/$grupo/$plantel/change_squidguarddest.xml", 'w');
		fwrite($archivo_cambio, $contenido);
		fclose($archivo_cambio);
		return $this->redirectToRoute("grupos_target");
	}	

	public function registro_targetAction(Request $request)
	{
		$ubicacion=$_REQUEST['id'];
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == 'ROLE_SUPERUSER')
			$grupo=$_REQUEST['id'];
		else
			$grupo=$u->getGrupo();
		$informacion_interfaces_plantel = $this->informacion_interfaces_plantel($ubicacion);
		if(isset($_POST['guardar']))
		{
			foreach($_POST['plantel'] as $plantel_grupo)
			{
				$xml = simplexml_load_file("clients/$grupo/$plantel_grupo/info_squidguarddest.xml");
				foreach($xml->config as $config)
				{
					if($config->name==$_POST['nombre'])
					{
						$estatus="The name that you try to register already exists in ".$plantel_grupo.".";
						$this->session->getFlashBag()->add("estatus",$estatus);
						return $this->redirectToRoute("grupos_target");
					}
				}
			}
			foreach($_POST['plantel'] as $plantel_grupo)
			{
				$xml = simplexml_load_file("clients/$grupo/$plantel_grupo/info_squidguarddest.xml");
				$product = $xml->addChild('config');
				$product->addChild('name', $_POST['nombre']);
				$product->addChild('domains', $_POST['lista_dominios']);
				$product->addChild('urls', $_POST['lista_url']);
				$product->addChild('expressions', $_POST['expression_regular']);
				$product->addChild('redirect_mode', $_POST['modo_redireccion']);
				$product->addChild('redirect', $_POST['redireccion']);
				$product->addChild('description', $_POST['descripcion']);
				$product->addChild('enablelog', $_POST['log']);
				file_put_contents("clients/$grupo/$plantel_grupo/info_squidguarddest.xml", $xml->asXML());
				$contenido = "\t\t<squidguarddest>\n";
				foreach($xml->config as $config)
				{
				    $contenido .= "\t\t\t<config>\n";
				    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
				    $contenido .= "\t\t\t\t<domains>" . $config->domains . "</domains>\n";
				    $contenido .= "\t\t\t\t<urls>" . $config->urls . "</urls>\n";
				    $contenido .= "\t\t\t\t<expressions>" . $config->expressions . "</expressions>\n";
				    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
				    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
				    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
				    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
				    $contenido .= "\t\t\t</config>\n";
				}
			    $contenido .= "\t\t</squidguarddest>";
				$archivo = fopen("clients/$grupo/$plantel_grupo/info_squidguarddest.xml", 'w');
				fwrite($archivo, $contenido);
				fclose($archivo);
				# Archivo de cambios #
				$archivo_cambio = fopen("clients/$grupo/$plantel_grupo/change_squidguarddest.xml", 'w');
				fwrite($archivo_cambio, $contenido);
				fclose($archivo_cambio);
			}
			return $this->redirectToRoute("grupos_target");
		}
		return $this->render('@App/target/registro_target.html.twig', array(
			'informacion_interfaces_plantel'=>$informacion_interfaces_plantel,
			'ubicacion'=>$ubicacion
		));
	}

	# Area de consultas #
	# Funcion utilizada en grupos_target #
	private function obtener_grupo_nombre()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT DISTINCT g.nombre FROM AppBundle:grupos g ORDER BY g.nombre ASC');
		$grupos = $query->getResult();
		return $grupos;
	}
	# Funcion utilizada en lista_target #
	private function informacion_grupos_descripcion($grupo)
	{
		$em = $this->getDoctrine()->getEntityManager();
	    $db = $em->getConnection();
		$query = "SELECT descripcion FROM grupos WHERE nombre = '$grupo'";
		$stmt = $db->prepare($query);
		$params =array();
		$stmt->execute($params);
		$grupos=$stmt->fetchAll();
		return $grupos;
	}
	# Funcion utilizada en registro_aliases #
	private function informacion_interfaces_plantel($grupo)
	{
		$em = $this->getDoctrine()->getEntityManager();
	    $db = $em->getConnection();
		$query = "SELECT DISTINCT descripcion FROM interfaces WHERE grupo = '$grupo'";
		$stmt = $db->prepare($query);
		$params =array();
		$stmt->execute($params);
		$grupos=$stmt->fetchAll();
		return $grupos;
	}
}