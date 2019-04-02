function validarUsuario() 
{
	var nombre, email, passwordUno, passwordDos;
	nombre = document.getElementById("nombre").value;
	email = document.getElementById("email").value;
	passwordUno = document.getElementById("passwordUno").value;
	passwordDos = document.getElementById("passwordDos").value;
	expresion = /\w+@\w+\.+[a-z]/;
	if(nombre === "" || email === "" || passwordUno === "" || passwordDos ==="")
	{
		swal({
  				icon: "error",
  				title: "The fields name, email, password and repeat password are required."
			});
		return false;
	}
	else if(nombre.length>15)
	{
		swal({
  				icon: "error",
  				title: "The name can not be longer than 15 characters."
			});
		return false;
	}
	else if(email.length>50)
	{
		swal({
  				icon: "error",
  				title: "The email can not be longer than 50 characters."
			});
		return false;
	}
	else if (!expresion.test(email))
	{
		swal({
  				icon: "error",
  				title: "Please enter an email."
			});
		return false;
	}
}