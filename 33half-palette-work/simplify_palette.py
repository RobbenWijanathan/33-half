from copy import copy
from pathlib import Path
from zipfile import ZipFile

from lxml import etree

SOURCE = Path("../33half-closing-work/output/33-half-pitch-with-thank-you.pptx")
DRAFT = Path("palette-draft.pptx")
PALETTE_SLIDE = "ppt/slides/slide7.xml"
NS = {
    "p": "http://schemas.openxmlformats.org/presentationml/2006/main",
    "a": "http://schemas.openxmlformats.org/drawingml/2006/main",
}
HEX_CODES = ["#AB0808", "#360021", "#820030", "#D74D00", "#F1AD7B", "#F8E1BE"]


def position(shape, *, y, height, x=None, width=None):
    transform = shape.find("p:spPr/a:xfrm", NS)
    offset = transform.find("a:off", NS)
    extent = transform.find("a:ext", NS)
    offset.set("y", str(y))
    extent.set("cy", str(height))
    if x is not None:
        offset.set("x", str(x))
    if width is not None:
        extent.set("cx", str(width))


with ZipFile(SOURCE) as source:
    slide = etree.fromstring(source.read(PALETTE_SLIDE))
    tree = slide.find("p:cSld/p:spTree", NS)
    shapes = {
        int(shape.find("p:nvSpPr/p:cNvPr", NS).get("id")): shape
        for shape in tree.findall("p:sp", NS)
    }
    heading = shapes[2]
    heading.find("p:nvSpPr/p:cNvPr", NS).set("name", "Color Palette")
    heading_text = heading.find("p:txBody", NS)
    first_line, second_line = heading_text.findall("a:p", NS)
    assert first_line.find(".//a:t", NS).text == "BRAND"
    first_line.find(".//a:t", NS).text = "COLOR PALETTE"
    heading_text.remove(second_line)
    position(heading, x=9953625, y=2150000, height=1171702)

    for index, hex_code in enumerate(HEX_CODES):
        swatch_id = 6 + 3 * index
        swatch, name, label = (shapes[swatch_id + offset] for offset in range(3))
        assert label.find(".//a:t", NS).text == hex_code
        assert swatch.find("p:spPr/a:solidFill/a:srgbClr", NS).get("val") == hex_code[1:]
        swatch.find("p:nvSpPr/p:cNvPr", NS).set("name", f"{hex_code} swatch")
        row_y = 3660000 + (index // 3) * 2220000
        position(swatch, y=row_y, height=1200000)
        position(label, y=row_y + 1300000, height=410000, width=2095500)
        for properties in label.xpath(".//*[@sz]"):
            properties.set("sz", "1920")
        tree.remove(name)

    tree.remove(shapes[24])
    tree.remove(shapes[25])
    with ZipFile(DRAFT, "w") as draft:
        for item in source.infolist():
            data = source.read(item)
            if item.filename == PALETTE_SLIDE:
                data = etree.tostring(slide, encoding="UTF-8", xml_declaration=True)
            draft.writestr(copy(item), data)

    with ZipFile(DRAFT) as draft:
        assert draft.testzip() is None
        original_crcs = {item.filename: item.CRC for item in source.infolist()}
        changed_parts = [
            item.filename for item in draft.infolist() if item.CRC != original_crcs[item.filename]
        ]
        assert changed_parts == [PALETTE_SLIDE]
        updated = etree.fromstring(draft.read(PALETTE_SLIDE))
        assert [item.text for item in updated.findall(".//a:t", NS)] == [
            "COLOR PALETTE", *HEX_CODES, "08 / 11"
        ]
