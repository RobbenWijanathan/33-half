from pathlib import Path
from PIL import Image, ImageOps

files = ["gs-shop-0.jpg", "reference-1.jpg", "reference-2.jpg", "atlas-grade.jpg"]
crop_centers = [(0.5, 0.48), (0.55, 0.42), (0.5, 0.50), (0.5, 0.50)]
Path("crops").mkdir(exist_ok=True)
for i, (filename, center) in enumerate(zip(files, crop_centers)):
    with Image.open(filename) as source:
        photo = ImageOps.exif_transpose(source).convert("RGB")
        crop = ImageOps.fit(photo, (810, 632), Image.Resampling.LANCZOS, centering=center)
        crop.save(Path("crops") / f"reference-{i}.jpeg", quality=92, optimize=True)
