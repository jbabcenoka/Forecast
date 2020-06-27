<!-- DO NOT CHANGE THIS FILE -->
<?php 
 //If "result_status" is not set, most likely the user has opened the
 // view.php file directly. We don't want to allow this.
 if (!isset($result))
 {
      header("Location: index.php");
 }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Currency converter">
	<meta name="author" content="">
	<title>Weather forcast</title>

	<!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </head>

    <body>

	<div class="container">
            <div class="row">
                <div class="col">

                    <form method="GET">
		
		<h2>Show weather forecast for</h2>
                <div class="form-group row">
                    <label for="city" class="col-sm-2 col-form-label">City</label>
                    <select class="form-control col-sm-10" name="city" placeholder="City" id="city">
                        <option class="form-control" selected="selected" value="">Select city</option>
                        <?php 
                        foreach ($cities as $name => $value) {
                            echo '<option value="'.$value.'">' . $name . '</option>'."\r\n";
                        }
                        ?>
                    </select>
                </div>               
                <div class="form-group row">
                    <label for="date" class="col-sm-2 col-form-label">Date and time</label>
                    <input type="datetime-local" name="date" id="date" class="form-control col-sm-10" placeholder="Date and time" autofocus>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"></div>
                    <button type="submit" class="btn btn-primary col-sm-10">Show weather forecast</button>
                </div>		
	    </form>
		<?php if ($result == "OK") { ?>
		<div class="alert alert-success">
                    <p>
                        Weather forecast for <?php echo htmlspecialchars($city)?> on <?php echo htmlspecialchars($date)?> 
                    </p>
		    <table class="table">
                        <?php 
                        foreach ($forecast as $name => $value) {
                            echo '<tr><td>'.$name.'</td><td>' . $value . '</td></tr>';
                        }                        
                        ?>
                    </table>
		</div>
		<?php } elseif ($result == "ERROR") {?>
		<div class="alert alert-warning">
		    <h3>Problem detected!</h3>
                    <p><?php echo htmlspecialchars($error_message)?></p>
		</div>
		<?php } ?>
		
                    
                </div>
            </div>
        </div> <!-- /container -->

    </body>
</html>
