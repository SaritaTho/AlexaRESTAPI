<form class="form-inline my-2 my-lg-0" method="post" action="/auth/?action=logout">
	<ul class="navbar-nav ml-auto">
		<li class="nav-item">
			<a href="/dashboard/account" class="nav-link" id="nav-email"><?php if (array_key_exists("email", $_SESSION)) { echo $_SESSION["email"]; } ?></a>
		</li>
	</ul>
	<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Logout</button>
</form>