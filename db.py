from sqlalchemy import create_engine

from sqlalchemy.orm import DeclarativeBase
from sqlalchemy.orm import Mapped
from sqlalchemy.orm import mapped_column

from sqlalchemy import Column, Integer, String, DateTime, Boolean
from sqlalchemy.ext.declarative import declarative_base

engine = create_engine("sqlite+pysqlite:///delilnica.db", echo=False)
Base = declarative_base()

class Fragment(Base):
    """
    Struktura tabele fragmentov.
    https://docs.sqlalchemy.org/en/20/tutorial/metadata.html#declaring-mapped-classes
    """

    __tablename__ = "fragments"

    # Metapodatki
    id          = Column(Integer, primary_key=True)
    fid         = Column(String)
    ip_addr     = Column(String)
    expiry_date = Column(String) #DateTime

    # Uporabniška vsebina
    title      = Column(String)
    author     = Column(String)
    text       = Column(String)
    is_private = Column(Boolean)

    def __repr__(self):
        return f"Fragment '{self.title}' avtorja '{self.author}'"

