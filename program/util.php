<?php

// Prepreči neposreden zagon datoteke
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
	exit("");
}

// JWT (žetoni)
$zasebni_kljuc = "zelovarnogeslonegakopirat-zelovarnogeslonegakopirat";

require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\Key;

/*!
 * Povezava v podatkovno zbirko. Program se zaustavi, če spodleti.
 *
 * @return MySQL objekt
 */
function pridobi_zbirko()
{
	$mysql = mysqli_connect("mysql", "delilnica", "FolijantPasivizem16", "delilnica");
	// $mysql = mysqli_connect("mysql", "root", "korenskogeslo", "delilnica");

	if (mysqli_connect_errno()){
		echo "Povezava v zbirko je spodletela: " . mysqli_connect_error();
		exit();
	}

	return $mysql;
}

/*!
 * Pripravi odgovor v formatu JSON
 *
 * @param $vsebina Poljuben izhodni podatek
 * @param $koda Opcijska vrnitvena cifra (privzeta 0)
 */
function json_odgovor($vsebina, $koda=0)
{
	header('Content-Type: application/json');
	$odgovor = [
		"status"   => $koda,
		"response" => $vsebina
	];
	echo json_encode($odgovor);
}

/*!
 * Tvori JWT žeton z omejenim trajanjem.
 *
 * @param $uid Uporabnikov ID
 * @return Besedilni niz JWT žetona
 */
function jwt_generiraj($uid)
{
	global $zasebni_kljuc;

	$trenutek = time();
	$potek = $trenutek + 60 * 30; // Čas poteka v sekundah

	$jwt_paket = [
		"iat" => $trenutek, // Issued At
		"exp" => $potek,    // Expiry Date
		"sub" => $uid       // Subject - ID
	];

	return JWT::encode($jwt_paket, $zasebni_kljuc, "HS256");
}

/*!
 * Validiraj JWT žeton
 *
 * @param $zeton Besedilni niz JWT žetona
 * @return Dekodirana struktura prvotnega žetona
 */
function jwt_validiraj($zeton)
{
	global $zasebni_kljuc;

	http_response_code(401);
	try {
		$jwt_val = (array) JWT::decode($zeton, new Key($zasebni_kljuc, "HS256"));
		http_response_code(200);
		return $jwt_val;

	} catch (ExpiredException $e) {
		json_odgovor("Žeton je potekel", 10);

	} catch (SignatureInvalidException $e) {
		json_odgovor("Podpis žetona ni veljaven", 11);

	} catch (BeforeValidException $e) {
		json_odgovor("Žeton še ni veljaven", 12);

	} catch (Exception $e) {
		json_odgovor("Žeton ni veljaven", 13);
	}

	return -1;
}

/*!
 * @return -1 ob napaki, ID uporabnika sicer.
 */
// function je_avtoriziran()
// {
	// $headers = apache_request_headers();
 //
	// if (isset($headers["Authorization"])) {
	// 	$validiraj = jwt_validiraj($headers["Authorization"]);
 //
	// 	if ($validiraj == -1) {
	// 		exit();
	// 	}
 //
	// 	return $validiraj["sub"];
	// }
 //
	// return false;


	// $id = idu();

	// return ($id > 0);
// }

/*!
 * Pridobi ID uporabnika
 * @return -1, če je napaka z žetonom, 0, če ni prijavljen, >0 (id), če je.
 */
function idu()
{
	$headers = apache_request_headers();

	if (!isset($headers["Authorization"]))
		return 0;

	$validiraj = jwt_validiraj($headers["Authorization"]);

	if ($validiraj == -1)
		return -1;

	return $validiraj["sub"];
}

/*!
 * @return 1, če je uporabnik administrator, sicer 0
 */
function je_admin()
{
	global $zbirka;

	$id = idu();

	if ($id < 0)
		exit($id);
	elseif ($id == 0)
		return 0;

	$poizvedba = "SELECT admin FROM uporabnik WHERE id = '$id';";
	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Uporabnik '$id' ne obstaja.", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor = $v;
	}

	return $odgovor["admin"];
}

function identificiraj()
{
	global $zbirka;

	http_response_code(200);
	$id = idu();

	if ($id < 0) {
		return $id;
	} elseif ($id == 0) {
		json_odgovor("Uporabnik ni avtenticiran");
		return;
	}

	$poizvedba = "SELECT vzdevek FROM uporabnik WHERE id = '$id';";
	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Uporabnik '$id' ne obstaja.", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor = $v;
	}

	json_odgovor($odgovor["vzdevek"], $id);
}

?>
