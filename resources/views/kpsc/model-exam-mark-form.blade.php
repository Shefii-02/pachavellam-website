<form class="" action="{{ url('kpsc/model-exam/store-result/' . $id) }}" enctype="multipart/form-data"
    method="POST">

    @csrf
    <p class="small text-muted mb-0">Uploading as <b>{{ auth()->user()->name }}</b></p>
    <div class="form-group border-bottom mb-2 p-3 bg-light">
        <div class="row">
            <div class="col-md-12 mt-2">
                <label class="form-label">No:of Correct Answer</label>
                <input class="form-control" type="number" min="0" required name="correct_answer" placeholder="" autocomplete="off">

            </div>
            <div class="col-md-12 mt-2">
                <label class="form-label">No:of Wrong Answer</label>
                <input class="form-control" type="number" min="0" required name="wrong_answer" placeholder="" autocomplete="off">
            </div>

            <div class="col-md-12 mb-2 text-center mt-4">
                <button type="submit" class="btn btn-success">Save</button>
            </div>

        </div>
    </div>

</form>
