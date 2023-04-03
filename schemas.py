from pydantic import BaseModel
from typing import Annotated
from fastapi import Form

class Fragment(BaseModel):
    """
    Podatki, javno izpostavljeni prek API-ja.
    """
    title: Annotated[str, Form()]
    author: Annotated[str, Form()]
    text: Annotated[str, Form()]
    is_private: Annotated[bool, Form()]

# class Fragment(BaseModel):
#     """
#     Podatki, javno izpostavljeni prek API-ja.
#     """
#     title: str
#     author: str
#     text: str
#     is_private: bool
