<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class gruposController extends Controller
{
	# Funcion para leer el archivo txt y guardar los datos en la base #
	public function leer_archivo_txtAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$db = $em->getConnection();
		# Archivo interfaces #
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == "ROLE_ADMINISTRATOR")
	    {
	    	$grupo=$u->getGrupo();
	    	$grupos = $this->obtener_nombre_grupo($grupo);
	    	$interfaces = array("wan", "lan", "opt1", "opt2","opt3");
	    	$interfaces_archivo = fopen("interfaces.txt", "w");
	    	foreach ($grupos as $equipo_grupos) 
			{
				foreach ($equipo_grupos as $equipo) 
				{
					$xml = simplexml_load_file("clients/$grupo/$equipo/info.xml");
					foreach ($interfaces as $interfaces_equipo) 
					{
						$tipo_interfas = $xml->xpath("/interfaces/$interfaces_equipo/if");
		        		$nombre = $xml->xpath("/interfaces/$interfaces_equipo/descr");
		        		$ip = $xml->xpath("/interfaces/$interfaces_equipo/ipaddr");
		        		foreach ($tipo_interfas as $interfas) 
						{
							fwrite($interfaces_archivo, $interfas."|");
						}
						foreach ($nombre as $nombre_interfas) 
						{
							fwrite($interfaces_archivo, $nombre_interfas . "|".$interfaces_equipo."|");
						}
						foreach ($ip as $ip_equipo) 
						{
							$ip_nueva = preg_replace('/\d{1,3}$/', '', $ip_equipo);
							if($ip_nueva == "dhcp")
							{
								fwrite($interfaces_archivo, "192.168.0." . "|UI|".$equipo."|\n");
							}
							else
							{
								$resultado_ip = $ip_nueva . "|UI|$equipo|\n";
								fwrite($interfaces_archivo, $resultado_ip);
							}
						}
					}
				}
			}
			fclose($interfaces_archivo);
	    }
		# Query para borrar la tabla grupos de la base de datos #
		$query_delete_grupos = "DELETE FROM grupos";
		$stmt_delete_grupos = $db->prepare($query_delete_grupos);
		$params_delete_grupos =array();
		$stmt_delete_grupos->execute($params_delete_grupos);
		$flush_delete_grupos=$em->flush();
		# Query para que la secuencia del contador regrese a 1 #
		$query_alter_grupos = "ALTER SEQUENCE grupos_id_seq RESTART WITH 1";
		$stmt_alter_grupos = $db->prepare($query_alter_grupos);
		$params_alter_grupos =array();
		$stmt_alter_grupos->execute($params_alter_grupos);
		$flush_alter_grupos=$em->flush();
		# Query para borrar la tabla grupos de la base de datos #
		$query_delete_interfaces = "DELETE FROM interfaces";
		$stmt_delete_interfaces = $db->prepare($query_delete_interfaces);
		$params_delete_interfaces =array();
		$stmt_delete_interfaces->execute($params_delete_interfaces);
		$flush_delete_interfaces=$em->flush();
		# Query para que la secuencia del contador regrese a 1 #
		$query_alter_interfaces = "ALTER SEQUENCE interfaces_id_seq RESTART WITH 1";
		$stmt_alter_interfaces = $db->prepare($query_alter_interfaces);
		$params_alter_interfaces =array();
		$stmt_alter_interfaces->execute($params_alter_interfaces);
		$flush_alter_interfaces=$em->flush();
		# Variable para leer el archivo informacion.txt #
		$filas=file('informacion.txt'); 
		foreach($filas as $value)
		{
			list($ip, $grupo, $plantel) = explode("|", $value);
			'ip: '.$ip.'<br/>';
			'grupo: '.$grupo.'<br/>';
			'plantel: '.$plantel.'<br/><br/>';
			$query_insertar_grupos = "INSERT INTO grupos VALUES (nextval('grupos_id_seq'),'$ip','$grupo','$plantel')";
			$stmt_insertar_grupos = $db->prepare($query_insertar_grupos);
			$params_insertar_grupos =array();
			$stmt_insertar_grupos->execute($params_insertar_grupos);
			$flush_insertar_grupos=$em->flush();
		}
		$archivo_interfaces=file('interfaces.txt'); 
		foreach($archivo_interfaces as $archivo_interfas)
		{
			list($interfaz, $tipo, $nombre, $ip, $grupo, $plantel) = explode("|", $archivo_interfas);
			'interfaz: '.$interfaz.'<br/>';
			'tipo: '.$tipo.'<br/>'; 
			'nombre: '.$nombre.'<br/>'; 
			'ip: '.$ip.'<br/>';
			'grupo: '.$grupo.'<br/>';
			'plantel: '.$plantel.'<br/><br/>';
			$query_insertar_interfaces = "INSERT INTO interfaces VALUES (nextval('interfaces_id_seq'),'$interfaz','$tipo','$nombre','$ip','$grupo','$plantel')";
			$stmtquery_insertar_interfaces = $db->prepare($query_insertar_interfaces);
			$paramsquery_insertar_interfaces =array();
			$stmtquery_insertar_interfaces->execute($paramsquery_insertar_interfaces);
			$flushquery_insertar_interfaces=$em->flush();
		}
		return $this->redirectToRoute("grupos");
	}

	# Funcion para mostrar los grupos #
	public function gruposAction()
	{
		$u = $this->getUser();
		if($u != null)
		{
	        $role=$u->getRole();
	        if($role == "ROLE_SUPERUSER")
	        {
	        	$recuperar_grupo_grupos = $this->recuperar_grupo_grupos();
				return $this->render("@App/grupos/grupos.html.twig", array(
					"grupo"=>$recuperar_grupo_grupos
				));
	        }
	        if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
	        {
	        	return $this->redirectToRoute("ver_ip");
	        }
	    }
	}

	# Funcion para listar las Ip #
	public function ver_ipAction()
	{
		$u = $this->getUser();
		$role=$u->getRole();
		if($role == "ROLE_SUPERUSER")
		{
			$plantel=$_REQUEST['id'];
			$grupos = $this->obtener_ip_plantel_grupos($plantel);
			return $this->render("@App/grupos/ver_ip.html.twig", array(
				"grupos"=>$grupos
			));
		}
		if($role == "ROLE_ADMINISTRATOR" or $role == "ROLE_USER")
		{
			$grupo=$u->getGrupo();
			$grupos = $this->obtener_ip_plantel_grupos($grupo);
			return $this->render("@App/grupos/ver_ip.html.twig", array(
				"grupos"=>$grupos
			));
		}
	}

	# Area de consultas #
	# Funcion utilizada en leer_archivo_txt #
	private function obtener_nombre_grupo($grupo)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT g.descripcion FROM AppBundle:grupos g WHERE g.nombre = :grupo')->setParameter('grupo', $grupo);;
		$grupos = $query->getResult();
		return $grupos;
	}
	# Funcion utilizada en grupos #
	private function recuperar_grupo_grupos()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT DISTINCT g.nombre FROM AppBundle:grupos g ORDER BY g.nombre ASC');
		$grupos = $query->getResult();
		return $grupos;
	}
	# Funcion utilizada en ver_ip #
	private function obtener_ip_plantel_grupos($grupo)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery('SELECT g.id, g.ip, g.descripcion FROM AppBundle:grupos g
			WHERE g.nombre = :grupo ORDER BY g.descripcion ASC')->setParameter('grupo', $grupo);
		$grupos = $query->getResult();
		return $grupos;
	}
}
