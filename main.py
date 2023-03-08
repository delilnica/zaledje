# Zaledje za Delilnico
# NB (NUKS) 7. 3. 2023

from typing import Union
from fastapi import FastAPI, Response, Request
from db import engine, Base, Fragment
from sqlalchemy.orm import Session
from sqlalchemy import select
import schemas

Base.metadata.create_all(engine)
app = FastAPI()

@app.get("/")
def read_root():
    """Privzeta vhodna točka"""

    #return {"sporočilo": "Pozdrav."}
    return "Domača stran Delilnice"

@app.post("/add")
def add_fragment(frag: schemas.Fragment, request: Request):
    """Dodaj nov fragment"""

    client_host = request.client.host
    #session = Session(bind=engine, expire_on_commit=False)
    with Session(engine) as session:
        fragment = Fragment(
            ip_addr=str(client_host),
            expiry_date="-",
            title=frag.title,
            author=frag.author,
            text=frag.text,
            is_private=False
        )

        session.add(fragment)
        session.commit()
        print("Dodan fragment " + str(fragment.id))

    return f"Vnašam fragment, oznaka {str(fragment.id)}"

@app.get("/fragment/{id}")
def retrieve_fragment(id: int, response: Response):
    """Pridobi fragment po podanem ID-ju"""

    frag_list = []

    statement = select(Fragment).where(Fragment.id==id)
    with Session(engine) as session:
        for frag in session.scalars(statement):
            frag_list.append(frag)

    if len(frag_list) < 1:
        response.status_code = 404
        # TODO tudi za is_private
        return "Ta fragment ne obstaja"

    return frag_list

@app.get("/author/{author}")
def retrieve_fragments_from_author(author: str, response: Response):
    """Pridobi vse fragmente želenega avtorja"""

    frag_list = []

    statement = (select(Fragment)
        .where(Fragment.is_private==False)
        .where(Fragment.author==author))
    with Session(engine) as session:
        for frag in session.scalars(statement):
            frag_list.append(frag)

    if len(frag_list) < 1:
        response.status_code = 404
        return "Ta avtor nima lastnih fragmentov"

    return frag_list

@app.get("/all_fragments")
def retrieve_all_fragments():
    """Pridobi vse javne fragmente"""

    frag_list = []
    statement = select(Fragment).where(Fragment.is_private==False)
    with Session(engine) as session:
        for frag in session.scalars(statement):
            frag_list.append(frag)

    if len(frag_list) < 1:
        return "Fragmentov še ni..."

    return frag_list

# @app.delete("/delete/{id}")
# def delete_fragment(id: int):
#     """Izbriši fragment"""
#
#     return success


