<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class aclController extends Controller
{
	public function grupos_aclAction()
	{
		$u = $this->getUser();
		if($u != null)
		{
	        $role=$u->getRole();
	        $grupo=$u->getGrupo();
	        if($role == "ROLE_SUPERUSER")
	        {
	        	$grupos = $this->obtener_grupo_nombre();
				return $this->render("@App/acl/grupos_acl.html.twig", array(
					"grupo"=>$grupos
				));
	        }
	        if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
	        	return $this->redirectToRoute("lista_acl");
	    }
	}

	public function lista_aclAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$db = $em->getConnection();
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
			$xml = simplexml_load_file("clients/Ejemplo_2/$ubicacion/info_squidguardacl.xml");
			return $this->render('@App/acl/lista_acl.html.twig', array(
				'ubicacion'=>$ubicacion,
				'xmls'=>$xmls= $xml->config
			));
		}
	    return $this->render('@App/plantillas/solicitud_grupo.html.twig', array(
			'informacion_grupos_descripcion'=>$informacion_grupos_descripcion
		));
	}

	public function editar_aclAction()
	{
		$plantel=$_POST['plantel'];
		$xml = simplexml_load_file("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
		$xml2 = simplexml_load_file("clients/Ejemplo_2/$plantel/info_squidguarddest.xml");
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
			$xml->asXML("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
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
			$archivo = fopen("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", 'w');
			fwrite($archivo, $contenido);
			fclose($archivo);
			return $this->redirectToRoute("grupos_target");
		}
		foreach($xml->config as $config)
		{
			if($config->name== $_POST['valor'] )
			{
				$estado = $config->disabled;
				$nombre = $config->name;
				$cliente = $config->source;
				$target_rule = $config->dest;
				$modo_redireccion = $config->redirect_mode;
				$redireccion = $config->redirect;
				$descripcion = $config->description;
				$log = $config->enablelog;
				break;
			}
		}
		return $this->render("@App/acl/editar_acl.html.twig",array(
			"ubicacion"=>$plantel,
			"estado"=>$estado,
			"nombre"=>$nombre,
			"cliente"=>$cliente,
			"target_rule"=>$target_rule,
			"modo_redireccion"=>$modo_redireccion,
			"redireccion"=>$redireccion,
			"descripcion"=>$descripcion,
			"log"=>$log
		));
	}

	public function eliminar_targetAction()
	{
		$plantel=$_POST['plantel'];
		$libreria_dom = new \DOMDocument; 
	    $libreria_dom->load("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
	    $squidguarddest = $libreria_dom->documentElement;
	    $config = $squidguarddest->getElementsByTagName('config');
	    foreach ($config as $nodo) 
	    {
	    	$uri = $nodo->getElementsByTagName('name');
        	$valor = $uri->item(0)->nodeValue;
	        if($valor == $_POST['valor'])
	            $squidguarddest->removeChild($nodo);
	    }
	    $libreria_dom->save("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
	    $xml = simplexml_load_file("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
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
		$archivo = fopen("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", 'w');
		fwrite($archivo, $contenido);
		fclose($archivo);
		return $this->redirectToRoute("grupos_target");
	}	

	public function registro_targetAction(Request $request)
	{
		$ubicacion=$_REQUEST['id'];
		$informacion_interfaces_plantel = $this->informacion_interfaces_plantel($ubicacion);
		if(isset($_POST['guardar']))
		{
			foreach($_POST['plantel'] as $plantel_grupo)
			{
				$xml = simplexml_load_file("clients/Ejemplo_2/$plantel_grupo/info_squidguardacl.xml");
				$product = $xml->addChild('config');
				$product->addChild('name', $_POST['nombre']);
				$product->addChild('domains', $_POST['lista_dominios']);
				$product->addChild('urls', $_POST['lista_url']);
				$product->addChild('expressions', $_POST['expression_regular']);
				$product->addChild('redirect_mode', $_POST['modo_redireccion']);
				$product->addChild('redirect', $_POST['redireccion']);
				$product->addChild('description', $_POST['descripcion']);
				$product->addChild('enablelog', $_POST['log']);
				file_put_contents("clients/Ejemplo_2/$plantel_grupo/info_squidguardacl.xml", $xml->asXML());
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
				$archivo = fopen("clients/Ejemplo_2/$plantel_grupo/info_squidguardacl.xml", 'w');
				fwrite($archivo, $contenido);
				fclose($archivo);
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
