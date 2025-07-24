import sys
from wand.image import Image

def convert_pdf_to_image(pdf_path, output_path):
    with Image(filename=pdf_path, resolution=300) as img:
        img.format = 'jpeg'
        img.save(filename=output_path)

if __name__ == "__main__":
    # Get PDF path and output path from command line arguments
    pdf_path = sys.argv[1]
    output_path = sys.argv[2]

    # Convert PDF to image
    convert_pdf_to_image(pdf_path, output_path)
