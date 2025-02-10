<form class="" action="{{ url('kpsc/model-exam/store-result/' . $id) }}" enctype="multipart/form-data"
    method="POST">

    @csrf
    <div class="form-group border-bottom mb-2 p-3 bg-light">
        <div class="row">
            <div class="col-md-12 mt-2">
                <label class="form-label">Full Name</label>
                <input class="form-control" required type="text" name="fullname" placeholder="">

            </div>
            <div class="col-md-12 mt-2">
                <label class="form-label">Mobile Number</label>
                <input class="form-control" required
                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');"
                    maxlength="10" type="text" name="mobile_no" placeholder="">
            </div>
            <div class="col-md-12 mt-2">
                <label class="form-label">No:of Correct Answer</label>
                <input class="form-control" type="number" name="correct_answer" placeholder="" autocomplete="off">

            </div>
            <div class="col-md-12 mt-2">
                <label class="form-label">No:of Wrong Answer</label>
                <input class="form-control" type="number" name="wrong_answer" placeholder="" autocomplete="off">
            </div>

            <div class="col-md-12 mb-2 text-center mt-4">
                <button type="submit" class="btn btn-success">Save</button>
            </div>

        </div>
    </div>

</form>
