<?php

include "util.php";

$zbirka = pridobi_zbirko();

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET':
		if (isset($_GET["id"])) {
			uporabnik_iz_id($_GET["id"]);
		} elseif (isset($_GET["ident"])) {
			identificiraj();
		} else {
			uporabnik_seznam_vseh();
		}
		break;

	case 'POST':
		uporabnik();
		break;

	case "OPTIONS":
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: Authorization');
		break;

	default:
		http_response_code(405); // Method not allowed
		break;
}


/*!
 * @return 0 ob uspehu, -1 ob napaki
 */
function uporabnik_seznam_vseh()
{
	global $zbirka;

	$id = idu();
	if ($id == 0) {
		http_response_code(401);
		json_odgovor("Niste avtorizirani", __LINE__);
		return -1;
	} /*else {
		return -1; TODO
	}*/

	$poizvedba = "SELECT id, vzdevek, enaslov, datum_reg, admin FROM uporabnik;";
	$rez = mysqli_query($zbirka, $poizvedba);
	$n_vrstic = mysqli_num_rows($rez);

	if ($n_vrstic < 1) {
		http_response_code(404);
		json_odgovor("Uporabnikov ni.", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor[] = $v;
	}

	http_response_code(200);
	json_odgovor($odgovor, $n_vrstic);

	return 0;
}

/*!
 * @param $id ID uporabnika
 * @return 0 ob uspehu, -1 ob napaki
 */
function uporabnik_iz_id($id)
{
	global $zbirka;

	$id = idu();
	if ($id == 0) {
		http_response_code(401);
		json_odgovor("Niste avtorizirani", __LINE__);
		return -1;
	} else {
		return -1;
	}

	$id = mysqli_escape_string($zbirka, $id);
	$poizvedba = "SELECT id, vzdevek, enaslov, datum_reg, admin FROM uporabnik WHERE id = '$id';";
	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Uporabnik ne obstaja.", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor = $v;
	}

	http_response_code(200);
	json_odgovor($odgovor);

	return 0;
}

function uporabnik()
{
	$vhod = json_decode(file_get_contents("php://input"), true);

	if (isset($vhod["izbris"], $vhod["id"])) {
		uporabnik_izbrisi($vhod["id"]);
	} else {
		uporabnik_posodobi($vhod);
	}
}

/*!
 * @param POST vzdevek, enaslov, geslo, admin
 * @return 0 ob uspehu, -1 ob napaki
 */
function uporabnik_posodobi()
{
	global $zbirka;
	$vhod = json_decode(file_get_contents("php://input"), true);

	if (!je_admin()) {
		http_response_code(401);
		json_odgovor("Potrebujete administratorske pravice", __LINE__);
		return -1;
	}

	if (!isset($vhod["id"])) {
		http_response_code(400); // Bad Request
		json_odgovor("Manjka ID uporabnika.", __LINE__);
		return -1;
	}

	if (!isset($vhod["vzdevek"]) &&
		!isset($vhod["enaslov"]) &&
		!isset($vhod["geslo"])   &&
		!isset($vhod["admin"])) {
		http_response_code(400); // Bad Request
		json_odgovor("Vsaj ena lastnost mora biti prisotna.", __LINE__);
		return -1;
	}

	$id = mysqli_escape_string($zbirka, $vhod["id"]);
	$poizvedba = "SELECT id, vzdevek, enaslov, geslo, admin FROM uporabnik WHERE id = '$id';";
	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Uporabnik ne obstaja.", __LINE__);
		return -1;
	}

	$odgovor = mysqli_fetch_assoc($rez);

	if (isset($vhod["vzdevek"])) {
		$vzdevek = mysqli_escape_string($zbirka, $vhod["vzdevek"]);
	} else {
		$vzdevek = $odgovor["vzdevek"];
	}

	if (isset($vhod["enaslov"])) {
		$enaslov = mysqli_escape_string($zbirka, $vhod["enaslov"]);
	} else {
		$enaslov = $odgovor["enaslov"];
	}

	if (isset($vhod["geslo"])) {
		$geslo = password_hash($vhod["geslo"], PASSWORD_BCRYPT);
	} else {
		$geslo = $odgovor["geslo"];
	}

	if (isset($vhod["admin"])) {
		$admin = ($odgovor["admin"] == 1) ? 0 : 1;
	} else {
		$admin = $odgovor["admin"];
	}

	$poizvedba = "UPDATE uporabnik SET vzdevek = '$vzdevek', enaslov = '$enaslov', geslo = '$geslo', admin = '$admin' WHERE id = '$id';";
	if (!mysqli_query($zbirka, $poizvedba)) {
		http_response_code(500); // Ampak, ni nujno strežniška okvara
		json_odgovor("Napaka pri posodobitvi: " . mysqli_error($zbirka), __LINE__);
		return -1;
	}

	http_response_code(204); // OK With No Content
	header('Access-Control-Allow-Origin: *');
	return 0;
}

function uporabnik_izbrisi($id)
{
	global $zbirka;

	if (!je_admin()) {
		http_response_code(401);
		json_odgovor("Potrebujete administratorske pravice", __LINE__);
		return -1;
	}

	$id = mysqli_escape_string($zbirka, $id);
	$poizvedba = "DELETE FROM uporabnik WHERE id = '$id';";
	$rez = mysqli_query($zbirka, $poizvedba);

	http_response_code(204); // OK With No Content
	header('Access-Control-Allow-Origin: *');
	return 0;
}


?>
