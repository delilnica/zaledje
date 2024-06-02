<?php

include "util.php";

$zbirka = pridobi_zbirko();

switch ($_SERVER["REQUEST_METHOD"]) {
	// case 'GET':

	case 'POST':
		uporabnik_dodaj();
		break;

	default:
		http_response_code(405); // Method not allowed
		break;
}

/*!
 * @param POST vzdevek, enaslov, geslo
 * @return 0 ob uspehu, -1 ob napaki
 */
function uporabnik_dodaj()
{
	global $zbirka;
	$vhod = json_decode(file_get_contents("php://input"), true);

	if (!isset($vhod["vzdevek"], $vhod["enaslov"], $vhod["geslo"])) {
		http_response_code(400); // Bad Request
		json_odgovor("Ključna polja manjkajo.", 3);
		return -1;
	}

	// Sanitiziraj vhod, zgosti geslo
	$vzdevek = mysqli_escape_string($zbirka, $vhod["vzdevek"]);
	$enaslov = mysqli_escape_string($zbirka, $vhod["enaslov"]);
	$geslo = password_hash(mysqli_escape_string($zbirka, $vhod["geslo"]), PASSWORD_BCRYPT);

	// Preveri, če je želen vzdevek že vpisan v zbirki
	$poizvedba = "SELECT id FROM uporabnik WHERE vzdevek = '$vzdevek'";
	$rez = mysqli_query($zbirka, $poizvedba);
	$n_vrstic = mysqli_num_rows($rez);

	if ($n_vrstic > 0) {
		// http_response_code(404);
		json_odgovor("Uporabnik s tem vzdevkom že obstaja.", 32);
		return -1;
	}

	// Dodaj novega uporabnika
	$poizvedba = "INSERT INTO uporabnik (vzdevek, enaslov, geslo) VALUES ('$vzdevek', '$enaslov', '$geslo')";

	if (!mysqli_query($zbirka, $poizvedba)) {
		http_response_code(500);
		json_odgovor("Kriterij vseh potrebnih vnosov ni zadoščen", 33);
		return -1;
	}

	http_response_code(201); // Created
	return 0; // TODO ID uporabnika?
}
