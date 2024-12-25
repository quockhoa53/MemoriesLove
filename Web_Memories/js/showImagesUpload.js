function updateFileNameAndPreview() {
    const input = document.getElementById('profilePic');
    const label = document.getElementById('fileLabel');
    const imagePreview = document.getElementById('imagePreview');

    if (input.files.length > 0) {
        label.textContent = input.files[0].name;

        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-50">';
            imagePreview.style.display = 'block';
        }

        reader.readAsDataURL(file);
    } else {
        label.textContent = "Tải ảnh lên";
        imagePreview.style.display = 'none';
    }
}