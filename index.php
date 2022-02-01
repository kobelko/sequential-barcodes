<?php
/**
 * @author Samuel Kobelkowsky
 * @copyright 2022 (c) Samuel Kobelkowsky
 * @version 0.1
 * @desc Generates a PDF with barcodes
 */
require_once 'inc/PdfGenerator.php';

// The form parameters
$from = isset($_REQUEST['inputFrom']) ? $_REQUEST['inputFrom'] : '';
$to = isset($_REQUEST['inputTo']) ? $_REQUEST['inputTo'] : '';

// This will be added to the input classes to show an error if needed
$from_error = '';
$to_error = '';

// The user sent the form
if (count($_REQUEST) > 0) {
    // Validate the parameters
    $from_error = ! is_numeric($from) || $from <= 0 ? "is-invalid" : "is-valid";
    $to_error = ! is_numeric($to) || $to <= $from ? "is-invalid" : "is-valid";

    // Main program
    if ($from_error == "is-valid" && $to_error == "is-valid") {
        try {
            $pdfGenerator = new PdfGenerator($from, $to);
            $pdfGenerator->Output('barras.pdf', 'I');
        } catch (Exception $ex) {
            if ($ex->getMessage() == "Too many pages") {
                $to_error = "is-invalid";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
	integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link href="css/style.css" rel="stylesheet">

<title>Generador de códigos de barras</title>
</head>
<body>

	<div class="container">
		<div class="row my-3">
			<div class="col">
				<h2>Generador de código de barras</h2>
			</div>
		</div>

		<form class="row g-3 needs-validation" novalidate>
			<div class="col-md-4">
				<label for="inputFrom" class="form-label">Desde</label> <input type="text"
					class="form-control <?php echo $from_error;?>" id="inputFrom" aria-describedby="helpFrom" name="inputFrom"
					value="<?php echo $from; ?>" required />
				<div id="helpFrom" class="form-text">Folio inicial</div>
				<div class="invalid-feedback">Número inválido</div>
			</div>
			<div class="col-md-4">
				<label for="inputTo" class="form-label">Hasta</label> <input type="text"
					class="form-control <?php echo $to_error;?>" id="inputTo" aria-describedby="helpTo" name="inputTo"
					value="<?php echo $to;?>" required />
				<div id="helpTo" class="form-text">Folio final</div>
				<div class="invalid-feedback">Número inválido</div>
			</div>
			<div class="col-12">
				<button type="submit" class="btn btn-primary">Generar</button>
			</div>
		</form>
	</div>

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
		integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
	<script src="js/scripts.js"></script>
</body>
</html>