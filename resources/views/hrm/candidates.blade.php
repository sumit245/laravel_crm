<form action="{{ route("import.candidates") }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="file" name="file" required>
  <button type="submit">Import Candidates</button>
</form>

<form action="{{ route("send.emails") }}" method="POST">
  @csrf
  <button type="submit">Send Emails</button>
</form>
