# Projekt Delilnica - zaledje

Zaledje Delilnice sestavljajo trije deli:

- Spletni strežnik Apache 2.4 s PHP 8.2
- MySQL podatkovna zbirka, vezana na spletni strežnik
- Orodje phpMyAdmin, vezano na pod. zbirko

Vsi delujejo v Dockerju, njihova postavitev pa je samodejna.


## Komunikacija

Prejemanje in pošiljanje podatkov poteka s HTTP zahtevki.

Vsi odgovori zaledja so v obliki JSON-kodiranega niza s sledečo strukturo:

```json
{
    "response": "<niz z odgovorom ali nova podstruktura>",
    "status": 0
}
```

Razen če je navedeno drugače, je odgovor zapisan kot besedilni niz v
polju `response`.

Vsem zahtevkom je postavljena HTTP koda glede na njihov uspeh. Posamezne končne točke in njihovi parametri so podrobno opisani na [samostojni strani](API.md).

## Vzpostavitev sistema

Za zagon se uporabi `docker compose up [-d]`, za izklop *Ctrl-C* oz. `docker compose down`.
Podrobnosti se najdejo v datoteki `docker-compose.yml`.

S privzetimi nastavitvami postaneta dostopna sledeča naslova:

- <http://localhost:81> (httpd)
- <http://localhost:82> (PMA)
