<!DOCTYPE html>
<html>
<body>

<form action="" method="post" enctype="multipart/form-data">
    @csrf
  Select image to upload:
  <input type="file" name="file" id="file">
  <input type="submit" value="Upload Image" name="submit">
</form>

</body>
</html>