# Zaledje za Delilnico

Aplikacijski vmesnik, izdelan v ogrodju FastAPI, ki streže ospredju in komunicira z bazo.

**URL**: <http://nuks.bertoncelj.eu.org:8000/>{ukaz}.


## Končne točke

- `/add` (POST) - dodaj nov fragment

  Zahtevani parametri:

  * `title` - naslov
  * `author` - pisec
  * `text` - besedilna vsebina
  * `is_private` - logična vrednost, ki naznanja zasebnost fragmenta

  Vrnjene vrednosti:

  * `success` - logična vrednost za uspešnost operacije
  * `fid` če `success == True` - enolična oznaka fragmenta (šestznakovni besedilni niz)


- `/fragment/{id}` (GET) - pridobi obstoječ fragment

  Zahtevan parameter:

  * `fid` - enolična oznaka fragmenta, pridobljena po dodajanju (besedilni niz)

  Vrnjene vrednosti:

  * `success` - logična vrednost za uspešnost operacije
  * izvod razreda `Fragment` če `success == True` - razred z vsebino fragmenta
  * `reason` če `success == False` - niz z razlago napake


- `/author/{author}` (GET) - pridobi vse javne fragmente podanega pisca

  Zahtevan parameter:

  * `author` - ime pisca

  Vrnjene vrednosti:
  * `success` - logična vrednost za uspešnost operacije
  * izvodi razreda `Fragment` če `success == True` za vsak piščev fragment
  * `reason` če `success == False` - niz z razlago napake


- `/all_fragments` (GET) - pridobi vse javne fragmente

  Vrnjene vrednosti:

  * `success` - logična vrednost za uspešnost operacije
  * izvodi razreda `Fragment` če `success == True` za vsak fragment
  * `reason` če `success == False` - niz z razlago napake


## Razred `fragment`

```python
class Fragment(BaseModel):
    """
    Podatki, javno izpostavljeni prek API-ja.
    """
    title: str
    author: str
    text: str
    is_private: bool
```

## Pogon

```
$ python3 -m venv venv
$ . venv/bin/activate
$ pip3 install -r requirements.txt

$ uvicorn --reload --host 0.0.0.0 main:app
```

## Dobitek enolične oznake fragmenta

Za oznako `fid` to zaledje uporablja zunanjo storitev (`delilnica-idgen`). Klic nje brez dodatnih
parametrov vrne enoličen, šestznakoven besedilni niz (primer: `tdkm9k`).
