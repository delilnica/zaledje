# Kontejner za zaledje Delilnice
# docker build -t delilnica/zaledje .
# docker run -v "$PWD/baza:/delilnica/baza" -it -p 8000:8000 --rm delilnica/zaledje

FROM python:3.10

RUN mkdir -p /delilnica/baza
WORKDIR /delilnica

COPY requirements.txt .
RUN pip3 install --no-cache-dir -r requirements.txt

COPY *.py ./

EXPOSE 8000
CMD ["uvicorn", "--host", "0.0.0.0", "main:app"]
