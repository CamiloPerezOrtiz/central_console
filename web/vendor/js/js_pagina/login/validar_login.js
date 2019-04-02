function validarLogin() 
{
	var username, password;
	username = document.getElementById("username").value;
	password = document.getElementById("password").value;
	expresion = /\w+@\w+\.+[a-z]/;
	if(username === "" || password === "")
	{
		swal({
  				icon: "error",
  				title: "All fields are required."
			});
		return false;
	}
	else if(username.length>50)
	{
		swal({
  				icon: "error",
  				title: "The email can not be longer than 50 characters."
			});
		return false;
	}
	else if (!expresion.test(username))
	{
		swal({
  				icon: "error",
  				title: "Please enter an email."
			});
		return false;
	}
}