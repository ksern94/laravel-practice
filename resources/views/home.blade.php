<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>Laravel</title>    

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

        <style type="text/css">
            body {
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: space-between;
              /*background: linear-gradient(to right, #8E24AA, #b06ab3);*/
              /*color: #D7D7EF;*/
              font-family: 'Lato', sans-serif;
              padding:  50px;
            }

            h2 {
              margin: 50px 0;
            }

            #file-drop-area {
              position: relative;
              display: flex;
              align-items: center;
              /*width: 450px;*/
              /*max-width: 100%;*/
              padding: 25px;
              border: 1px solid black;
              border-radius: 3px;
              transition: 0.2s;
              justify-content: space-between;
            }

            #upload-file-button {
              z-index: 100;
              flex-shrink: 0;
              background-color: rgba(255, 255, 255, 0.04);
              border: 1px solid black;
              border-radius: 3px;
              padding: 8px 15px;
              margin-right: 10px;
              font-size: 12px;
            }

            .file-message {
              font-size: small;
              font-weight: 300;
              line-height: 1.4;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }

            #file-input {
              position: absolute;
              left: 0;
              top: 0;
              height: 100%;
              width: 100%;
              cursor: pointer;
              opacity: 0;
            }

            table {
                margin-top: 30px;
            }

            thead th{
                border: 1px solid black !important;
                background-color: grey;
            }
            tbody td{
                border: 1px solid black;
            }
            tbody tr:nth-child(even){
                background:  lightgray;
            }
        </style>
    </head>
    <body class="container">
        <form id="file-drop-area" class="row col-12">
          
          <span class="file-message">Select file/Drag and drop</span>
          <input id="file-input" type="file" name="file">
          <button id="upload-file-button">Upload file</button>
          
        </form>

        <table class="table">
          <thead>
            <tr>
              <th scope="col">Time</th>
              <th scope="col">File Name</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $oneData)
                <tr data-id="{{$oneData->id}}">
                  <td data-type="timestamp">{{$oneData->updated_at}}</td>
                  <td data-type="file_name">{{$oneData->file_name}}</td>
                  <td data-type="status">{{$oneData->status}}</td>
                </tr>
            @endforeach
          </tbody>
        </table>
    </body>

    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(document).ready(function(){

            $(document).on('change', '#file-input', function() {
              var filesCount = $(this)[0].files.length;
              var textbox = $(".file-message");

              if (filesCount === 1) {
                var fileName = $(this).val().split('\\').pop();
                textbox.text(fileName);
              } else {
                textbox.text(filesCount + ' files selected');
              }
            });

            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#file-drop-area').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                // console.log(formData);
                $.ajax({
                    type:'POST',
                    url: "{{ url('upload')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        if(data.status == 'success')
                        {
                            alert('File has been uploaded');
                        }
                        else
                        {
                            alert('File upload failed');
                        }
                    },
                    error: function(data){
                        // console.log(data);
                    }
                });
            });

            Echo.channel('file-upload-channel')
            .listen('FileUploadEvent', (res) => {
                // console.log(res);
                updateOrCreateRow(res.data);
            });
        })

        function updateOrCreateRow(data)
        {
            // console.log(data);
            let time = data.updated_at;

            //find element
            if($(`tr[data-id='${data.id}']`).length > 0)
            {
                //update exising
                $(`tr[data-id='${data.id}']`).find('td[data-type="timestamp"]').text(time);
                $(`tr[data-id='${data.id}']`).find('td[data-type="status"]').text(data.status);
            }
            else
            {
                //create row
                $("tbody").append($(`<tr data-id="${data.id}">`)
                    .append($('<td data-type="timestamp">').text(time))
                    .append($('<td data-type="file_name">').text(data.file_name))
                    .append($('<td data-type="status">').text(data.status))
                );
            }
        }
    </script>
      
</html>
