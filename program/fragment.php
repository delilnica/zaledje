<?php

include "util.php";

$zbirka = pridobi_zbirko();

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET':
		if (empty($_GET["o"])) {
			// http_response_code(400); // ‘Bad Request’
			fragment_seznam_vseh();
		} else {
			fragment_iz_oznake($_GET["o"]);
		}
		break;

	case 'POST':
		header('Access-Control-Allow-Origin: *');
		fragment();
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
 * @param $o alfanumerična oznaka fragmenta
 * @return 0 ob uspehu, -1 ob napaki
 */
function fragment_iz_oznake($o)
{
	global $zbirka;

	$oznaka = mysqli_escape_string($zbirka, $o);

	$uid = idu();
	if ($uid > 0) {
		$poizvedba = "SELECT id, uid, datum, ime, besedilo, did FROM fragment WHERE oznaka = '$oznaka';";
	} elseif ($uid == 0) {
		$poizvedba = "SELECT id, uid, datum, ime, besedilo, did FROM fragment WHERE je_zaseben = 0 AND oznaka = '$oznaka';";
	} else {
		return -1;
	}

	$rez = mysqli_query($zbirka, $poizvedba);

	if (mysqli_num_rows($rez) < 1) {
		http_response_code(404);
		json_odgovor("Fragment ne obstaja", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor = $v;
	}

	if ($odgovor["did"] != NULL) {
		$did = $odgovor["did"];
		$poizvedba_dat = "SELECT uri FROM datoteka WHERE id = '$did'";
		$rez = mysqli_query($zbirka, $poizvedba_dat);
		if ($rez) {
			while ($v = mysqli_fetch_assoc($rez)) {
				$odgovor += $v;
			}
		}
	}

	http_response_code(200);
	json_odgovor($odgovor);

	return 0;
}

/*!
 * @return 0 ob uspehu, -1 ob napaki
 */
function fragment_seznam_vseh()
{
	global $zbirka;
	$odgovor = [];

	// $poizvedba = "SELECT id, oznaka, uid, datum, ime, besedilo, did FROM fragment WHERE je_zaseben = 0;";

	$uid = idu();
	if ($uid > 0) {
		$poizvedba = "SELECT * FROM fragment;";
	} elseif ($uid == 0) {
		$poizvedba = "SELECT * FROM fragment WHERE je_zaseben = 0;";
	} else {
		return $uid;
	}

	$rez = mysqli_query($zbirka, $poizvedba);
	$n_vrstic = mysqli_num_rows($rez);

	if ($n_vrstic < 1) {
		http_response_code(404);
		json_odgovor("Fragmentov ni.", __LINE__);
		return -1;
	}

	while ($v = mysqli_fetch_assoc($rez)) {
		$odgovor[] = $v;
	}

	http_response_code(200);
	json_odgovor($odgovor, $n_vrstic);

	return 0;
}

function fragment()
{
	$vhod = json_decode(file_get_contents("php://input"), true);

	if (isset($vhod["izbris"], $vhod["id"])) {
		fragment_izbrisi($vhod["id"]);
	} else {
		fragment_dodaj($vhod);
	}
}

/*!
 * @param POST ime, besedilo, [zaseben]
 * @return 0 ob uspehu, -1 ob napaki
 */
function fragment_dodaj()
{
	global $zbirka;
	header('Access-Control-Allow-Origin: *');

	$vhod = json_decode(file_get_contents("php://input"), true);
	// var_dump($vhod); exit();

	if (!isset($vhod["ime"], $vhod["besedilo"])) {
		http_response_code(400); // Bad Request
		json_odgovor("Ključna polja manjkajo.", __LINE__);
		return -1;
	}

	$uid = idu();

	if ($uid < 0)
		return $uid; // Napaka pri avtentikaciji, prekini

	$oznaka = substr(md5(rand()), 0, 6); // 6-znakoven psevdonaključen niz
	// $ip = isset($vhod["ip"]) ? mysqli_escape_string($zbirka, $vhod["ip"]) : "-";
	$ip = $_SERVER['REMOTE_ADDR']; // Morda nezadostno, a tukaj zadošča
	$ime = mysqli_escape_string($zbirka, $vhod["ime"]);
	$besedilo = mysqli_escape_string($zbirka, $vhod["besedilo"]);
	$je_zaseben = 0;
	$did = 0;

	if (strlen($ime) < 1 || strlen($besedilo) < 1) {
		http_response_code(400); // Bad Request
		json_odgovor("Ničelna dolžina polj je neveljavna.", __LINE__);
		return -1;
	}

	if ($uid == 0 && (strlen($ime) > 40 || strlen($besedilo) > 2000)) {
		http_response_code(400); // Bad Request
		json_odgovor("Anonimni uporabniki imajo dolžinsko omejitev.", __LINE__);
		return -1;
	}

	if ($uid > 0) { // Uporabnik je prijavljen, dovoli zasebno objavo
		$je_zaseben = $vhod["zaseben"];
	}

	if (isset($vhod["datoteka"])) {
		$imenik = "/var/www/html/shramba/";
		// $dat = $imenik . basename($_FILES["datoteka"]["name"]);
		$bas = basename($oznaka);
		$dat = $imenik . $bas;
		// $vhod["datoteka"];

		if (file_exists($dat)) {
			json_odgovor("Datoteka že obstaja");
			return -1;
		}

		// error_log($dat);

		// if (move_uploaded_file($_FILES["datoteka"]["name"], $dat)) {
		// 	echo "Uspeh";
		// } else {
		// 	echo "Neuspeh";
		// }
		if (!file_put_contents($dat, file_get_contents($vhod["datoteka"]))) {;
			// error_log("napaka pri nalaganju");
			return -1;
		}
		// error_log("nalozeno");

		$poizvedba = "INSERT INTO datoteka (uid, uri) VALUES ('$uid', '$bas');";

		if (mysqli_query($zbirka, $poizvedba)) {
			$did = mysqli_insert_id($zbirka);
			// error_log("did: $did");
		} else {
			// error_log("ne gre");
			http_response_code(500);
			json_odgovor("Napaka pri 'did'.", __LINE__);
			return -1;
		}
		$poizvedba = "INSERT INTO fragment (oznaka, uid, ip, ime, besedilo, je_zaseben, did) VALUES ('$oznaka', '$uid', '$ip', '$ime', '$besedilo', '$je_zaseben', '$did')";
	} else {
		$poizvedba = "INSERT INTO fragment (oznaka, uid, ip, ime, besedilo, je_zaseben) VALUES ('$oznaka', '$uid', '$ip', '$ime', '$besedilo', '$je_zaseben')";
	}

	if (!mysqli_query($zbirka, $poizvedba)) {
		http_response_code(500);
		json_odgovor("Kriterij vseh potrebnih vnosov ni zadoščen", __LINE__);
		return -1;
	}

	http_response_code(201); // Created

	json_odgovor($oznaka);
	return 0;
}

function fragment_izbrisi($id)
{
	global $zbirka;

	if (!je_admin()) {
		http_response_code(401);
		json_odgovor("Potrebujete administratorske pravice", __LINE__);
		return -1;
	}

	$id = mysqli_escape_string($zbirka, $id);

	$poizvedba = "SELECT did FROM fragment WHERE id = '$id'";
	$rez = mysqli_query($zbirka, $poizvedba);
	if ($rez) {
		while ($v = mysqli_fetch_assoc($rez)) {
			$odgovor = $v;
		}

		if ($odgovor["did"] != NULL) {
			$did = $odgovor["did"];
			$poizvedba = "DELETE FROM datoteka WHERE id = '$did'";
			if (!mysqli_query($zbirka, $poizvedba)) {
				http_response_code(500);
				json_odgovor("Napaka pri brisanju datoteke: " . mysqli_error($zbirka), __LINE__);
				return -1;
			}
		}
	}

	$poizvedba = "DELETE FROM fragment WHERE id = '$id';";
	if (!mysqli_query($zbirka, $poizvedba)) {
		http_response_code(500);
		json_odgovor("Napaka pri brisanju fragmenta: " . mysqli_error($zbirka), __LINE__);
		return -1;
	}

	http_response_code(204); // OK With No Content
	return 0;
}

?>
