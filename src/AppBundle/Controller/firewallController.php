<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class firewallController extends Controller
{
	public function grupos_firewallAction()
	{
		$u = $this->getUser();
		if($u != null)
		{
	        $role=$u->getRole();
	        $grupo=$u->getGrupo();
	        if($role == "ROLE_SUPERUSER")
	        {
	        	$grupos = $this->obtener_grupo_nombre();
				return $this->render("@App/firewall/grupos_firewall.html.twig", array(
					"grupo"=>$grupos
				));
	        }
	        if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
	        	return $this->redirectToRoute("lista_firewall");
	    }
	}

	public function lista_firewallAction()
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
			$obtener_nombre_interfas = $this->obtener_nombre_interfas($ubicacion);
			$xml = simplexml_load_file("clients/$grupo/$ubicacion/info_filter.xml");
			return $this->render('@App/firewall/lista_firewall.html.twig', array(
				'ubicacion'=>$ubicacion,
				'obtener_nombre_interfas'=>$obtener_nombre_interfas,
				'xmls'=>$xml->rule
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
					$config->disabled = $_POST['estado'];
					$config->name = $_POST['nombre'];
					$config->source = $_POST['cliente'];
					$config->time = "";
					$config->dest = $_POST['target_rule'];
					$config->notallowingip = $_POST['not_ip'];
					$config->redirect_mode = $_POST['modo_redireccion'];
					$config->redirect = $_POST['redireccion'];
					$config->safesearch = "on";
					$config->rewrite = "";
					$config->overrewrite ="";
					$config->description = $_POST['descripcion'];
					$config->enablelog = $_POST['log'];
				}
			}
			$xml->asXML("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
			$contenido = "\t\t<squidguardacl>\n";
			foreach($xml->config as $config)
			{
			    $contenido .= "\t\t\t<config>\n";
			    $contenido .= "\t\t\t\t<disabled>" . $config->disabled . "</disabled>\n";
			    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
			    $contenido .= "\t\t\t\t<source>" . $config->source . "</source>\n";
			    $contenido .= "\t\t\t\t<time>" . $config->time . "</time>\n";
			    $contenido .= "\t\t\t\t<dest>" . $config->dest . "</dest>\n";
			    $contenido .= "\t\t\t\t<notallowingip>" . $config->notallowingip . "</notallowingip>\n";
			    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
			    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
			    $contenido .= "\t\t\t\t<safesearch>" . $config->safesearch . "</safesearch>\n";
			    $contenido .= "\t\t\t\t<rewrite>" . $config->rewrite . "</rewrite>\n";
			    $contenido .= "\t\t\t\t<overrewrite>" . $config->overrewrite . "</overrewrite>\n";
			    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
			    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
			    $contenido .= "\t\t\t</config>\n";
			}
		    $contenido .= "\t\t</squidguardacl>";
			$archivo = fopen("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", 'w');
			fwrite($archivo, $contenido);
			fclose($archivo);
			return $this->redirectToRoute("grupos_acl");
		}
		foreach($xml->config as $config)
		{
			if($config->name== $_POST['valor'] )
			{
				$estado = $config->disabled;
				$nombre = $config->name;
				$cliente = $config->source;
				$target_rule = $config->dest;
				$lista_target = explode(' ',$target_rule);
				$not_ip = $config->notallowingip;
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
			"lista_target"=>$lista_target,
			"not_ip"=>$not_ip,
			"modo_redireccion"=>$modo_redireccion,
			"redireccion"=>$redireccion,
			"descripcion"=>$descripcion,
			"log"=>$log,
			'xmls'=>$xmls= $xml2->config
		));
	}

	public function eliminar_aclAction()
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
		$contenido = "\t\t<squidguardacl>\n";
		foreach($xml->config as $config)
		{
		    $contenido .= "\t\t\t<config>\n";
		    $contenido .= "\t\t\t\t<disabled>" . $config->disabled . "</disabled>\n";
		    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
		    $contenido .= "\t\t\t\t<source>" . $config->source . "</source>\n";
		    $contenido .= "\t\t\t\t<time>" . $config->time . "</time>\n";
		    $contenido .= "\t\t\t\t<dest>" . $config->dest . "</dest>\n";
		    $contenido .= "\t\t\t\t<notallowingip>" . $config->notallowingip . "</notallowingip>\n";
		    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
		    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
		    $contenido .= "\t\t\t\t<safesearch>" . $config->safesearch . "</safesearch>\n";
		    $contenido .= "\t\t\t\t<rewrite>" . $config->rewrite . "</rewrite>\n";
		    $contenido .= "\t\t\t\t<overrewrite>" . $config->overrewrite . "</overrewrite>\n";
		    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
		    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
		    $contenido .= "\t\t\t</config>\n";
		}
	    $contenido .= "\t\t</squidguardacl>";
		$archivo = fopen("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", 'w');
		fwrite($archivo, $contenido);
		fclose($archivo);
		return $this->redirectToRoute("grupos_acl");
	}	

	public function registro_firewallAction()
	{
		$plantel=$_REQUEST['id'];
		$obtener_nombre_interfas = $this->obtener_nombre_interfas($plantel);
		$xml = simplexml_load_file("clients/Ejemplo_2/$plantel/info_squidguarddest.xml");
		if(isset($_POST['guardar']))
		{
			$xml = simplexml_load_file("clients/Ejemplo_2/$plantel/info_squidguardacl.xml");
			$target_rule = implode(" ",$_POST['lista_target']);
			$product = $xml->addChild('config');
			$product->addChild('disabled', $_POST['estado']);
			$product->addChild('name', $_POST['nombre']);
			$product->addChild('source', $_POST['cliente']);
			$product->addChild('time', "");
			$product->addChild('dest', $target_rule." all [ all]");
			$product->addChild('notallowingip', $_POST['not_ip']);
			$product->addChild('redirect_mode', $_POST['modo_redireccion']);
			$product->addChild('redirect', $_POST['redireccion']);
			$product->addChild('safesearch', "on");
			$product->addChild('rewrite', "");
			$product->addChild('overrewrite', "");
			$product->addChild('description', $_POST['descripcion']);
			$product->addChild('enablelog', $_POST['log']);
			file_put_contents("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", $xml->asXML());
			$contenido = "\t\t<squidguardacl>\n";
			foreach($xml->config as $config)
			{
			    $contenido .= "\t\t\t<config>\n";
			    $contenido .= "\t\t\t\t<disabled>" . $config->disabled . "</disabled>\n";
			    $contenido .= "\t\t\t\t<name>" . $config->name . "</name>\n";
			    $contenido .= "\t\t\t\t<source>" . $config->source . "</source>\n";
			    $contenido .= "\t\t\t\t<time>" . $config->time . "</time>\n";
			    $contenido .= "\t\t\t\t<dest>" . $config->dest . "</dest>\n";
			    $contenido .= "\t\t\t\t<notallowingip>" . $config->notallowingip . "</notallowingip>\n";
			    $contenido .= "\t\t\t\t<redirect_mode>" . $config->redirect_mode . "</redirect_mode>\n";
			    $contenido .= "\t\t\t\t<redirect>" . $config->redirect . "</redirect>\n";
			    $contenido .= "\t\t\t\t<safesearch>" . $config->safesearch . "</safesearch>\n";
			    $contenido .= "\t\t\t\t<rewrite>" . $config->rewrite . "</rewrite>\n";
			    $contenido .= "\t\t\t\t<overrewrite>" . $config->overrewrite . "</overrewrite>\n";
			    $contenido .= "\t\t\t\t<description>" . $config->description . "</description>\n";
			    $contenido .= "\t\t\t\t<enablelog>" . $config->enablelog . "</enablelog>\n";
			    $contenido .= "\t\t\t</config>\n";
			}
		    $contenido .= "\t\t</squidguardacl>";
			$archivo = fopen("clients/Ejemplo_2/$plantel/info_squidguardacl.xml", 'w');
			fwrite($archivo, $contenido);
			fclose($archivo);
			return $this->redirectToRoute("grupos_acl");
		}
		return $this->render('@App/firewall/registro_firewall.html.twig', array(
			'plantel'=>$plantel,
			'obtener_nombre_interfas'=>$obtener_nombre_interfas,
			'xmls'=>$xmls= $xml->config
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
	private function obtener_nombre_interfas($descripcion)
	{
		$em = $this->getDoctrine()->getEntityManager();
	    $db = $em->getConnection();
		$query = "SELECT nombre, tipo FROM interfaces WHERE descripcion = '$descripcion'";
		$stmt = $db->prepare($query);
		$params =array();
		$stmt->execute($params);
		$grupos=$stmt->fetchAll();
		return $grupos;
	}
}
