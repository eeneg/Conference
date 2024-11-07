import sys
from pdf2image import pdfinfo_from_path, convert_from_path
from PIL import Image
import base64
from io import BytesIO

class thumbnail:

    pdf = '/var/www/html/storage/app/public/'+sys.argv[1]
    pdf_info = pdfinfo_from_path(pdf, userpw=None, poppler_path=None)
    image = convert_from_path(pdf, first_page=1, last_page=1)[0]
    width, height = image.size
    top_half = image.crop((0, 0, width, height // 2))
    reduced_image = top_half.resize((top_half.width // 8, top_half.height // 8))
    buffered = BytesIO()
    reduced_image.save(buffered, format="PNG")
    img_byte = buffered.getvalue()
    thumbnail = base64.b64encode(img_byte).decode('utf-8')

    print(thumbnail)
