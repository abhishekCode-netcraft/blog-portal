<script>
    $(document).ready(function() {
        $("#multipleFiles").change(function() {
            $('#multiImagesDownload').show();
        });

        $("#downloadMultipleImage").click(function() {
            $("#multipleImagesform").submit();
        });

        $("#method").change(function() {
            $("#form").submit();
        })

        $('input[name="maker"], input[name="size"], input[name="format"]').change(function() {
            $("#form").submit();
        })

        $(".copy").click(function() {
            navigator.clipboard.writeText($(this).attr('id'));
            alert('Copied');
        });

        $("#refresh").click(function() {
            $.ajax({
                type: "GET",
                url: "{{ route('image.url.refresh') }}",
                data: {
                    session: $(this).data('session')
                },
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    if (result) {
                        var imageURl = "{{ url('/') }}/storage/uploads/" + result;
                        $(".image-url").val(imageURl);
                        $(".copy").attr('id', imageURl);
                        $("#download").attr('href', imageURl)
                    }
                },
            });
        })
    })
</script>