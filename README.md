# Projekt Delilnica - zaledje

Zaledje Delilnice sestavljajo trije deli:

- Spletni strežnik Apache 2.4 s PHP 8.2
- MySQL podatkovna zbirka, vezana na spletni strežnik
- Orodje phpMyAdmin, vezano na pod. zbirko

Vsi delujejo v Dockerju, njihova postavitev pa je samodejna.


## Pregled končnih točk

Vsi odgovori so v obliki JSON-kodiranega niza s sledečo strukturo:

```json
{
    "response": "<niz z odgovorom ali nova podstruktura>",
    "status": 0
}
```

Razen če je navedeno drugače, je odgovor zapisan kot besedilni niz v
polju `response`.

Vsem zahtevkom je postavljena HTTP koda glede na njihov uspeh.

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


* `/fragment.php`, **POST** (`fragment_dodaj()`).

  Pričakovana parametra:

  - `ime` (niz): ime fragmenta
  - `besedilo` (niz): besedilo fragmenta

  Opcijski parameter:

  - `zaseben` (logična vred.): zasebnost fragmenta (glej opis)

  Doda nov fragment. Če je uporabnik prijavljen, je lahko zaseben, sicer vselej javen.
  (TODO nalaganje datotek)

  Možni odzivi:

  - 201: fragment dodan
  - 400: manjkajoča, ničelna ali predolga polja (slednje za neprijavljene)
  - 500: notranja napaka


* `/admin.php`, **GET** (`uporabnik_seznam_vseh()`), brez parametrov.

  Pridobi metapodatke in število vseh uporabnikov. Potrebna je prijava.


* `/admin.php`, **GET** (`uporabnik_iz_id()`), s parametrom:

  - `id` (število): ID uporabnika

  Pridobi metapodatke zahtevanega uporabnika. Potrebna je prijava.


* `/admin.php`, **POST**, lahko, če je uporabnik prijavljen in ima administratorske pravice:

  + Uredi obstoječega uporabnika (`uporabnik_dodaj()`):

    Pričakovan parameter:

    - `uid` (število): ID uporabnika

    Opcijski parameteri so vsi tisti, ki so našteti pri registraciji, in:

    - `admin` (logična vred.): nastavi uporabnika kot administratorja

    Potreben je vsaj en opcijski parameter.


## Vzpostavitev sistema

Za zagon se uporabi `docker compose up [-d]`, za izklop *Ctrl-C* oz. `docker compose down`.
Podrobnosti se najdejo v datoteki `docker-compose.yml`.

S privzetimi nastavitvami postaneta dostopna sledeča naslova:

- <http://localhost:81> (httpd)
- <http://localhost:82> (PMA)
