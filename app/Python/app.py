import sys
import pytesseract
import os
from pdf2image import pdfinfo_from_path, convert_from_path
import spacy

class processAttachent:

    res = ""
    pdf = '/var/www/html/storage/app/public/'+sys.argv[1]
    pdf_info = pdfinfo_from_path(pdf, userpw=None, poppler_path=None)
    nlp = spacy.load("en_core_web_sm")
    max_pages = 15

    for page in range(1, max_pages + 1, 5):
        converted = convert_from_path(pdf, first_page=page, last_page=min(page+5-1,max_pages))
        for image in converted:
            text = pytesseract.image_to_string(image)
            res += text.replace("\n", "")

    doc = nlp(res)
    print([chunk.text for chunk in doc.noun_chunks])
