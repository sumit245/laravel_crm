<form action="{{ route("candidates.upload", $candidate->id) }}" method="POST" enctype="multipart/form-data">
  @csrf
  <label for="documents">Upload Required Documents:</label>
  <input type="file" name="documents[]" multiple required>
  <button type="submit">Submit</button>
</form>
