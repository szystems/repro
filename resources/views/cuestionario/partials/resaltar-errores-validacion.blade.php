@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serverErrorAlert = document.querySelector('.form-content > .alert-danger');
    if (serverErrorAlert) {
        serverErrorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    @foreach($errors->keys() as $errorKey)
    @php
        $parts = explode('.', $errorKey);
        $inputName = array_shift($parts);
        foreach ($parts as $part) {
            $inputName .= '['.$part.']';
        }
    @endphp
    (function () {
        const field = document.querySelector('[name="{{ $inputName }}"]');
        if (field) {
            field.classList.add('is-invalid');
            if (!serverErrorAlert) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    })();
    @endforeach
});
</script>
@endif
