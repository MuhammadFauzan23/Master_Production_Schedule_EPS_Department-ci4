<?= $this->extend('layout/template') ?>
<?= $this->extend('layout/sidebar') ?>
<?= $this->section('content') ?>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col mt-2">
                                <h3 class="card-title">Schedule EPS Crack</h3>
                            </div>
                            <div class="col">
                                <div style="text-align: right;">
                                    <a href="" style="background-color:#7FFFD4" class="btn mr-1" data-toggle="modal" data-target="#addform"><i class="nav-icon far fa-plus-square"></i>&nbsp;Master Production</a>
                                    <a href="" style="background-color:#EDDA74" class="btn mr-1" data-toggle="modal" data-target="#addformsampel"><i class="nav-icon far fa-plus-square"></i>&nbsp;Sampel</a>
                                    <a href="" style="background-color:#C8A2C8" class="btn mr-1" data-toggle="modal" data-target="#changeDate"><i class="nav-icon far fa-calendar-alt"></i>&nbsp;Change Date</a>
                                    <a href="" style="background-color:#E0B0FF" class="btn mr-1" data-toggle="modal" data-target="#release"><i class="nav-icon fas fa-play-circle"></i>&nbsp;Release </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="tableView" class="table table-bordered" style="width: 100%;">
                            <thead align="center">
                                <tr>
                                    <th style="min-width: 40px;">M/C</th>
                                    <th style="min-width: 160px;">Started Time</th>
                                    <th style="min-width: 100px;">FG INT Code</th>
                                    <th style="min-width: 100px;">FG GEN Code</th>
                                    <th style="min-width: 60px;">R/C No</th>
                                    <th>BOM</th>
                                    <th style="min-width: 70px;">R-C Qty</th>
                                    <th style="min-width: 70px;">Bal R-C Qty</th>
                                    <th style="min-width: 230px;">Material Description</th>
                                    <th style="min-width: 160px;">Shortage Date</th>
                                    <th>Material Density(Kg/m3)</th>
                                    <th>Product Weight(Gr)</th>
                                    <th>Total Material(Kgs)</th>
                                    <th>Mould Size</th>
                                    <th>Mould Status</th>
                                    <th>Remarks</th>
                                    <th>Actual</th> <!-- Actual -->
                                    <th>R/C</th>
                                    <th>OP/hours</th>
                                    <th>Sec/hours</th>
                                    <th style="min-width: 90px;">Total Hours Required</th>
                                    <th style="min-width: 90px;">Running Days</th>
                                    <th>Change Mold</th> <!-- Change Mould -->
                                    <th>Run Join</th>
                                    <th style="min-width: 100px;">Action</th>
                                </tr>
                            </thead>

                            <tbody align="center">
                                <?php
                                function tgl_next($hari, $thour, $libur) // $hari = data hari | $thour = total hour
                                {
                                    $total_menit = $thour * 60;
                                    //----------------------------------------------------------------
                                    $start_time = DateTime::createFromFormat('d-m-Y H:i:s', trim($hari));
                                    $currentdate = $start_time->format('d-m-Y H:i:s');
                                    $ct = new DateTime($currentdate);
                                    $first_end = $start_time->setTime(16, 0, 0);
                                    $first_end = $first_end->format('d-m-Y H:i:s');
                                    $jarak_ke_jam16 = $ct->diff(new DateTime($first_end));
                                    $menit_dipakai = ($jarak_ke_jam16->h * 60) + $jarak_ke_jam16->i; //540 -> 9 jam
                                    $sisa_menit = $total_menit - $menit_dipakai;
                                    //----------------------------------------------------------------
                                    if ($sisa_menit <= 0) { //Jika kurang dari 0 menit atau tidak lewat jam 16:00 ditampilkan hari itu juga 
                                        $ct->modify('+' . ceil($total_menit) . 'minute');
                                        $final = $ct->format('d-m-Y H:i:s');
                                        return $final;
                                    } else { //Jika lebih dari jam 16:00 lanjut keesokan harinya
                                        if ($sisa_menit <= 540) { //jika kurang dari jam 16:00 / 540 menit
                                            $ct->modify('+1 weekday'); //nambah 1 hari 
                                            $sisa_menit += 7 * 60;
                                            $tambah_hari = $ct->modify('+' . ceil($sisa_menit) . 'minute')->format('d-m-Y');
                                            while (in_array($tambah_hari, $libur)) { //cek apakah tgl tsb jatuh tempo  di hari libur nasional atau engga
                                                $tambah_hari = $ct->modify('+1 day');
                                            }
                                            $final = $ct->format('d-m-Y H:i:s');
                                            return $final;
                                        } else { //Jika di hari 2nd lebih dari jam 16:00 lanjut lagi di keesokan harinya
                                            for ($i = $sisa_menit; $i > 540; $i -= 540) {
                                                $tambah_hari_1 = $ct->modify('+1 weekday')->format('d-m-Y');
                                                if (in_array($tambah_hari_1, $libur)) {
                                                    $i +=  540;
                                                    continue;
                                                }
                                            }
                                            $schedule = $tambah_hari_1;
                                            $ctt = new DateTime($schedule);
                                            $ctt->modify('+1 weekday');
                                            $tambah_hari = $ctt->modify('+' . ceil($i + (7 * 60)) . 'minute')->format('d-m-Y');
                                            while (in_array($tambah_hari, $libur)) { //cek apakah tgl tsb jatuh tempo di hari libur nasional atau engga
                                                $tambah_hari = $ctt->modify('+1 day');
                                            }
                                            $final = $ctt->format('d-m-Y H:i:s');
                                            return $final;
                                        }
                                    }
                                }

                                foreach ($data_holiday as $hd) {
                                    $holiday[] = $hd['tgl_libur'];
                                    // dd($holiday);
                                }

                                for ($i = 0; $i < count($schedule_list); $i++) :

                                    $Running_Mesin = " ";
                                    $total_jam_keseluruhan = 0;
                                    $total_running_days = 0;
                                    //-------------------------
                                    $total_Jam = 0;
                                    $no = 0;
                                    $array_tgl = [];

                                    foreach ($schedule_list[$i] as $form) :
                                        if ($form['gabung'] == 0) {
                                            $colorB = 'background-color:#7FFFD4';
                                        } else {
                                            $colorB = '';
                                        }

                                        if ($form['stts'] == "Sampel") {
                                            $colorB = 'background-color:#EDDA74';
                                            $sampel = '';
                                            $idfg  = explode(":", $form['idfg']);
                                        } else {
                                            $sampel = 'ada';
                                        }


                                        $total_Jam += $form['total_hours'];
                                        if ($form['gabung'] == 0) {
                                            $next_tgl = $form['tgl'];
                                        } else {
                                            $prev = $no - 1;
                                            if ($form['tgl'] == "YES") {
                                                $next_tgl = tgl_next($array_tgl[$prev], 0, $holiday);
                                            } else {
                                                $next_tgl = tgl_next($array_tgl[$prev], $schedule_list[$i][$prev]['total_hours'], $holiday);
                                            }
                                        }
                                        $array_tgl[$no] = $next_tgl;

                                ?>
                                        <tr>
                                            <td style="<?= $colorB ?>"><?= $form['mc']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $next_tgl ?></td>
                                            <td style="<?= $colorB ?>"><?= $sampel ? $form['fgcodeint'] : $idfg[1]; ?></td>
                                            <td style="<?= $colorB ?>"><?= $sampel ? $form['fgcodegen'] : $idfg[0]; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['rc_no']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['cav']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['rc_qty']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['bal_rc_qty']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['material']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['shortage_date']; ?></td>
                                            <td style="<?= $colorB ?>">
                                                <?php if ($form['int_density'] != 0) { ?>
                                                    <?= $form['int_density'] ?> Kg/m3
                                                <?php }
                                                if ($form['int_density'] == 0 || $form['int_density'] == " ") { ?>
                                                    <?= $form['int_density'] = 0 ?> Kg/m3
                                                <?php } ?>
                                            </td>
                                            <td style="<?= $colorB ?>"><?= $sampel ? $form['fgweight'] : 0; ?></td>
                                            <?php $total_material =  $form['fgweight'] *  $form['rc_qty'] / 1000 ?>
                                            <td style="<?= $colorB ?>"><?= $sampel ? round($total_material) : 0 ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['mould_size']; ?></td>
                                            <td style="<?= $colorB ?>"><?= $form['mould_status']; ?></td>
                                            <td style="<?= $colorB ?>" class="text-center">
                                                <a class="btn btn-secondary" data-toggle="modal" data-target="#remark<?= $form['idform']; ?>">
                                                    <i class="fab fa-airbnb fa-1x"></i>
                                                </a>
                                            </td>
                                            <div class="modal fade" id="remark<?= $form['idform']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h3>Catatan / Note</h3>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p style="font-size: 20px;"><?= $form['remarks'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php $rc = $form['act']; ?>
                                            <td style="<?= $colorB ?>"><?= number_format($form['act']) ?></td>
                                            <td style="<?= $colorB ?>"><?= number_format($form['r_c']) ?></td>
                                            <td style="<?= $colorB ?>"><?= number_format($form['op_hours'], 2, ".", ",") ?></td>
                                            <td style="<?= $colorB ?>"><?= number_format($form['sec_hours']) ?></td>
                                            <td style="<?= $colorB ?>">
                                                <?php if ($form["tgl"] != "YES") { ?>
                                                    <?= number_format($form['total_hours'], 2, ".", ","); ?>
                                                <?php } elseif ($form['total_hours'] < 0) { ?>
                                                    <?= abs($form['total_hours']); ?>
                                                <?php } elseif ($form['tgl'] != "NO") { ?>
                                                    <?= $form['total_hours'] = 0; ?>
                                                <?php } ?>
                                            </td>
                                            <?php
                                            $running_day = $form['total_hours'] / 8.83;
                                            $total_running_days += number_format($running_day, 2, ".", ",");
                                            ?>

                                            <?php
                                            if ($form["tgl"] != "YES") {
                                                $total_jam_keseluruhan += number_format($form['total_hours'], 2, ".", ",");
                                            }
                                            $Running_Mesin = $form['mc'];
                                            ?>

                                            <td style="<?= $colorB ?>"><?= number_format($running_day, 2, ".", ",") ?></td>
                                            <td style="<?= $colorB ?>">
                                                <div class="form-check text-center" id="">
                                                    <input type="checkbox" class="form-check-input" name="changemold" id="changemold" <?= cek_mold($form['idform']) ?> onclick="ubah(<?= $form['idform'] ?>, '<?= $form['mc']  ?>')">
                                                </div>
                                            </td>
                                            <td style="<?= $colorB ?>">
                                                <?php if ($form['gabung'] != 0) { ?>
                                                    <div class="form-check text-center" id="">
                                                        <input type="checkbox" class="form-check-input" name="runJoin" id="runJoin" onclick="runJoinReject(<?= $form['idform'] ?>)" <?= run_join($form['idform']) ?>>
                                                    </div>
                                                <?php } else { ?>
                                                    <p style="text-align: center;"><b>Master</b></p>
                                                <?php } ?>
                                            </td>
                                            <td style="<?= $colorB ?>">
                                                <div class="row">
                                                    <div class="col">
                                                        <!-- <a href="" class="btn btn-warning mb-1" data-toggle="modal" data-target="#editForm<?= $form['idform'] ?>"><i class="fas fa-edit"></i> </a> -->
                                                        <a href="<?= base_url() . '/schedule/viewupdate?id=' . $form['idform'] . '&idfg=' . $form['idfg']; ?>" class="btn btn-warning mb-1 edit_data"><i class="fas fa-edit"></i></a>
                                                        <!-- <a href="" class="btn btn-danger mb-1" data-toggle="modal" data-target="#deleteForm<?= $form['idform'] ?>"><i class="far fa-trash-alt"></i> </a> -->
                                                        <a href="<?= base_url() . '/schedule/deleteform?id=' . $form['idform']; ?>" class="btn btn-danger mb-1 hapus"><i class="far fa-trash-alt"></i> </a>
                                                    </div>
                                                    <?php if ($form['tgl'] != 'NO' && $form['tgl'] != 'YES') { ?>
                                                        <div class="col"><a href="<?= base_url() . '/schedule/view_master_data?id=' . $form['idform'] ?>" class="btn btn-info mb-1"><?= cek_data_list($form['view_form']) ?></a>
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="col">
                                                            <a href="" class="btn btn-success mb-1" data-toggle="modal" data-target="#changeList<?= $form['idform'] ?>"><i class="far fa-calendar-alt"></i> </a>
                                                            <a href="" class="btn btn-primary mb-1" data-toggle="modal" data-target="#changeMaster<?= $form['idform'] ?>"><i class="nav-icon fas fa-copy"></i> </a>
                                                            <a href="<?= base_url() . '/schedule/view_data_sub?id=' . $form['idform']; ?>" class="btn btn-info mb-1"><?= cek_data_list($form['view_form']) ?></a>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $no++; ?>
                                    <?php endforeach; ?><tr>
                                        <td style="background-color: #fcec03;"><?= $Running_Mesin ?></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="background-color: #fcec03;">Total : <?= $total_jam_keseluruhan ?></td>
                                        <td style="background-color: #fcec03;">Total : <?= $total_running_days ?></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>
                                        <td style="border: 0; background-color: #000;"></td>

                                    </tr>

                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</div>
<!-- /.content -->

<!-- Modal tambah list-->
<div class="modal fade" id="addform">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Schedule list</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('schedule/addform') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Join Us</label>
                                <select type="text" class="custom-select rounded-0" id="join" name="join">
                                    <option value="">-- No Join to List --</option>
                                </select>
                            </div>
                            <div class="form-group" id="list-number">
                                <label>List Number</label>
                                <select type="text" class="custom-select rounded-0" id="urutan" name="urutan">
                                    <option value="1">-- List Number --</option>
                                </select>
                            </div>

                            <div class="form-group" id="startedtime-jam">
                                <label>Started Time</label>
                                <div class="input-group">
                                    <input name="started_time" class="form-control datetimepicker" id="startedtime">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Finishgood</label>
                                <select class="form-control select2 fg" name="fg" id="fg">
                                    <option>-- Select FG --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cav">BOM</label>
                                <select type="text" class="form-control search_select cav" style="width: 100%;" id="cav" name="cav">
                                    <option value="">-- Isi Kolom FG dulu --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="machine">Machine</label>
                                <select class="form-control select2 machine" id="machine" name="machine">
                                    <option value="">-- Select Machine --</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cycle Time</label>
                                        <div class="input-group">
                                            <input type="text" name="cycle_time" class="form-control" id="cycletime" placeholder="" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No Rev</label>
                                        <div class="input-group">
                                            <input type="text" name="no_rev" class="form-control" id="rev" placeholder="" required readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>R/C No</label>
                                        <div class="input-group">
                                            <input type="text" name="rc_no" class="form-control rcno" id="rcno" placeholder="" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">R/C Qty</label>
                                        <input type="text" name="rc_qty" class="form-control rc_qty" id="rc_qty" placeholder=" " required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Bal R-C Qty</label>
                                        <input type="text" name="bal_rc_qty" class="form-control bal_rc_qty" id="bal_rc_qty" placeholder="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>R/C</label>
                                        <input type="text" name="rc" class="form-control rc" id="rc" placeholder="" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Actual</label>
                                        <input type="text" name="actual" class="form-control actual" id="actual" placeholder="" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="material">Material</label>
                                <input type="text" class="form-control material" id="material_id" name="material" readonly>
                            </div>
                            <div class="form-group">
                                <label for="name">Shortage Date</label>
                                <input type="text" name="shortage_date" class="form-control datetimepicker" id="shortage_date" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Mould Size</label>
                                        <select type="text" class="form-control mouldsize" style="width: 100%;" id="mouldsize" name="mould_size">
                                            <option value="">-- Select Size --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Mould Status</label>
                                        <select type="text" class="form-control mouldstatus" style="width: 100%;" id="mouldstatus" name="mould_status">
                                            <option value="">-- Select Status --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- OP / Hours -->
                            <input type="hidden" name="sec_hours" class="form-control" id="sec_hours" required>
                            <!--  -->

                            <div class="form-group">
                                <label for="name">Remarks</label>
                                <textarea name="remarks" class="form-control" id="remarks_id" rows="8" placeholder=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End modal -->

<!-- Modal tambah list Sampel-->
<div class="modal fade" id="addformsampel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sampel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('schedule/addsampel') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Join Us</label>
                                <select type="text" class="custom-select rounded-0" id="join_sampel" name="join">
                                    <option value="">-- No Join to List --</option>
                                </select>
                            </div>
                            <div class="form-group" id="list-number_sampel">
                                <label>List Number</label>
                                <select type="text" class="custom-select rounded-0" id="urutan_sampel" name="urutan">
                                    <option value="1">-- List Number --</option>
                                </select>
                            </div>

                            <div class="form-group" id="startedtime-jam_sampel">
                                <label>Started Time</label>
                                <div class="input-group">
                                    <input type="text" name="started_time" class="form-control datetimepicker" id="startedtime_sampel">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>FG Code Int</label>
                                        <input type="text" class="form-control " name="fg_int_code" id="fg_int_code">
                                        </input>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>FG Code Gen</label>
                                        <input class="form-control" name="fg_gen_code" id="fg_gen_code">
                                        </input>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="machine">Machine</label>
                                <input type="text" class="form-control" id="machine" name="machine">
                            </div>

                            <div class="form-group">
                                <label for="cav">BOM</label>
                                <input type="text" class="form-control" id="cav" name="cav">
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>R/C No</label>
                                        <div class="input-group">
                                            <input type="text" name="rc_no" class="form-control rcno" id="rcno" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">R/C Qty</label>
                                        <input type="text" name="rc_qty" class="form-control rc_qty" id="rc_qty_sampel" placeholder=" ">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Bal R-C Qty</label>
                                        <input type="text" name="bal_rc_qty" class="form-control bal_rc_qty" id="bal_rc_qty_sampel" placeholder="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>R/C</label>
                                        <input type="text" name="rc" class="form-control rc" id="rc" placeholder="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Actual</label>
                                        <input type="text" name="actual" class="form-control actual" id="actual" placeholder="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="material">Material Description</label>
                                <input type="text" class="form-control material" id="material_id" name="material">
                            </div>
                            <div class="form-group">
                                <label for="name">Shortage Date</label>
                                <input type="text" name="shortage_date" class="form-control datetimepicker" id="shortage_date">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Mould Size</label>
                                        <select type="text" class="form-control mouldsize" style="width: 100%;" id="mouldsize" name="mould_size">
                                            <option value="">-- Select Size --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Mould Status</label>
                                        <select type="text" class="form-control mouldstatus" style="width: 100%;" id="mouldstatus" name="mould_status">
                                            <option value="">-- Select Status --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- OP / Hours -->
                            <input type="hidden" name="sec_hours" class="form-control" id="sec_hours">
                            <!--  -->

                            <div class="form-group">
                                <label for="name">Remarks</label>
                                <textarea name="remarks" class="form-control" id="remarks_id" rows="5" placeholder=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End modal -->

<!-- Modal Change List  -->
<div class="modal fade" id="changeDate">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Date</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('schedule/change_date') ?>" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Route card</label>
                                <?php
                                $no = 1;
                                foreach ($number_list as $list) :
                                    if ($list['rc_no']) :
                                ?>
                                        <li class="list-group-item">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="nomor<?= $no ?>" name="nomor<?= $no ?>" value="<?= $list['rc_no'] ?>">
                                                <label class="form-check-label"><?= $list['rc_no'] ?></label>
                                            </div>
                                        </li>
                                <?php
                                    endif;
                                endforeach;
                                $no++;
                                ?>
                            </div>
                            <div class="form-group">
                                <label>Date/time</label>
                                <input type="text" class="form-control datetimepicker" name="started_time" required>
                                <input type="number" class="form-control" id="banyak_data" name="banyak_data" value="<?= $no; ?>" hidden>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End modal -->

<!-- Modal Release  -->
<div class="modal fade" id="release">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Release Schedule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('release/release_data') ?>" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Date/Time</label>
                                <input name="tanggal" class="form-control datepicker" type="text">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End modal -->

<?php for ($i = 0; $i < count($schedule_list); $i++) : ?>
    <?php foreach ($schedule_list[$i] as $form2) : ?>
        <!-- modal change list -->
        <div class="modal fade" id="changeList<?= $form2['idform'] ?>">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Update List Number</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?= base_url('schedule/change_list_number') ?>" method="POST">
                        <div class="modal-body">

                            <input type="hidden" name="dariID" value="<?= $form2['idform'] ?>">
                            <div class="form-group">
                                <label>Number list now</label>
                                <input class="form-control" name="list_awal" value="<?= $form2['urutan'] ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="mould_stts">Change list to</label>
                                <select class="custom-select rounded-0" name="keID" required>
                                    <option value="">-- Select List --</option>
                                    <?php $urutan = get_list($form2['gabung']) ?>
                                    <?php foreach ($urutan as $urut) : ?>
                                        <?php if ($form2['urutan'] != $urut['urutan']) : ?>
                                            <option value="<?= $urut['idform'] . ":" . $urut['urutan'] . ":" . $urut['gabung'] ?>">List Number : <?= $urut['urutan'] ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Change List</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end modal -->

        <!-- Modal Change to data master -->
        <div class="modal fade" id="changeMaster<?= $form2['idform'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Change Data Sub to Master</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form action="<?= base_url() ?>/schedule/data_master?id=<?= $form2['idform'] . '&routecardno=' . $form2['rc_no'] . '&master=' . $form2['gabung'] ?>" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Master Date</label>
                                        <input type="text" class="form-control ml-2 mr-2 datetimepicker" name="time" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">SET TO MASTER</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
        </div>
        <!-- end modal -->
    <?php endforeach; ?>
<?php endfor; ?>

<script type="text/javascript" async>
    // JAVASCRIPT DATATABLE
    $('#tableView').DataTable({
        'dom': 'Bflrtip',
        "ordering": false,
        "searching": true,
        "lengthMenu": [
            [10, 5, 15],
            ["10", "5", "15"]
        ],
        "info": true,
        "scrollY": 1000,
        "scrollX": 1000,
        "buttons": ["excel"],
        fixedColumns: {
            left: 3,
            right: 1
        }

    });
    // ---------------------------------------------------------------------------------------------------------

    //JAVASCRIPT MENAMPILKAN DATA MOULD SIZE
    $.ajax({
        type: "get",
        url: "<?= base_url('schedule/MouldSize') ?>",
        dataType: "json",
        success: function(response) {
            if (response) {
                response.forEach(function(data) {
                    $('.mouldsize').append(`<option value="${data.mould_size}"> ${data.mould_size} </option>`);
                })
            } else {
                console.log(response)
            }
        }
    });
    //----------------------------------------------------------------------------------------------------------

    //JAVASCRIPT MENAMPILKAN DATA MOULD STATUS
    $.ajax({
        type: "get",
        url: "<?= base_url('schedule/MouldStatus') ?>",
        dataType: "json",
        success: function(response) {
            if (response) {
                response.forEach(function(data) {
                    $('.mouldstatus').append(`<option value="${data.mould_name}"> ${data.mould_name} </option>`);
                })
            } else {
                console.log(response)
            }
        }
    });
    //----------------------------------------------------------------------------------------------------------

    //JAVASCRIPT MENAMPILKAN DATA MACHINE
    $.ajax({
        type: "get",
        url: "<?= base_url('schedule/masmachine') ?>",
        dataType: "json",
        success: function(response) {
            if (response) {
                response.forEach(function(data) {
                    $('#machine').append(`<option value="${data.idmachine}">Machine : ${data.idmachine} || Desc Machine : ${data.machinedesc}</option>`);
                })
            } else {
                console.log(response)
            }
        }
    });
    //----------------------------------------------------------------------------------------------------------

    //JAVASCRIPT MENAMPILKAN DATA FG
    $.ajax({
        type: "get",
        url: "<?= base_url('schedule/masfg') ?>",
        dataType: "json",
        success: function(response) {
            if (response) {
                response.forEach(function(data) {
                    $('#fg').append(`<option value="${data.idfg}">${data.idfg} || ${data.fgcodeint}- ${data.fgcodegen} || ${data.fgweight}</option>`);
                })
            } else {
                console.log(response)
            }
        }
    });
    //----------------------------------------------------------------------------------------------------------

    //JAVASCRIPT MENAMPILKAN DATA CAV ----> (OPTION CHAINED / SELECT BERTINGKAT YANG BERHUBUNGAN DENGAN TABEL MASTER_FG)
    $('#fg').on('change', function() {
        ajaxList()
    })

    function ajaxList() {

        $("#cav").empty();
        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/cav') ?>",
            data: {
                masfg: $('#fg').val() ? $('#fg').val() : 0,
            },
            dataType: "json",
            success: function(response) {
                if (response.length >= 1) {
                    $('#cav').append(`<option value="0">-- Select Revisi No --</option>`);
                    response.forEach(function(data) {
                        $('#cav').append(
                            `<option value="${data.ups}:${data.idmaterial}:${data.stdpcs}:${data.cycletime}:${data.idxlen}:${data.revno}:${data.idmachine}">${data.idmachine} || BOM : ${data.ups} || Rev No : ${data.revno} || Material : ${data.idmaterial}</option>`);
                    })
                } else {
                    $('#cav').append(`<option value="">-- No revisi tidak ada --</option>`);
                    response.forEach(function(data) {})
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: 'No revisi pada Finishgood tidak ada!',
                        text: 'Silahkan Pilih yang lain !',
                        showConfirmButton: false,
                        timer: 1500,
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp'
                        },
                    })
                }
            }
        });
    }
    //----------------------------------------------------------------------------------------------------------

    // JAVASCRIPT MENAMPILKAN DATA CYCLE TIME DAN MATERIAL
    $('#cav').on('change', function() {
        const material = $('#cav').val().split(':');
        let ups = material[0];
        let materialid = material[1];
        let stdpcs = material[2];
        let cycletime = material[3];
        let idxlen = material[4];
        let revno = material[5];
        let machine = material[6];
        // console.log(machine);


        $('#cycletime').val("Cycle time : " + cycletime + " || " + "index len : " + idxlen)
        $('#rev').val(revno)
        // $('#machine').html(machine)

        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/material') ?>",
            data: {
                idform: materialid
            },
            dataType: "json",
            success: function(response) {
                console.log(response);
                if (response) {
                    $('#material_id').val(response.material.materialdesc)
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Material tidak ada!',
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/getmachineautoselect') ?>?machine=" + machine,
            success: function(mcn) {
                $('.machine').html(mcn)
            }
        });

    });


    //----------------------------------------------------------------------------------------------------------

    //JAVASCRIPT COPAS INPUT
    $("#rc_qty").on('change keydown paste input', function() {
        $('#bal_rc_qty').val($('#rc_qty').val())
    })

    $("#rc_qty_sampel").on('change keydown paste input', function() {
        $('#bal_rc_qty_sampel').val($('#rc_qty_sampel').val())
    })

    //----------------------------------------------------------------------------------------------------------

    //javascript pada Join Us
    $(document).ready(function() {
        let data = $('#join').val()
        if (!data) {
            document.getElementById('list-number').setAttribute('hidden', true)
            document.getElementById('list-number_sampel').setAttribute('hidden', true)
        }

        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/list_join') ?>",
            dataType: "json",
            success: function(response) {
                response.forEach(function(data) {
                    $('#join').append(`<option value="${data.idform}">${data.tgl} || ${data.mc} || ${data.idmaterial}</option>`);
                    $('#join_sampel').append(`<option value="${data.idform}">${data.tgl} || ${data.mc} || ${data.idmaterial}</option>`);
                    $('.join-sampel').append(`<option value="${data.id}">${data.tgl} || ${data.machine} || ${data.idmaterial}</option>`);
                })
            }
        });

    });
    //----------------------------------------------------------------------------------------------------------

    //javascript pada list number 
    $('#join').on('change', function() {
        $("#urutan").empty();
        let joinid = $('#join').val()
        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/list_urutan') ?>",
            data: {
                id: joinid
            },
            dataType: "json",
            success: function(response) {
                console.log(response)
                for (let i = 2; i <= 20; i++) {
                    let join = 0;
                    response.forEach(function(data) {
                        if (i == data.urutan) {
                            join = 1;
                        }
                    })
                    if (join != 1) {
                        $('#urutan').append(`<option value="${i}"> Urutan ke ${i} </option>`);
                    } else {
                        $('#urutan').append(`<option  style="background-color: #d1ff5e;" value="${i}"> Urutan ke ${i} </option>`);
                    }
                    join = 0;
                }
            }
        });
    })

    $('#join_sampel').on('change', function() {
        $("#urutan_sampel").empty();
        let joinid = $('#join_sampel').val()
        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/list_urutan') ?>",
            data: {
                id: joinid
            },
            dataType: "json",
            success: function(response) {
                console.log(response)
                for (let i = 2; i <= 20; i++) {
                    let join = 0;
                    response.forEach(function(data) {
                        if (i == data.urutan) {
                            join = 1;
                        }
                    })
                    if (join != 1) {
                        $('#urutan_sampel').append(`<option value="${i}"> Urutan ke ${i} </option>`);
                    } else {
                        $('#urutan_sampel').append(`<option  style="background-color: #d1ff5e;" value="${i}"> Urutan ke ${i} </option>`);
                    }
                    join = 0;
                }
            }
        });
    })

    $('#join').change(function() { //hiddem div startedtime & list number
        $('#startedtime').val('')
        $('#urutan').val('')
        let data = $('#join').val()
        if (!data) {
            document.getElementById('startedtime-jam').removeAttribute('hidden', true)
            document.getElementById('list-number').setAttribute('hidden', true)
        } else {
            document.getElementById('startedtime-jam').setAttribute('hidden', true)
            document.getElementById('list-number').removeAttribute('hidden', true)
        }
    })

    $('#join_sampel').change(function() { //hiddem div startedtime & list number
        $('#startedtime_sampel').val('')
        $('#urutan_sampel').val('')
        let data = $('#join_sampel').val()
        if (!data) {
            document.getElementById('startedtime-jam_sampel').removeAttribute('hidden', true)
            document.getElementById('list-number_sampel').setAttribute('hidden', true)
        } else {
            document.getElementById('startedtime-jam_sampel').setAttribute('hidden', true)
            document.getElementById('list-number_sampel').removeAttribute('hidden', true)
        }
    })
    //----------------------------------------------------------------------------------------------------------

    // javascript change mould

    function ubah(idform, machine) {
        $.ajax({
            type: "POST",
            url: "<?= base_url('schedule/edit_mold') ?>",
            data: {
                'idform': idform,
                'mc': machine
            },
            dataType: "json",
            success: function(response) {
                console.log(response);
                if (response.stts == true) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Proses Berhasil!',
                        text: `${response.msg}!`,
                        showConfirmButton: false,
                        timer: 2000,

                    })
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Proses Berhasil!',
                        text: `${response.msg}!`,
                        showConfirmButton: false,
                        timer: 2000,

                    })
                }
                location.reload()
            },
            error: function(xhr, opsi, errors) {
                console.log(xhr.status + "\n" + xhr.responseText + "\n" + errors);
            }
        });
    }
    //--------------------------------------------------------------------------------------------------------------

    //JAVASCRIPT RUN JOIN
    function runJoinReject(idform) {
        $.ajax({
            type: "post",
            url: "<?= base_url('schedule/runJoin') ?>",
            data: {
                'idform': idform,
            },
            dataType: "json",
            success: function(response) {
                console.log(idform)
                console.log(response);
                if (response.stts == true) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Proses Berhasil!',
                        text: `${response.msg}!`,
                        showConfirmButton: false,
                        timer: 2000,

                    })
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Proses Berhasil!',
                        showConfirmButton: false,
                        text: `${response.msg}!`,
                        timer: 2000,

                    })
                }
                location.reload()
            },
            error: function(xhr, opsi, errors) {
                console.log(xhr.status + "\n" + xhr.responseText + "\n" + errors);
            }
        });
    }
    //-----------------------------------------------------------------------------------------------------------
</script>
<?= $this->endSection() ?>