import re
from copy import copy
from pathlib import Path
from uuid import uuid4
from zipfile import ZIP_DEFLATED, ZipFile

from lxml import etree


SOURCE = Path("../33half-references-work/output/33-half-pitch-with-references.pptx")
DRAFT = Path("closing-draft.pptx")
PRESENTATION = "ppt/presentation.xml"
PRESENTATION_RELS = "ppt/_rels/presentation.xml.rels"
CONTENT_TYPES = "[Content_Types].xml"
APP_PROPERTIES = "docProps/app.xml"
COVER = "ppt/slides/slide1.xml"
COVER_RELS = "ppt/slides/_rels/slide1.xml.rels"
CLOSING = "ppt/slides/slide11.xml"
CLOSING_RELS = "ppt/slides/_rels/slide11.xml.rels"

P = "http://schemas.openxmlformats.org/presentationml/2006/main"
A = "http://schemas.openxmlformats.org/drawingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
A16 = "http://schemas.microsoft.com/office/drawing/2014/main"
NS = {"p": P, "a": A, "a16": A16}
SLIDE_COUNT = 11


def to_xml(element):
    return etree.tostring(element, encoding="UTF-8", xml_declaration=True)


def update_page_label(raw_xml, number):
    old = f"{number:02d} / 10".encode()
    new = f"{number:02d} / {SLIDE_COUNT}".encode()
    pattern = rb'(?<=>)' + old + rb'(?=<)'
    updated, count = re.subn(pattern, new, raw_xml)
    assert count == 1, number
    return updated


def make_closing(cover_xml):
    closing = etree.fromstring(cover_xml)
    closing.find("p:cSld", NS).set("name", "Thank You")
    shapes = {
        shape.find("p:nvSpPr/p:cNvPr", NS).get("id"): shape
        for shape in closing.findall("p:cSld/p:spTree/p:sp", NS)
    }
    title = shapes["2"]
    title.find(".//a:t", NS).text = "THANK YOU"
    title.find("p:nvSpPr/p:cNvPr", NS).set("name", "Thank You")
    title.find("p:spPr/a:xfrm/a:off", NS).set("y", "1300000")
    title.find("p:spPr/a:xfrm/a:ext", NS).set("cy", "4700000")
    for properties in title.xpath(".//*[@sz]", namespaces=NS):
        properties.set("sz", "22400")
    spacing = title.find(".//a:lnSpc", NS)
    spacing.clear()
    etree.SubElement(spacing, f"{{{A}}}spcPct", val="100000")
    shapes["20"].find(".//a:t", NS).text = "33½"
    shapes["20"].find("p:nvSpPr/p:cNvPr", NS).set("name", "33½")
    shapes["21"].find(".//a:t", NS).text = f"{SLIDE_COUNT} / {SLIDE_COUNT}"
    shapes["21"].find("p:nvSpPr/p:cNvPr", NS).set("name", f"{SLIDE_COUNT} / {SLIDE_COUNT}")
    for creation_id in closing.findall(".//a16:creationId", NS):
        creation_id.set("id", "{" + str(uuid4()).upper() + "}")
    return to_xml(closing)


with ZipFile(SOURCE) as source:
    presentation = etree.fromstring(source.read(PRESENTATION))
    presentation_rels = etree.fromstring(source.read(PRESENTATION_RELS))
    slide_list = presentation.find("p:sldIdLst", NS)
    targets = {
        rel.get("Id"): rel.get("Target").lstrip("/")
        for rel in presentation_rels
        if rel.get("Type") == R + "/slide"
    }
    slide_paths = [
        target if target.startswith("ppt/") else f"ppt/{target}"
        for slide in slide_list
        for target in [targets[slide.get(f"{{{R}}}id")]]
    ]
    assert len(slide_paths) == SLIDE_COUNT - 1 and slide_paths[0] == COVER
    assert CLOSING not in source.namelist()

    closing_rel_id = "rIdThankYouSlide"
    next_id = str(max(int(slide.get("id")) for slide in slide_list) + 1)
    etree.SubElement(slide_list, f"{{{P}}}sldId", {"id": next_id, f"{{{R}}}id": closing_rel_id})
    etree.SubElement(
        presentation_rels, presentation_rels[0].tag,
        Id=closing_rel_id, Target="/"+CLOSING, Type=R+"/slide",
    )
    content_types = etree.fromstring(source.read(CONTENT_TYPES))
    etree.SubElement(
        content_types, f"{{{etree.QName(content_types).namespace}}}Override",
        PartName="/"+CLOSING,
        ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml",
    )
    app_properties = etree.fromstring(source.read(APP_PROPERTIES))
    app_properties.find("{*}Slides").text = str(SLIDE_COUNT)
    closing_rels = etree.fromstring(source.read(COVER_RELS))
    for rel in list(closing_rels):
        if rel.get("Type") == R + "/notesSlide":
            closing_rels.remove(rel)

    replacements = {
        PRESENTATION: to_xml(presentation),
        PRESENTATION_RELS: to_xml(presentation_rels),
        CONTENT_TYPES: to_xml(content_types),
        APP_PROPERTIES: to_xml(app_properties),
        **{
            slide_path: update_page_label(source.read(slide_path), number)
            for number, slide_path in enumerate(slide_paths, 1)
        },
    }
    with ZipFile(DRAFT, "w", ZIP_DEFLATED) as draft:
        for part in source.infolist():
            draft.writestr(
                copy(part), replacements[part.filename] if part.filename in replacements else source.read(part),
            )
        draft.writestr(CLOSING, make_closing(source.read(COVER)))
        draft.writestr(CLOSING_RELS, to_xml(closing_rels))

with ZipFile(DRAFT) as draft:
    assert draft.testzip() is None
    for number, slide_path in enumerate([*slide_paths, CLOSING], 1):
        labels = etree.fromstring(draft.read(slide_path)).findall(".//a:t", NS)
        assert labels[-1].text == f"{number:02d} / {SLIDE_COUNT}"
    assert labels[0].text == "THANK YOU"
print(f"Verified {SLIDE_COUNT} slides and page labels.")
