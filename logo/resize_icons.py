from PIL import Image
import os

# Các kích thước icon phổ biến
sizes = [16, 32, 48, 64, 96, 128, 256, 512, 1024]

# Đọc logo gốc (PNG hoặc SVG convert)
try:
    source = Image.open('logo.png')
    print(f"Original size: {source.size}")
    
    # Tạo các kích thước khác nhau
    for size in sizes:
        resized = source.resize((size, size), Image.Resampling.LANCZOS)
        output_name = f'logo_{size}x{size}.png'
        resized.save(output_name, 'PNG', optimize=True)
        print(f"Created: {output_name}")
    
    # Tạo file ICO với nhiều kích thước
    ico_sizes = [(16, 16), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)]
    icon_images = []
    for size in ico_sizes:
        resized = source.resize(size, Image.Resampling.LANCZOS)
        icon_images.append(resized)
    
    icon_images[0].save('logo_multi.ico', format='ICO', sizes=ico_sizes)
    print(f"Created: logo_multi.ico with sizes {ico_sizes}")
    
    print("\n✓ All icon sizes created successfully!")
    
except Exception as e:
    print(f"Error: {e}")
