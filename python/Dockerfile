FROM python:${PYTHON_VERSION:+${PYTHON_VERSION}-}alpine
RUN pip3 install --upgrade pip
RUN pip3 install mysql-connector-python
COPY . /app
WORKDIR /app
ENTRYPOINT ["python"]
CMD ["ingest.py"]