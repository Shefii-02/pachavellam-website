<form action="{{ kpsc_cms('daily-exam/update-question/' . $exam_detail->id) }}" method="POST">
    @csrf
    <div class="form-group border-bottom mb-2 p-3 bg-light">
        <div class="">
            <div class="">
                <div class="row">
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Question</label>
                        <textarea class="form-control my-editor" id="desc-editor-1" required name="question" placeholder="Enter Question"
                            rows="3">{{ $exam_detail->question }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Answer</label>
                        <textarea class="form-control my-editor" id="desc-ans-1" required autocomplete="off" type="text" name="answer"
                            placeholder="Enter Answer">{{ $exam_detail->currect_ans }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Option1</label>
                        <textarea class="form-control my-editor" id="desc-op1-1" autocomplete="off" type="text" name="option1"
                            placeholder="Enter Option1">{{ $exam_detail->option_1 }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Option2</label>
                        <textarea class="form-control my-editor" id="desc-op2-1" autocomplete="off" type="text" name="option2"
                            placeholder="Enter Option2">{{ $exam_detail->option_2 }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Option3</label>
                        <textarea class="form-control my-editor" id="desc-op3-1" autocomplete="off" type="text" name="option3"
                            placeholder="Enter Option3">{{ $exam_detail->option_3 }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Option4</label>
                        <textarea class="form-control  my-editor" id="desc-op4-1" autocomplete="off" type="text" name="option4"
                            placeholder="Enter Option4">{{ $exam_detail->option_4 }}</textarea>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Explanation</label>
                        <textarea class="form-control" id="explanation-1" autocomplete="off" type="text" name="explanation"
                            placeholder="Enter Explanation">{{ $exam_detail->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>