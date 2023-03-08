from pydantic import BaseModel

class Fragment(BaseModel):
    """
    Podatki, javno izpostavljeni prek API-ja.
    """
    title: str
    author: str
    text: str
    is_private: bool
