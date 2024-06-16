# Pregled končnih točk

Opomba: funkcije v oklepajih so navedene le kot sklic na programsko kodo in za odjemalce niso pomembne.

* `/login.php`, **POST** (`uporabnik_prijava()`), s parametroma:

  - `vzdevek` (niz): uporabnikov vzdevek/ime
  - `geslo` (niz): uporabnikovo geslo

  Prijavi (avtorizira) uporabnika in vrne JWT žeton za tekočo sejo.

  Možni odzivi:

  - 200: prijava uspešna, priložena je vrednost žetona
  - 400: manjkajoča polja
  - 401: napačno geslo
  - 401: neveljaven žeton
  - 404: uporabnik ne obstaja


* `/register.php`, **POST** (`uporabnik_dodaj()`), s parametri:

  - `vzdevek` (niz): enolični vzdevek
  - `enaslov` (niz): e-poštni naslov
  - `geslo` (niz): geslo v čistopisu

  Doda novega uporabnika.

  Možni odzivi:

  - 201: registracija uspešna
  - 400: manjkajoča polja
  - 500: uporabnik že obstaja/notranja napaka


* `/fragment.php`, **GET** (`fragment_seznam_vseh()`), brez parametrov.

  Če uporabnik *ni* prijavljen, izpiše vsebino in metapodatke vseh javno-dostopnih
  fragmentov.

  Če uporabnik *je* prijavljen, izpiše tudi zasebne.

  Možna odziva:

  - 200: izpis uspešen
  - 404: fragmentov ni


* `/fragment.php`, **GET** (`fragment_iz_oznake()`), s parametrom:

  - `o` (niz): oznaka fragmenta

  Pridobi vsebino in metapodatke zahtevanega fragmenta. Če uporabnik *ni* prijavljen,
  so dostopni le javni fragmenti, sicer vsi.

  Možna odziva:

  - 200: poizvedba uspešna, priložen je seznam
  - 404: fragmentov ni


* `/fragment.php`, **POST**, lahko:

  + Doda nov fragment (`fragment_dodaj()`):

    Pričakovana parametra:

    - `ime` (niz): ime fragmenta
    - `besedilo` (niz): besedilo fragmenta

    Opcijska parametra:

    - `zaseben` (logična vred.): zasebnost fragmenta (glej opis)
    - `datoteka` (niz): vsebina datoteke, kodirana v zapisu _base64_

    Če je uporabnik prijavljen, je lahko zaseben, sicer vselej javen.

    Možni odzivi:

    - 201: fragment dodan
    - 400: manjkajoča, ničelna ali predolga polja (slednje za neprijavljene)
    - 500: notranja napaka

  + Odstrani obstoječ fragment (`fragment_izbrisi()`):

    Pričakovana parametra:

    - `izbris`, nastavljen na katerokoli vrednost (npr. _true_)
    - `uid` (število): ID fragmenta, *ne njegova oznaka*


* `/admin.php`, **GET** (`uporabnik_seznam_vseh()`), brez parametrov.

  Pridobi metapodatke in število vseh uporabnikov. Potrebna je prijava.

  Možni odzivi:

  - 200: poizvedba uspešna
  - 401: avtorizacija neuspešna
  - 404: uporabnikov ni


* `/admin.php`, **GET** (`uporabnik_iz_id()`), s parametrom:

  - `id` (število): ID uporabnika

  Pridobi metapodatke zahtevanega uporabnika. Potrebna je prijava.

  Možni odzivi:

  - 200: poizvedba uspešna
  - 401: administratorska avtorizacija neuspešna
  - 404: uporabnikov ni


* `/admin.php`, **POST**, lahko, če je uporabnik prijavljen in ima administratorske pravice:

  + Uredi obstoječega uporabnika (`uporabnik_posodobi()`):

    Pričakovan parameter:

    - `uid` (število): ID uporabnika

    Opcijski parameteri so vsi tisti, ki so našteti pri registraciji, in:

    - `admin` (logična vred.): nastavi uporabnika kot administratorja

    Potreben je vsaj en opcijski parameter.

    Možni odzivi:

    - 204: posodobitev uspešna
    - 400: manjka ali `uid` ali kateri od opcijskih parametrov
    - 401: administratorska avtorizacija neuspešna
    - 500: notranja napaka pri posodobitvi

  + Izbriše obstoječega uporabnika (`uporabnik_izbrisi()`):

    Pričakovana parametra:

    - `izbris`, nastavljen na katerokoli vrednost (npr. _true_)
    - `uid` (število): ID uporabnika

    Možni odzivi:

    - 204: izbris uspešen
    - 401: administratorska avtorizacija neuspešna
    - 500: notranja napaka pri posodobitvi
