<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="">Home</a></li>
        <li class="breadcrumb-item active text-capitalize">
            {{ ucwords(str_replace('/', ' / ', Request::path())) }}
        </li>
    </ol>
</nav>
