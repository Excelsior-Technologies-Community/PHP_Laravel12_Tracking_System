@extends('layouts.app')

@section('title', 'Tracking Links')


@section('content')


{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>
        <i class="bi bi-link-45deg"></i>
        Tracking Links
    </h1>


<div>

    <a href="{{ route('tracking-links.export', request()->query()) }}"
        class="btn btn-success">

        <i class="bi bi-file-earmark-spreadsheet"></i>

        Export CSV

    </a>

    <a href="{{ route('tracking-links.trash') }}"
        class="btn btn-warning">

        <i class="bi bi-trash"></i>

        Trash

    </a>

    <a href="{{ route('tracking-links.create') }}"
        class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Create New Link

    </a>

</div>


</div>


{{-- Statistics Cards --}}
<div class="row mb-4">


    <div class="col-md-3">

        <div class="card text-bg-primary">

            <div class="card-body">

                <h6>Total Links</h6>

                <h2>
                    {{ $stats['total'] }}
                </h2>

            </div>

        </div>

    </div>



    <div class="col-md-3">

        <div class="card text-bg-success">

            <div class="card-body">

                <h6>Active Links</h6>

                <h2>
                    {{ $stats['active'] }}
                </h2>

            </div>

        </div>

    </div>




    <div class="col-md-3">

        <div class="card text-bg-warning">

            <div class="card-body">

                <h6>Trash</h6>

                <h2>
                    {{ $stats['deleted'] }}
                </h2>

            </div>

        </div>

    </div>




    <div class="col-md-3">

        <div class="card text-bg-info">

            <div class="card-body">

                <h6>Total Clicks</h6>

                <h2>
                    {{ $stats['clicks'] }}
                </h2>

            </div>

        </div>

    </div>


</div>







{{-- Search + Filter --}}

<div class="card mb-4">


    <div class="card-body">


        <form method="GET"
            action="{{ route('tracking-links.index') }}">


            <div class="row">



                <div class="col-md-6">


                    <input type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search name, URL or slug..."
                        value="{{ request('search') }}">


                </div>




                <div class="col-md-3">


                    <select name="status"
                        class="form-select">


                        <option value="">
                            All Status
                        </option>


                        <option value="active"
                            @if(request('status')=='active' )
                            selected
                            @endif>
                            Active
                        </option>



                        <option value="deleted"
                            @if(request('status')=='deleted' )
                            selected
                            @endif>
                            Deleted
                        </option>


                    </select>


                </div>




                <div class="col-md-3">


                    <button class="btn btn-primary">

                        <i class="bi bi-search"></i>

                        Search

                    </button>



                    <a href="{{ route('tracking-links.index') }}"
                        class="btn btn-secondary">

                        Clear

                    </a>


                </div>




            </div>


        </form>


    </div>


</div>







{{-- Table --}}


@if($links->count()==0)


<div class="alert alert-info">

    No tracking links found.

</div>


@else


<div class="table-responsive">


    <table class="table table-bordered table-hover">


        <thead class="table-dark">


            <tr>

                <th>
                    Name
                </th>


                <th>
                    Original URL
                </th>


                <th>
                    Tracking URL
                </th>


                <th>
                    Clicks
                </th>


                <th>
                    Status
                </th>


                <th>
                    Created
                </th>


                <th>
                    Action
                </th>


            </tr>


        </thead>





        <tbody>


            @foreach($links as $link)


            <tr>


                <td>

                    {{ $link->name }}

                </td>





                <td>

                    <a href="{{ $link->original_url }}"
                        target="_blank">

                        {{ Str::limit($link->original_url,40) }}

                    </a>


                </td>





                <td>


                    <div class="input-group">


                        <input type="text"
                            class="form-control"
                            readonly
                            id="url-{{ $link->id }}"
                            value="{{ $link->tracking_url }}">



                        <button class="btn btn-outline-secondary"
                            onclick="copyUrl('url-{{ $link->id }}')">


                            <i class="bi bi-clipboard"></i>


                        </button>


                    </div>


                </td>





                <td>


                    <span class="badge bg-primary">

                        {{ $link->clicks_count }}

                    </span>


                </td>





                <td>


                    @if($link->status=='active')


                    <span class="badge bg-success">

                        Active

                    </span>


                    @else


                    <span class="badge bg-danger">

                        Deleted

                    </span>


                    @endif


                </td>





                <td>


                    {{ $link->created_at->format('d M Y') }}


                </td>





                <td>



                    <a href="{{ route('tracking-links.show',$link) }}"
                        class="btn btn-info btn-sm">


                        <i class="bi bi-eye"></i>


                    </a>



                    <form action="{{ route('tracking-links.destroy',$link) }}"
                        method="POST"
                        class="d-inline">


                        @csrf

                        @method('DELETE')


                        <button type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Move to trash?')">


                            <i class="bi bi-trash"></i>


                        </button>


                    </form>



                </td>



            </tr>


            @endforeach



        </tbody>


    </table>


</div>



{{-- Pagination --}}

<div class="d-flex justify-content-center">


    @if ($links->lastPage() > 1)
    <div class="d-flex justify-content-center mt-4">
        <nav>
            <ul class="pagination">

                @for ($i = 1; $i <= $links->lastPage(); $i++)

                    <li class="page-item {{ $links->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link"
                            href="{{ $links->url($i) }}">
                            {{ $i }}
                        </a>
                    </li>

                    @endfor

            </ul>
        </nav>
    </div>
    @endif

</div>



@endif






<script>
    function copyUrl(id) {

        let input = document.getElementById(id);


        input.select();


        navigator.clipboard.writeText(input.value);


        alert("Tracking URL copied!");

    }
</script>



@endsection