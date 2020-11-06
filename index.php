@extends('layout.base')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card sizeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 pb-2">
                            <h1 class="text-capitalize"><i class="fa fa-chart-bar mr-2"></i>statistique</h1>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="float-left w-100">
                                {!! Form::open(['method' => 'get','url' => route('statistique.index'), 'class' => 'form-inline w-100']) !!}
                                <div class="form-group mb-2 col-4 p-0">
                                    {!! Form::text('search', null, ['class' => 'form-control col-12', 'placeholder' => "Exemple : 2014, 04/2017, 04/2014 - 08/2018 "]) !!}
                                </div>
                                <button type="submit" class="btn btn-info mx-sm-3 mb-2">
                                    Rechercher
                                </button>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>

                    <script src="https://code.highcharts.com/highcharts.js"></script>
                    <script src="https://code.highcharts.com/modules/exporting.js"></script>
                    <script src="https://code.highcharts.com/modules/export-data.js"></script>
                    <script src="https://code.highcharts.com/modules/broken-axis.js"></script>

                    <script type="text/javascript">
                        Highcharts.wrap(Highcharts.Chart.prototype, 'init', function (proceed, options, callback) {
                            if (options.chart && options.chart.forExport && options.series) {
                                $.each(options.series, function () {
                                    this.showInLegend = false;
                                });
                            }
                            return proceed.call(this, options, callback);
                        });
                    </script>

                    <div class="row">
                        <div class="col-12 my-5">
                            {!! App::make(\App\Http\Controllers\StatistiqueController::class)->makeChart($dateNow, 'sexe') !!}
                        </div>

                        <div class="col-12 my-5">
                            {!! App::make(\App\Http\Controllers\StatistiqueController::class)->makeChart($dateNow, 'ville') !!}
                        </div>

                        <div class="col-12 my-5">
                            {!! App::make(\App\Http\Controllers\StatistiqueController::class)->makeChart($dateNow, 'etablissement') !!}
                        </div>

                        <div class="col-12 my-5">
                            {!! App::make(\App\Http\Controllers\StatistiqueController::class)->makeChart($dateNow, 'classe') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


