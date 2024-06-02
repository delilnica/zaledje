<?php

include "util.php";

$zbirka = pridobi_zbirko();

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		uporabnik_prijava();
		break;

	default:
		http_response_code(405); // Method not allowed
		break;
}

function uporabnik_prijava()
{
	global $zbirka;
	$vhod = json_decode(file_get_contents("php://input"), true);

	if (!isset($vhod["vzdevek"], $vhod["geslo"])) {
		http_response_code(400); // Bad Request
		json_odgovor("Ključna polja manjkajo.", __LINE__);
		return -1;
	}

	$vzdevek = mysqli_escape_string($zbirka, $vhod["vzdevek"]);
	$geslo = $vhod["geslo"];

	$poizvedba = "SELECT * FROM uporabnik WHERE vzdevek = '$vzdevek'";
	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Uporabnik ne obstaja", __LINE__);
		return -1;
	}

	$odgovor = mysqli_fetch_assoc($rez);

	if (!password_verify($vhod["geslo"], $odgovor["geslo"])) {
		http_response_code(401);
		json_odgovor("Geslo ni pravilno", __LINE__);
		return -1;
	}

	$id = $odgovor["id"];
	$zeton = jwt_generiraj($id);

	$validiraj = jwt_validiraj($zeton);
	if ($validiraj == -1) {
		http_response_code(401);
		json_odgovor("Žeton ni veljaven", __LINE__);
		return -1;
	}

	http_response_code(200);
	json_odgovor($zeton);
}



?>
