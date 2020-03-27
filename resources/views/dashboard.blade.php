@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="ibox bg-success color-white widget-stat">
                <div class="ibox-body">
                    <h2 class="m-b-5 font-strong">201</h2>
                    <div class="m-b-5">NEW ORDERS</div><i class="ti-shopping-cart widget-stat-icon"></i>
                    <div><i class="fa fa-level-up m-r-5"></i><small>25% higher</small></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ibox bg-info color-white widget-stat">
                <div class="ibox-body">
                    <h2 class="m-b-5 font-strong">1250</h2>
                    <div class="m-b-5">UNIQUE VIEWS</div><i class="ti-bar-chart widget-stat-icon"></i>
                    <div><i class="fa fa-level-up m-r-5"></i><small>17% higher</small></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ibox bg-warning color-white widget-stat">
                <div class="ibox-body">
                    <h2 class="m-b-5 font-strong">$1570</h2>
                    <div class="m-b-5">TOTAL INCOME</div><i class="fa fa-money widget-stat-icon"></i>
                    <div><i class="fa fa-level-up m-r-5"></i><small>22% higher</small></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ibox bg-danger color-white widget-stat">
                <div class="ibox-body">
                    <h2 class="m-b-5 font-strong">108</h2>
                    <div class="m-b-5">NEW USERS</div><i class="ti-user widget-stat-icon"></i>
                    <div><i class="fa fa-level-down m-r-5"></i><small>-12% Lower</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-body">
                    <div class="flexbox mb-4">
                        <div>
                            <h3 class="m-0">Statistics</h3>
                            <div>Your shop sales analytics</div>
                        </div>
                        <div class="d-inline-flex">
                            <div class="px-3" style="border-right: 1px solid rgba(0,0,0,.1);">
                                <div class="text-muted">WEEKLY INCOME</div>
                                <div>
                                    <span class="h2 m-0">$850</span>
                                    <span class="text-success ml-2"><i class="fa fa-level-up"></i> +25%</span>
                                </div>
                            </div>
                            <div class="px-3">
                                <div class="text-muted">WEEKLY SALES</div>
                                <div>
                                    <span class="h2 m-0">240</span>
                                    <span class="text-warning ml-2"><i class="fa fa-level-down"></i> -12%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <canvas id="bar_chart" style="height:260px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Statistics</div>
                </div>
                <div class="ibox-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <canvas id="doughnut_chart" style="height:160px;"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="m-b-20 text-success"><i class="fa fa-circle-o m-r-10"></i>Desktop 52%</div>
                            <div class="m-b-20 text-info"><i class="fa fa-circle-o m-r-10"></i>Tablet 27%</div>
                            <div class="m-b-20 text-warning"><i class="fa fa-circle-o m-r-10"></i>Mobile 21%</div>
                        </div>
                    </div>
                    <ul class="list-group list-group-divider list-group-full">
                        <li class="list-group-item">Chrome
                            <span class="float-right text-success"><i class="fa fa-caret-up"></i> 24%</span>
                        </li>
                        <li class="list-group-item">Firefox
                            <span class="float-right text-success"><i class="fa fa-caret-up"></i> 12%</span>
                        </li>
                        <li class="list-group-item">Opera
                            <span class="float-right text-danger"><i class="fa fa-caret-down"></i> 4%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Latest Orders</div>
                    <div class="ibox-tools">
                        <a class="ibox-collapse"><i class="fa fa-minus"></i></a>
                        <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item">option 1</a>
                            <a class="dropdown-item">option 2</a>
                        </div>
                    </div>
                </div>
                <div class="ibox-body">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th width="91px">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <a href="invoice.html">AT2584</a>
                            </td>
                            <td>@Jack</td>
                            <td>$564.00</td>
                            <td>
                                <span class="badge badge-success">Shipped</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="invoice.html">AT2575</a>
                            </td>
                            <td>@Amalia</td>
                            <td>$220.60</td>
                            <td>
                                <span class="badge badge-success">Shipped</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="invoice.html">AT1204</a>
                            </td>
                            <td>@Emma</td>
                            <td>$760.00</td>
                            <td>
                                <span class="badge badge-default">Pending</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="invoice.html">AT7578</a>
                            </td>
                            <td>@James</td>
                            <td>$87.60</td>
                            <td>
                                <span class="badge badge-warning">Expired</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="invoice.html">AT0158</a>
                            </td>
                            <td>@Ava</td>
                            <td>$430.50</td>
                            <td>
                                <span class="badge badge-default">Pending</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="invoice.html">AT0127</a>
                            </td>
                            <td>@Noah</td>
                            <td>$64.00</td>
                            <td>
                                <span class="badge badge-success">Shipped</span>
                            </td>
                            <td>10/07/2017</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Best Sellers</div>
                </div>
                <div class="ibox-body">
                    <ul class="media-list media-list-divider m-0">
                        <li class="media">
                            <a class="media-img" href="javascript:;">
                                <img src="./assets/img/image.jpg" width="50px;" />
                            </a>
                            <div class="media-body">
                                <div class="media-heading">
                                    <a href="javascript:;">Samsung</a>
                                    <span class="font-16 float-right">1200</span>
                                </div>
                                <div class="font-13">Lorem Ipsum is simply dummy text.</div>
                            </div>
                        </li>
                        <li class="media">
                            <a class="media-img" href="javascript:;">
                                <img src="./assets/img/image.jpg" width="50px;" />
                            </a>
                            <div class="media-body">
                                <div class="media-heading">
                                    <a href="javascript:;">iPhone</a>
                                    <span class="font-16 float-right">1150</span>
                                </div>
                                <div class="font-13">Lorem Ipsum is simply dummy text.</div>
                            </div>
                        </li>
                        <li class="media">
                            <a class="media-img" href="javascript:;">
                                <img src="./assets/img/image.jpg" width="50px;" />
                            </a>
                            <div class="media-body">
                                <div class="media-heading">
                                    <a href="javascript:;">iMac</a>
                                    <span class="font-16 float-right">800</span>
                                </div>
                                <div class="font-13">Lorem Ipsum is simply dummy text.</div>
                            </div>
                        </li>
                        <li class="media">
                            <a class="media-img" href="javascript:;">
                                <img src="./assets/img/image.jpg" width="50px;" />
                            </a>
                            <div class="media-body">
                                <div class="media-heading">
                                    <a href="javascript:;">apple Watch</a>
                                    <span class="font-16 float-right">705</span>
                                </div>
                                <div class="font-13">Lorem Ipsum is simply dummy text.</div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="ibox-footer text-center">
                    <a href="javascript:;">View All Products</a>
                </div>
            </div>
        </div>
    </div>
    <style>
        .visitors-table tbody tr td:last-child {
            display: flex;
            align-items: center;
        }

        .visitors-table .progress {
            flex: 1;
        }

        .visitors-table .progress-parcent {
            text-align: right;
            margin-left: 10px;
        }
    </style>
@endsection

@section('js')
    <script src="{{asset('assets/js/Chart.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/dashboard_1_demo.js')}}" type="text/javascript"></script>
@endsection