
    <form action="{{ug_pg_cms('class-request/update/'.$show->id)}}" method="POST"> 
        @csrf
        <div class="row">
         
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" value="{{$show->name}}"  name="name">
            </div>
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="{{$show->email}}"  name="email">
            </div>
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Mobile No</label>
                <input type="text" class="form-control"  value="{{$show->mobile}}" name="mobile_no">
            </div>
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">College</label>
                <input type="text" class="form-control" value="{{$show->college}}"  name="college">
            </div>
            
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Course</label>
                <input type="text" class="form-control" value="{{$show->course}}"  name="course">
            </div>
            
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control" value="{{$show->subject}}"  name="subject">
            </div>
            
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Type</label>
                <select class="form-control"  name="type">
                    <option value="online" @if($show->status == 'online') selected @endif>Online</option>
                    <option value="offline" @if($show->status == 'offline') selected @endif>Offline</option>
                </select>
            </div>
            
            <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Status</label>
                <select class="form-control"  name="status">
                    <option value="0" @if($show->status == 0) selected @endif>Pending</option>
                    <option value="1" @if($show->status == 1) selected @endif>Confirm</option>
                    <option value="2" @if($show->status == 2) selected @endif>Deleted</option>
                </select>
            </div>
             <div class="col-lg-12 col-md-12 ">
                <label class="form-label">Comments</label>
                <textarea class="form-control" rows="3"  name="comments">{{$show->comments}}</textarea>
            </div>
            
            
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
