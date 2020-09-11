<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Central Login Page</title>
	<link rel="stylesheet" href="../assets/milligram/milligram.min.css">

	<style>
		.center {
			text-align:center;
		}
		.row {
			margin-top: 25px;
		}
		.button-block {
			width:100%;
		}
		/* Custom color */
		.button-black {
		  background-color: black;
		  border-color: black;
		}
		.button-black.button-clear,
		.button-black.button-outline {
		  background-color: transparent;
		  color: black;
		}
		.button-black.button-clear {
		  border-color: transparent;
		}
		.alert {
	    padding: 15px;
	    margin-bottom: 20px;
	    border: 1px solid transparent;
	    border-radius: 4px;
		}
		.alert-danger {
	    color: #a94442;
	    background-color: #f2dede;
	    border-color: #ebccd1;
		}
	</style>
</head>
<body>
	<div class="container">
	<div class="row">
		<div class="column column-50 column-offset-25">
			<h1 class="center">Central Login</h1>
			<?php if(isset($error_msg)): ?>
				<h2 class="center alert alert-danger"><?php echo $error_msg; ?></h2>
			<?php endif; ?>
			<form class="" method="post" action="">
			  <fieldset>
			    <input type="text" placeholder="Username" id="username" name="username" required>
			    <input type="password" placeholder="Password" id="password" name="password" required>
			    <input type="hidden" name="request_data" value="<?php echo htmlspecialchars(json_encode($_GET)); ?>">
			    <input class="button button-outline button-block" type="submit" value="Login" name="submit">
			  </fieldset>
			</form>
		</div>
	</div>
</div>
</body>
</html>