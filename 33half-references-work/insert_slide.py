from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re

from lxml import etree as ET

P = "http://schemas.openxmlformats.org/presentationml/2006/main"
A = "http://schemas.openxmlformats.org/drawingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
REL = "http://schemas.openxmlformats.org/package/2006/relationships"
CT = "http://schemas.openxmlformats.org/package/2006/content-types"
APP = "http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"


def tag(namespace, local):
    return f"{{{namespace}}}{local}"


def read_xml(package, part):
    return ET.fromstring(package.read(part))


def xml_bytes(element):
    return ET.tostring(element, encoding="UTF-8", xml_declaration=True)


input_path = Path("input/source.pptx")
new_slide_path = Path("refs-slide.pptx")
output_path = Path("deck-draft.pptx")

with ZipFile(input_path) as original, ZipFile(new_slide_path) as added:
    updates = {}
    presentation = read_xml(original, "ppt/presentation.xml")
    slide_list = presentation.find(tag(P, "sldIdLst"))
    assert len(slide_list) == 9
    slide_list.insert(5, ET.Element(tag(P, "sldId"), {
        "id": str(max(int(slide.get("id")) for slide in slide_list) + 1),
        tag(R, "id"): "rIdReferencesSlide",
    }))
    updates["ppt/presentation.xml"] = xml_bytes(presentation)

    links = read_xml(original, "ppt/_rels/presentation.xml.rels")
    ET.SubElement(links, tag(REL, "Relationship"), {
        "Id": "rIdReferencesSlide", "Type": f"{R}/slide", "Target": "/ppt/slides/slide10.xml",
    })
    updates["ppt/_rels/presentation.xml.rels"] = xml_bytes(links)

    content_types = read_xml(original, "[Content_Types].xml")
    for part, kind in (("slides/slide10.xml", "slide"), ("notesSlides/notesSlide10.xml", "notesSlide")):
        ET.SubElement(content_types, tag(CT, "Override"), {
            "PartName": f"/ppt/{part}",
            "ContentType": f"application/vnd.openxmlformats-officedocument.presentationml.{kind}+xml",
        })
    updates["[Content_Types].xml"] = xml_bytes(content_types)

    new_slide = read_xml(added, "ppt/slides/slide1.xml")
    new_canvas = new_slide.find(tag(P, "cSld"))
    source_background = read_xml(original, "ppt/slides/slide7.xml").find(f".//{tag(P, 'bg')}")
    new_canvas.replace(new_canvas.find(tag(P, "bg")), source_background)
    updates["ppt/slides/slide10.xml"] = xml_bytes(new_slide)

    new_links = read_xml(added, "ppt/slides/_rels/slide1.xml.rels")
    image_count = 0
    for link in new_links:
        kind = link.get("Type").rsplit("/", 1)[-1]
        if kind == "slideLayout":
            link.set("Target", "/ppt/slideLayouts/slideLayout7.xml")
        elif kind == "notesSlide":
            link.set("Target", "/ppt/notesSlides/notesSlide10.xml")
        elif kind == "image":
            image_count += 1
            target = f"/ppt/media/reference-photo-{image_count}.jpeg"
            updates[target.lstrip("/")] = added.read(link.get("Target").lstrip("/"))
            link.set("Target", target)
    assert image_count == 4
    updates["ppt/slides/_rels/slide10.xml.rels"] = xml_bytes(new_links)

    updates["ppt/notesSlides/notesSlide10.xml"] = added.read("ppt/notesSlides/notesSlide1.xml")
    note_links = read_xml(added, "ppt/notesSlides/_rels/notesSlide1.xml.rels")
    for link in note_links:
        if link.get("Type") == f"{R}/slide":
            link.set("Target", "/ppt/slides/slide10.xml")
    updates["ppt/notesSlides/_rels/notesSlide10.xml.rels"] = xml_bytes(note_links)

    app = read_xml(original, "docProps/app.xml")
    app.find(tag(APP, "Slides")).text = "10"
    app.find(tag(APP, "Notes")).text = "10"
    updates["docProps/app.xml"] = xml_bytes(app)

    for index in range(1, 10):
        part = f"ppt/slides/slide{index}.xml"
        previous = f"<a:t>{index:02d} / 09</a:t>".encode()
        current = f"<a:t>{index + (index >= 6):02d} / 10</a:t>".encode()
        markup = original.read(part)
        assert markup.count(previous) == 1, part
        updates[part] = markup.replace(previous, current, 1)

    with ZipFile(output_path, "w", ZIP_DEFLATED, compresslevel=5) as packaged:
        for part in original.namelist():
            packaged.writestr(part, updates.pop(part) if part in updates else original.read(part))
        for part, data in updates.items():
            packaged.writestr(part, data)
