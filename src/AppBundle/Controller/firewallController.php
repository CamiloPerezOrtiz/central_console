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
		#$plantel=$_REQUEST['id'];
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == 'ROLE_SUPERUSER')
		{
			$plantel=$_REQUEST['plantel'];
			$grupo=$_REQUEST['grupo'];
		}
		else
		{
			$grupo=$u->getGrupo();
			$plantel=$_REQUEST['id'];
		}
		$obtener_nombre_interfas = $this->obtener_nombre_interfas($plantel);
		if(isset($_POST['guardar']))
		{
			$xml = simplexml_load_file("clients/$grupo/$plantel/info_filter.xml");
			foreach($xml->config as $config)
			{
				if($config->name==$_POST['nombre'])
				{
					$estatus="The name that you try to register already exists in ".$plantel.".";
				$this->session->getFlashBag()->add("estatus",$estatus);
				return $this->redirectToRoute("grupos_firewall");

				}
			}
			$contenido = "\t<filter>\n";
			foreach($xml->rule as $rule)
			{
			    $contenido .= "\t\t<rule>\n";
			    $contenido .= "\t\t\t<id>" . $rule->id . "</id>\n";
			    $contenido .= "\t\t\t<tracker>" . $rule->tracker . "</tracker>\n";
			    $contenido .= "\t\t\t<type>" . $rule->type . "</type>\n";
			    $contenido .= "\t\t\t<interface>" . $rule->interface . "</interface>\n";
			    $contenido .= "\t\t\t<ipprotocol>" . $rule->ipprotocol . "</ipprotocol>\n";
			    $contenido .= "\t\t\t<tag>" . $rule->tag . "</tag>\n";
			    $contenido .= "\t\t\t<tagged>" . $rule->tagged . "</tagged>\n";
			    $contenido .= "\t\t\t<max>" . $rule->max . "</max>\n";
			    $contenido .= "\t\t\t<max-src-nodes>" . $rule->{'max-src-nodes'} . "</max-src-nodes>\n";
			    $contenido .= "\t\t\t<max-src-conn>" . $rule->{'max-src-conn'} . "</max-src-conn>\n";
			    $contenido .= "\t\t\t<max-src-states>" . $rule->{'max-src-states'} . "</max-src-states>\n";
			    $contenido .= "\t\t\t<statetimeout>" . $rule->statetimeout . "</statetimeout>\n";
			    $contenido .= "\t\t\t<statetype>" . $rule->statetype . "</statetype>\n";
			    $contenido .= "\t\t\t<os>" . $rule->os . "</os>\n";
			    if($rule->protocol == true)
			    	$contenido .= "\t\t\t<protocol>" . $rule->protocol . "</protocol>\n";
			    if($rule->icmptype == true)
			    	$contenido .= "\t\t\t<icmptype>" . $rule->icmptype . "</icmptype>\n";
			    $contenido .= "\t\t\t<source>\n";
			    	if($rule->source->any == true)
			    		$contenido .= "\t\t\t\t<any>" . $rule->source->any . "</any>\n";
			    	if($rule->source->address == true)
			    		$contenido .= "\t\t\t\t<address>" . $rule->source->address . "</address>\n";
			    	if($rule->source->network == true)
			    		$contenido .= "\t\t\t\t<network>" . $rule->source->network . "</network>\n";
			    	if($rule->source->not == true)
			    		$contenido .= "\t\t\t\t<not>" . $rule->source->not . "</not>\n";
			    $contenido .= "\t\t\t</source>\n";
			    $contenido .= "\t\t\t<destination>\n";
			    	if($rule->destination->any == true)
			    		$contenido .= "\t\t\t\t<any>" . $rule->destination->any . "</any>\n";
			    	if($rule->destination->network == true)
			    		$contenido .= "\t\t\t\t<network>" . $rule->destination->network . "</network>\n";
			    	if($rule->destination->address == true)
			    		$contenido .= "\t\t\t\t<address>" . $rule->destination->address . "</address>\n";
			    	if($rule->destination->port == true)
			    		$contenido .= "\t\t\t\t<port>" . $rule->destination->port . "</port>\n";
			    $contenido .= "\t\t\t</destination>\n";
			    $contenido .= "\t\t\t<descr>" . $rule->descr . "</descr>\n";
			    if($rule->gateway== true)
			    	$contenido .= "\t\t\t<gateway>" . $rule->gateway . "</gateway>\n";
			    if($rule->{'associated-rule-id'}== true)
			    	$contenido .= "\t\t\t<associated-rule-id>" . $rule->{'associated-rule-id'} . "</associated-rule-id>\n";
			   	if($rule->updated== true)
			   	{
			   		$contenido .= "\t\t\t<updated>\n";
				    	if($rule->updated->time == true)
				    		$contenido .= "\t\t\t\t<time>" . $rule->updated->time . "</time>\n";
				    	if($rule->updated->username == true)
				    		$contenido .= "\t\t\t\t<username>" . $rule->updated->username . "</username>\n";
				    $contenido .= "\t\t\t</updated>\n";
			   	}
			   	if($rule->created== true)
			   	{
			   		$contenido .= "\t\t\t<created>\n";
				    	if($rule->created->time == true)
				    		$contenido .= "\t\t\t\t<time>" . $rule->created->time . "</time>\n";
				    	if($rule->created->username == true)
				    		$contenido .= "\t\t\t\t<username>" . $rule->created->username . "</username>\n";
				    $contenido .= "\t\t\t</created>\n";
			   	}
			   	if($rule->disabled == true)
			    	$contenido .= "\t\t\t<disabled>" . $rule->disabled . "</disabled>\n";
			    $contenido .= "\t\t</rule>\n";
			}
			if(isset($_POST['guardar']))
			{
				$contenido .= "\t\t<rule>\n";
				    $contenido .= "\t\t\t<id></id>\n";
				    $contenido .= "\t\t\t<tracker>" . time() . "</tracker>\n";
				    $contenido .= "\t\t\t<type>" . $_POST['action'] . "</type>\n";
				    $contenido .= "\t\t\t<interface>" . $_POST['interface'] . "</interface>\n";
				    $contenido .= "\t\t\t<ipprotocol>" . $_POST['adress'] . "</ipprotocol>\n";
				    $contenido .= "\t\t\t<tag></tag>\n";
				    $contenido .= "\t\t\t<tagged></tagged>\n";
				    $contenido .= "\t\t\t<max></max>\n";
				    $contenido .= "\t\t\t<max-src-nodes></max-src-nodes>\n";
				    $contenido .= "\t\t\t<max-src-conn></max-src-conn>\n";
				    $contenido .= "\t\t\t<max-src-states></max-src-states>\n";
				    $contenido .= "\t\t\t<statetimeout></statetimeout>\n";
				    $contenido .= "\t\t\t<statetype></statetype>\n";
				    $contenido .= "\t\t\t<os></os>\n";
				    if($_POST['protocolo'] === 'any')
						$nada = "Nada que hacer";
					else
						$contenido .= "\t\t\t<protocol>" . $_POST['protocolo'] . "</protocol>\n";
					if($_POST['protocolo']=== 'icmp')
					{
						$numero = count($_POST['icmp_subtypes']);
						$archivo=fopen("$plantel-icmp.txt","w") or die("Problemas con el servidor intente mas tarde.");
						foreach ($_POST['icmp_subtypes'] as $icmp) 
						{
							if($numero == 1)
								$icmp;
							else
								$icmp . ",";
							fputs($archivo,$icmp. ",");
						}
						fputs($archivo,"|"."\n");
						$delimitador=file("$plantel-icmp.txt");
						foreach($delimitador as $dem)
						{
							list($ip_interfas) = explode('|', $dem);
						}
						$icmp = trim($ip_interfas, ",|");
						$contenido .= "\t\t\t<icmptype>" . $icmp . "</icmptype>\n";
						unlink("$plantel-icmp.txt");
					}
					$contenido .= "\t\t\t<source>\n";
						if($_POST['sourceAdvancedType'] === 'any')
							$contenido .= "\t\t\t\t<any></any>\n";
						elseif($_POST['sourceAdvancedType'] === 'wan' or
						$_POST['sourceAdvancedType'] === 'wanip' or $_POST['sourceAdvancedType'] === 'lan' or
						$_POST['sourceAdvancedType'] === 'lanip' or $_POST['sourceAdvancedType'] === 'opt1' or
						$_POST['sourceAdvancedType'] === 'opt1ip' or $_POST['sourceAdvancedType'] === 'opt2' or
						$_POST['sourceAdvancedType'] === 'opt2ip' or $_POST['sourceAdvancedType'] === 'opt3' or
						$_POST['sourceAdvancedType'] === 'opt3ip' or $_POST['sourceAdvancedType'] === 'pppoe' or
						$_POST['sourceAdvancedType'] === 'l2tp')
							$contenido .= "\t\t\t\t<network>" . $_POST['sourceAdvancedType'] . "</network>\n";
						else
							if($_POST['sourceAdvancedType'] === 'single')
								$contenido .= "\t\t\t\t<address>" . $_POST['sourceAddresMask'] . "</address>\n";
							elseif($_POST['sourceAdvancedType'] === 'network' and $_POST['sourceAdvancedAdressMask1'] === '32')
								$contenido .= "\t\t\t\t<address>" . $_POST['sourceAddresMask'] . "</address>\n";
							else
								$contenido .= "\t\t\t\t<address>" . $_POST['sourceAddresMask'] . "/". $_POST['sourceAdvancedAdressMask1'] . "</address>\n";
						if($_POST['sourceInvertMatch'] === 'on')
							$contenido .= "\t\t\t\t<not></not>\n";
						if(empty($_POST['sourcePortRangeFrom']))
							$nada= "Nada que hacer";
						if($_POST['sourcePortRangeFrom'] === "any")
							$nada= "Nada que hacer";
						if(empty($_POST['sourcePortRangeCustom']) === empty($_POST['sourcePortRangeCustomTo']))
							$nada= "Nada que hacer";
						if($_POST['sourcePortRangeFrom'] === $_POST['sourcePortRangeTo'] and !empty($_POST['sourcePortRangeCustom']) and !empty($_POST['sourcePortRangeCustomTo']))
							$contenido .= "\t\t\t\t<port>" . $_POST['sourcePortRangeCustom'] . "</port>\n";
						if($_POST['sourcePortRangeFrom'] === "5999")
							$contenido .= "\t\t\t\t<port>5999</port>\n";
						if($_POST['sourcePortRangeFrom'] === "53")
							$contenido .= "\t\t\t\t<port>53</port>\n";
						if($_POST['sourcePortRangeFrom'] === "21")
							$contenido .= "\t\t\t\t<port>21</port>\n";
						if($_POST['sourcePortRangeFrom'] === "3000")
							$contenido .= "\t\t\t\t<port>3000</port>\n";
						if($_POST['sourcePortRangeFrom'] === "80")
							$contenido .= "\t\t\t\t<port>80</port>\n";
						if($_POST['sourcePortRangeFrom'] === "443")
							$contenido .= "\t\t\t\t<port>443</port>\n";
						if($_POST['sourcePortRangeFrom'] === "5190")
							$contenido .= "\t\t\t\t<port>5190</port>\n";
						if($_POST['sourcePortRangeFrom'] === "113")
							$contenido .= "\t\t\t\t<port>113</port>\n";
						if($_POST['sourcePortRangeFrom'] === "993")
							$contenido .= "\t\t\t\t<port>993</port>\n";
						if($_POST['sourcePortRangeFrom'] === "4500")
							$contenido .= "\t\t\t\t<port>4500</port>\n";
						if($_POST['sourcePortRangeFrom'] === "500")
							$contenido .= "\t\t\t\t<port>500</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1701")
							$contenido .= "\t\t\t\t<port>1701</port>\n";
						if($_POST['sourcePortRangeFrom'] === "389")
							$contenido .= "\t\t\t\t<port>389</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1755")
							$contenido .= "\t\t\t\t<port>1755</port>\n";
						if($_POST['sourcePortRangeFrom'] === "7000")
							$contenido .= "\t\t\t\t<port>7000</port>\n";
						if($_POST['sourcePortRangeFrom'] === "445")
							$contenido .= "\t\t\t\t<port>445</port>\n";
						if($_POST['sourcePortRangeFrom'] === "3389")
							$contenido .= "\t\t\t\t<port>3389</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1512")
							$contenido .= "\t\t\t\t<port>1512</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1863")
							$contenido .= "\t\t\t\t<port>1863</port>\n";
						if($_POST['sourcePortRangeFrom'] === "119")
							$contenido .= "\t\t\t\t<port>119</port>\n";
						if($_POST['sourcePortRangeFrom'] === "123")
							$contenido .= "\t\t\t\t<port>123</port>\n";
						if($_POST['sourcePortRangeFrom'] === "138")
							$contenido .= "\t\t\t\t<port>138</port>\n";
						if($_POST['sourcePortRangeFrom'] === "137")
							$contenido .= "\t\t\t\t<port>137</port>\n";
						if($_POST['sourcePortRangeFrom'] === "139")
							$contenido .= "\t\t\t\t<port>139</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1194")
							$contenido .= "\t\t\t\t<port>1194</port>\n";
						if($_POST['sourcePortRangeFrom'] === "110")
							$contenido .= "\t\t\t\t<port>110</port>\n";
						if($_POST['sourcePortRangeFrom'] === "995")
							$contenido .= "\t\t\t\t<port>995</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1723")
							$contenido .= "\t\t\t\t<port>1723</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1812")
							$contenido .= "\t\t\t\t<port>1812</port>\n";
						if($_POST['sourcePortRangeFrom'] === "1813")
							$contenido .= "\t\t\t\t<port>1813</port>\n";
						if($_POST['sourcePortRangeFrom'] === "5004")
							$contenido .= "\t\t\t\t<port>5004</port>\n";
						if($_POST['sourcePortRangeFrom'] === "5060")
							$contenido .= "\t\t\t\t<port>5060</port>\n";
						if($_POST['sourcePortRangeFrom'] === "25")
							$contenido .= "\t\t\t\t<port>25</port>\n";
						if($_POST['sourcePortRangeFrom'] === "465")
							$contenido .= "\t\t\t\t<port>465</port>\n";
						if($_POST['sourcePortRangeFrom'] === "161")
							$contenido .= "\t\t\t\t<port>161</port>\n";
						if($_POST['sourcePortRangeFrom'] === "162")
							$contenido .= "\t\t\t\t<port>162</port>\n";
						if($_POST['sourcePortRangeFrom'] === "22")
							$contenido .= "\t\t\t\t<port>22</port>\n";
						if($_POST['sourcePortRangeFrom'] === "3478")
							$contenido .= "\t\t\t\t<port>3278</port>\n";
						if($_POST['sourcePortRangeFrom'] === "587")
							$contenido .= "\t\t\t\t<port>587</port>\n";
						if($_POST['sourcePortRangeFrom'] === "3544")
							$contenido .= "\t\t\t\t<port>3544</port>\n";
						if($_POST['sourcePortRangeFrom'] === "23")
							$contenido .= "\t\t\t\t<port>23</port>\n";
						if($_POST['sourcePortRangeFrom'] === "69")
							$contenido .= "\t\t\t\t<port>69</port>\n";
						if($_POST['sourcePortRangeFrom'] === "5900")
							$contenido .= "\t\t\t\t<port>5900</port>\n";
					$contenido .= "\t\t\t</source>\n";
					$contenido .= "\t\t\t<destination>\n";
						if($_POST['destinationType'] === 'any')
							$contenido .= "\t\t\t\t<any></any>\n";
						elseif($_POST['destinationType'] === 'wan' or
						$_POST['destinationType'] === 'wanip' or $_POST['destinationType'] === 'lan' or
						$_POST['destinationType'] === 'lanip' or $_POST['destinationType'] === 'opt1' or
						$_POST['destinationType'] === 'opt1ip' or $_POST['destinationType'] === 'opt2' or
						$_POST['destinationType'] === 'opt2ip' or $_POST['destinationType'] === 'opt3' or
						$_POST['destinationType'] === 'opt3ip' or $_POST['destinationType'] === 'pppoe' or
						$_POST['destinationType'] === 'l2tp')
							$contenido .= "\t\t\t\t<network>" . $_POST['destinationType'] . "</network>\n";
						else
							if($_POST['destinationType'] === 'single')
								$contenido .= "\t\t\t\t<address>" . $_POST['destinationAddresMask'] . "</address>\n";
							elseif($_POST['destinationType'] === 'network' and $_POST['sourceAdvancedAdressMask1'] === '32')
								$contenido .= "\t\t\t\t<address>" . $_POST['destinationAddresMask'] . "</address>\n";
							else
								$contenido .= "\t\t\t\t<address>" . $_POST['destinationAddresMask'] . "/". $_POST['destinationAdressMask2'] . "</address>\n";
						if($_POST['destinationInvertMatch'] === 'on')
							$contenido .= "\t\t\t\t<not></not>\n";
						if(empty($_POST['destinationPortRangeFrom']))
							$nada= "Nada que hacer";
						if($_POST['destinationPortRangeFrom'] === "any")
							$nada= "Nada que hacer";
						if(empty($_POST['sourcePortRangeCustom']) === empty($_POST['sourcePortRangeCustomTo']))
							$nada= "Nada que hacer";
						if($_POST['destinationPortRangeFrom'] === $_POST['sourcePortRangeTo'] and !empty($_POST['sourcePortRangeCustom']) and !empty($_POST['sourcePortRangeCustomTo']))
							$contenido .= "\t\t\t\t<port>" . $_POST['sourcePortRangeCustom'] . "</port>\n";
						if($_POST['destinationPortRangeFrom'] === "5999")
							$contenido .= "\t\t\t\t<port>5999</port>\n";
						if($_POST['destinationPortRangeFrom'] === "53")
							$contenido .= "\t\t\t\t<port>53</port>\n";
						if($_POST['destinationPortRangeFrom'] === "21")
							$contenido .= "\t\t\t\t<port>21</port>\n";
						if($_POST['destinationPortRangeFrom'] === "3000")
							$contenido .= "\t\t\t\t<port>3000</port>\n";
						if($_POST['destinationPortRangeFrom'] === "80")
							$contenido .= "\t\t\t\t<port>80</port>\n";
						if($_POST['destinationPortRangeFrom'] === "443")
							$contenido .= "\t\t\t\t<port>443</port>\n";
						if($_POST['destinationPortRangeFrom'] === "5190")
							$contenido .= "\t\t\t\t<port>5190</port>\n";
						if($_POST['destinationPortRangeFrom'] === "113")
							$contenido .= "\t\t\t\t<port>113</port>\n";
						if($_POST['destinationPortRangeFrom'] === "993")
							$contenido .= "\t\t\t\t<port>993</port>\n";
						if($_POST['destinationPortRangeFrom'] === "4500")
							$contenido .= "\t\t\t\t<port>4500</port>\n";
						if($_POST['destinationPortRangeFrom'] === "500")
							$contenido .= "\t\t\t\t<port>500</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1701")
							$contenido .= "\t\t\t\t<port>1701</port>\n";
						if($_POST['destinationPortRangeFrom'] === "389")
							$contenido .= "\t\t\t\t<port>389</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1755")
							$contenido .= "\t\t\t\t<port>1755</port>\n";
						if($_POST['destinationPortRangeFrom'] === "7000")
							$contenido .= "\t\t\t\t<port>7000</port>\n";
						if($_POST['destinationPortRangeFrom'] === "445")
							$contenido .= "\t\t\t\t<port>445</port>\n";
						if($_POST['destinationPortRangeFrom'] === "3389")
							$contenido .= "\t\t\t\t<port>3389</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1512")
							$contenido .= "\t\t\t\t<port>1512</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1863")
							$contenido .= "\t\t\t\t<port>1863</port>\n";
						if($_POST['destinationPortRangeFrom'] === "119")
							$contenido .= "\t\t\t\t<port>119</port>\n";
						if($_POST['destinationPortRangeFrom'] === "123")
							$contenido .= "\t\t\t\t<port>123</port>\n";
						if($_POST['destinationPortRangeFrom'] === "138")
							$contenido .= "\t\t\t\t<port>138</port>\n";
						if($_POST['destinationPortRangeFrom'] === "137")
							$contenido .= "\t\t\t\t<port>137</port>\n";
						if($_POST['destinationPortRangeFrom'] === "139")
							$contenido .= "\t\t\t\t<port>139</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1194")
							$contenido .= "\t\t\t\t<port>1194</port>\n";
						if($_POST['destinationPortRangeFrom'] === "110")
							$contenido .= "\t\t\t\t<port>110</port>\n";
						if($_POST['destinationPortRangeFrom'] === "995")
							$contenido .= "\t\t\t\t<port>995</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1723")
							$contenido .= "\t\t\t\t<port>1723</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1812")
							$contenido .= "\t\t\t\t<port>1812</port>\n";
						if($_POST['destinationPortRangeFrom'] === "1813")
							$contenido .= "\t\t\t\t<port>1813</port>\n";
						if($_POST['destinationPortRangeFrom'] === "5004")
							$contenido .= "\t\t\t\t<port>5004</port>\n";
						if($_POST['destinationPortRangeFrom'] === "5060")
							$contenido .= "\t\t\t\t<port>5060</port>\n";
						if($_POST['destinationPortRangeFrom'] === "25")
							$contenido .= "\t\t\t\t<port>25</port>\n";
						if($_POST['destinationPortRangeFrom'] === "465")
							$contenido .= "\t\t\t\t<port>465</port>\n";
						if($_POST['destinationPortRangeFrom'] === "161")
							$contenido .= "\t\t\t\t<port>161</port>\n";
						if($_POST['destinationPortRangeFrom'] === "162")
							$contenido .= "\t\t\t\t<port>162</port>\n";
						if($_POST['destinationPortRangeFrom'] === "22")
							$contenido .= "\t\t\t\t<port>22</port>\n";
						if($_POST['destinationPortRangeFrom'] === "3478")
							$contenido .= "\t\t\t\t<port>3278</port>\n";
						if($_POST['destinationPortRangeFrom'] === "587")
							$contenido .= "\t\t\t\t<port>587</port>\n";
						if($_POST['destinationPortRangeFrom'] === "3544")
							$contenido .= "\t\t\t\t<port>3544</port>\n";
						if($_POST['destinationPortRangeFrom'] === "23")
							$contenido .= "\t\t\t\t<port>23</port>\n";
						if($_POST['destinationPortRangeFrom'] === "69")
							$contenido .= "\t\t\t\t<port>69</port>\n";
						if($_POST['destinationPortRangeFrom'] === "5900")
							$contenido .= "\t\t\t\t<port>5900</port>\n";
					$contenido .= "\t\t\t</destination>\n";
					$contenido .= "\t\t\t<descr>" . $_POST['descripcion'] . "</descr>\n";
				$contenido .= "\t\t</rule>\n";   
			}
			$contenido .= "\t\t<separator>\n";
				if($xml->separator== true)
			   	{
			    	if($xml->separator->lan == true)
			    		$contenido .= "\t\t\t<lan>" . $xml->separator->lan . "</lan>\n";
			    	if($xml->separator->opt1 == true)
			    		$contenido .= "\t\t\t<opt1>" . $xml->separator->opt1 . "</opt1>\n";
			    	if($xml->separator->opt2 == true)
			    		$contenido .= "\t\t\t<opt2>" . $xml->separator->opt2 . "</opt2>\n";
			    	if($xml->separator->openvpn == true)
			    		$contenido .= "\t\t\t<openvpn>" . $xml->separator->openvpn . "</openvpn>\n";
			    	if($xml->separator->floatingrules == true)
			    		$contenido .= "\t\t\t<floatingrules>" . $xml->separator->floatingrules . "</floatingrules>\n";
			    	if($xml->separator->wan == true)
			    		$contenido .= "\t\t\t<wan>" . $xml->separator->wan . "</wan>\n";
			   	}
			$contenido .= "\t\t</separator>\n";
		    $contenido .= "\t</filter>";
			$archivo = fopen("clients/$grupo/$plantel/info_filter.xml", 'w');
			fwrite($archivo, $contenido);
			fclose($archivo);
			# Aplicar cambios #
			$archivo_cambio = fopen("clients/$grupo/$plantel/change_filter.xml", 'w');
			fwrite($archivo_cambio, $contenido);
			fclose($archivo_cambio);
			return $this->redirectToRoute("grupos_firewall");
		}
		return $this->render('@App/firewall/registro_firewall.html.twig', array(
			'grupo'=>$grupo,
			'plantel'=>$plantel,
			'obtener_nombre_interfas'=>$obtener_nombre_interfas
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
