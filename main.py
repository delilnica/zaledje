# Zaledje za Delilnico
# NB (NUKS) 7. 3. 2023

# https://docs.sqlalchemy.org/en/20/orm/session_basics.html#querying
# https://docs.sqlalchemy.org/en/20/tutorial/data_select.html#the-where-clause

# FastAPI + zunanja razširitev za različice
from fastapi import FastAPI, Response, Request, HTTPException, Form, Depends
from fastapi.staticfiles import StaticFiles
from fastapi.responses import HTMLResponse
from fastapi.templating import Jinja2Templates
from fastapi_versioning import version, VersionedFastAPI
from pydantic import BaseModel

# Podatkovna baza
# from typing import Union
from db import engine, Base, Fragment
from sqlalchemy.orm import Session
from sqlalchemy import select
Base.metadata.create_all(engine)

# Javno izpostavljeni parametri APIja iz schemas.py
import schemas

app = FastAPI()
templates = Jinja2Templates(directory="static")

@app.get("/")
@version(1)
def home_page(request: Request):
    """Domača stran Delilnice"""
    return templates.TemplateResponse("index.html", {"request": request})

class AddForm:
    def __init__(self, title: str = Form(...), author: str = Form(...), text: str = Form(...), is_private: str = Form(False)):
        self.title = title
        self.author = author
        self.text = text
        self.is_private = is_private

# @app.post("/add", status_code=201, response_class=HTMLResponse)
@app.post("/add", status_code=201)
@version(1)
# def add_fragment(frag: schemas.Fragment, request: Request, response: Response):
# def add_fragment(add_form: AddForm = Depends()):
def add_fragment(request: Request, response: Response, add_form: AddForm = Depends()):
    """Dodaj nov fragment"""

    client_host = request.client.host
    print(add_form.is_private)
    #session = Session(bind=engine, expire_on_commit=False)
    with Session(engine) as session:
        fragment = Fragment(
            ip_addr=str(client_host),
            expiry_date="-",
            title=add_form.title,
            author=add_form.author,
            text=add_form.text,
            is_private=(add_form.is_private == "on")
        )

        session.add(fragment)
        session.commit()
        print(f"USPEH - Vnašam fragment, oznaka {str(fragment.id)}")

    return {"success": True, "id": fragment.id}

@app.get("/fragment/{id}")
@version(1)
def retrieve_fragment(id: int, request: Request, response: Response):
    """Pridobi fragment po podanem ID-ju"""

    frag_list = []
    # frag = None

    statement = select(Fragment).where(Fragment.id==id)
    with Session(engine) as session:
        for frag in session.scalars(statement):
            frag_list.append(frag)
            # frag = session.scalars(statement)

    if len(frag_list) < 1:
    # if frag == None:
        response.status_code = 404
        # TODO tudi za is_private

        # raise HTTPException(status_code=404, detail=f"Fragment {id} ne obstaja.")

        print("NAPAKA - fragment ne obstaja")
        retval = {"success": False, "reason": "Ta fragment ne obstaja."}
    else:
        print(f"USPEH - {frag_list}")
        retval = {"success": True, "fragment": frag_list[0]}

    return templates.TemplateResponse("retrieve_fragment.html", {"request": request, "retval": retval})

@app.get("/author/{author}")
@version(1)
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
        print("NAPAKA - avtor nima fragmentov")
        return {"success": False, "reason": "Ta avtor nima lastnih fragmentov."}

    print(f"USPEH - {frag_list}")
    return {"success": True, "fragments": frag_list}

# https://stackoverflow.com/questions/31624530/return-sqlalchemy-results-as-dicts-instead-of-lists
@app.get("/all_fragments")
@version(1)
def retrieve_all_fragments(only_meta: bool=False):
    """Pridobi vse javne fragmente"""

    frag_list = []
    if not only_meta:
        statement = select(Fragment).where(Fragment.is_private==False)
    else:
        #statement = select(Fragment).where(Fragment.is_private==False).filter(Fragment.author=="nejc")
        statement = select(Fragment.id, Fragment.title, Fragment.author, Fragment.ip_addr).where(Fragment.is_private==False)
    with Session(engine) as session:
        for frag in session.execute(statement):
            frag_list.append(frag._asdict())

    if len(frag_list) < 1:
        print("NAPAKA - v zbirki ni nobenih fragmentov")
        return {"success": False, "reason": "V zbirki še ni fragmentov."}

    print(f"USPEH - {frag_list}")
    return {"success": True, "fragments": frag_list}

# @app.delete("/delete/{id}")
#@version(1)
# def delete_fragment(id: int):
#     """Izbriši fragment"""
#
#     return success

app = VersionedFastAPI(app, version_format="{major}", prefix_format="/v{major}")
# app.mount("/", StaticFiles(directory="static", html=True), name="static")
app.mount("/static", StaticFiles(directory="static"), name="static")
