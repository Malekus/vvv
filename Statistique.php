<?php

namespace App\Http\Controllers;

use App\Eleve;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('search') == null) {
            $dateNow = \Carbon\Carbon::now()->format('Y');
            return view('statistique.index', compact('dateNow'));
        }

        $dateNow = $request->get('search');
        return view('statistique.index', compact('dateNow'));
    }

    public function makeChart($date, $type = null)
    {
        if ($date == null) {$date = Carbon::now()->format('Y');}
        if (strpos($date, '/') !== false) {
            $ex = explode("/", $date);
            $date = $ex[1]."-".$ex[0];
        }

        $idGraphe = $type.'Chart';

        if($type == "sexe"){

            $breaks = "breaksSexe";
            $models = ['femme', 'homme'];
            $categories = $values = [];

            foreach ($models as $model) {
                $tmp = DB::table('eleves')
                    ->where('sexe', '=',$model)
                    ->where('updated_at', 'like', '%'. $date .'%')
                    ->count();
                array_push($categories, ucfirst($model));
                array_push($values, $tmp);
            }

            $chart = "var ".$breaks." = new Map();\n    Highcharts.chart('" . $idGraphe . "', {
                        chart: {
                            type: 'column',
                            events: {
                            render: function() {
                              let series = this.series
                              let sum = 0
                              for(let i = 0; i < series.length; i++) {
                                if(series[i].visible){
                                  for(let j = 0; j < series[i].data.length; j++) {
                                    sum += series[i].data[j].y
                                  }
                                }
                              }
                            this.setTitle(false, {text: sum + ' exclus'}, false) 
                            }
                          }
                        },
                        title: {
                            text: 'Nombre d\'élèves exclus ".strval($date)."',
                        },
                        xAxis: {
                           type: 'category',
                        },
                        yAxis: {
                            min: 0,
                            
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            column: {
                                grouping: false,
                                pointPadding: 0.2,
                                borderWidth: 0,
                                events: {
                                    legendItemClick: function() {
                                        if(".$breaks.".has(this.xData[0])) {
                                        ".$breaks.".delete(this.xData[0])
                                      }
                                      else {
                                        ".$breaks.".set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                                      }
                                      this.chart.xAxis[0].update({
                                        breaks: [... ".$breaks.".values()]
                                      });
                                    }
      							}
                            },
                            series: {
                                dataLabels: { enabled: true }
                            }
                        },
                        series: ".$this->getSeries($categories, $values).",
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        });";

            return view('statistique.makeChart', ['idGraphe' => $idGraphe, 'chart' => $chart]);
        } // fin graphe sexe

        if($type == "ville"){

            $breaks = "breaksVille";
            $models = ['Stains', 'Epinay-sur-seine', 'Bagnolet'];
            $categories = $values = [];

            foreach ($models as $model) {
                $tmp = DB::table('eleves')
                    ->where('ville', '=', $model)
                    ->where('updated_at', 'like', '%'. $date .'%')
                    ->count();
                array_push($categories, ucfirst($model));
                array_push($values, $tmp);
            }

            $chart = "var ".$breaks." = new Map();\n    Highcharts.chart('" . $idGraphe . "', {
                        chart: {
                            type: 'column',
                            events: {
                            render: function() {
                              let series = this.series
                              let sum = 0
                              for(let i = 0; i < series.length; i++) {
                                if(series[i].visible){
                                  for(let j = 0; j < series[i].data.length; j++) {
                                    sum += series[i].data[j].y
                                  }
                                }
                              }
                            this.setTitle(false, {text: sum + ' exclus'}, false) 
                            }
                          }
                        },
                        title: {
                            text: 'Nombre d\'élèves exclus',
                        },
                        xAxis: {
                           type: 'category',
                        },
                        yAxis: {
                            min: 0,
                            
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            column: {
                                grouping: false,
                                pointPadding: 0.2,
                                borderWidth: 0,
                                events: {
                                    legendItemClick: function() {
                                        if(".$breaks.".has(this.xData[0])) {
                                        ".$breaks.".delete(this.xData[0])
                                      }
                                      else {
                                        ".$breaks.".set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                                      }
                                      this.chart.xAxis[0].update({
                                        breaks: [... ".$breaks.".values()]
                                      });
                                    }
      							}
                            },
                            series: {
                                dataLabels: { enabled: true }
                            }
                        },
                        series: ".$this->getSeries($categories, $values).",
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        });";

            return view('statistique.makeChart', ['idGraphe' => $idGraphe, 'chart' => $chart]);
        } // fin graphe ville

        if($type == "etablissement"){

            $breaks = "breaksEtablissement";
            $etablissements = DB::table('eleves')
                ->select(DB::raw('count(*) as nb, (select nom from etablissements where id = etablissement_id) as label'))
                ->where('updated_at', 'like', '%' . $date . '%')
                ->groupBy('etablissement_id')
                ->orderBy('etablissement_id')
                ->get()
                ->toArray();
            if(empty($etablissements)) return;
            $x = $this->getCategoriesValues($etablissements);
            $categories = $x[0];
            $values = $x[1];

            $chart = "var ".$breaks." = new Map();\n    Highcharts.chart('" . $idGraphe . "', {
                        chart: {
                            type: 'column',
                            events: {
                            render: function() {
                              let series = this.series
                              let sum = 0
                              for(let i = 0; i < series.length; i++) {
                                if(series[i].visible){
                                  for(let j = 0; j < series[i].data.length; j++) {
                                    sum += series[i].data[j].y
                                  }
                                }
                              }
                            this.setTitle(false, {text: sum + ' exclus'}, false) 
                            }
                          }
                        },
                        title: {
                            text: 'Nombre d\'élèves exclus',
                        },
                        xAxis: {
                           type: 'category',
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            column: {
                                grouping: false,
                                pointPadding: 0.2,
                                borderWidth: 0,
                                events: {
                                    legendItemClick: function() {
                                        if(".$breaks.".has(this.xData[0])) {
                                        ".$breaks.".delete(this.xData[0])
                                      }
                                      else {
                                        ".$breaks.".set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                                      }
                                      this.chart.xAxis[0].update({
                                        breaks: [... ".$breaks.".values()]
                                      });
                                    }
      							}
                            },
                            series: {
                                dataLabels: { enabled: true }
                            }
                        },
                        series: ". $this->getSeries($categories, $values) .",
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        });";


            return view('statistique.makeChart', ['idGraphe' => $idGraphe, 'chart' => $chart]);
        } // fin graphe etablissement

        if($type == "classe"){

            $breaks = "breaksClasse";
            $models = ['6ème', '5ème', '4ème', '3ème'];
            $categories = $values = [];

            foreach ($models as $model) {
                $tmp = DB::table('eleves')
                    ->where('classe', '=', $model)
                    ->where('updated_at', 'like', '%'. $date .'%')
                    ->count();
                array_push($categories, ucfirst($model));
                array_push($values, $tmp);
            }

            $chart = "var ".$breaks." = new Map();\n    Highcharts.chart('" . $idGraphe . "', {
                        chart: {
                            type: 'column',
                            events: {
                            render: function() {
                              let series = this.series
                              let sum = 0
                              for(let i = 0; i < series.length; i++) {
                                if(series[i].visible){
                                  for(let j = 0; j < series[i].data.length; j++) {
                                    sum += series[i].data[j].y
                                  }
                                }
                              }
                            this.setTitle(false, {text: sum + ' exclus'}, false) 
                            }
                          }
                        },
                        title: {
                            text: 'Nombre d\'élèves exclus',
                        },
                        xAxis: {
                           type: 'category',
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            column: {
                                grouping: false,
                                pointPadding: 0.2,
                                borderWidth: 0,
                                events: {
                                    legendItemClick: function() {
                                        if(".$breaks.".has(this.xData[0])) {
                                        ".$breaks.".delete(this.xData[0])
                                      }
                                      else {
                                        ".$breaks.".set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                                      }
                                      this.chart.xAxis[0].update({
                                        breaks: [... ".$breaks.".values()]
                                      });
                                    }
      							}
                            },
                            series: {
                                dataLabels: { enabled: true }
                            }
                        },
                        series: ". $this->getSeries($categories, $values) .",
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        });";


            return view('statistique.makeChart', ['idGraphe' => $idGraphe, 'chart' => $chart]);
        } // fin graphe classe

        return;

    }

    private function getCategoriesValues($data)
    {
        $categories = $values = [];
        foreach ($data as $key => $value) {
            array_push($values, $value->nb);
            array_push($categories, empty(ucfirst($value->label)) ? 'N/A' : ucfirst($value->label));
        }
        return [$categories, $values];
    }

    private function getSeries($categories, $values){
        $series = '[';
        for ($i = 0; $i < sizeof($categories); $i++){
            $tmp = '{name: "'. $categories[$i] .'", data: [{name: "'. $categories[$i] .'", y:'. $values[$i] .'}],},';
            $series .= $tmp;
        }
        $series = substr($series, 0, -1);
        return $series .= ']';
    }
}
