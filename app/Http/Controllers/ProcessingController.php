<?php

namespace App\Http\Controllers;

use App\Exports\FinalExport;
use Illuminate\Support\Facades\Auth;
use App\Models\Processing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportsExport;
use App\Models\Moderator;
use App\Models\FinalProcessing;
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
class ProcessingController extends Controller
{
    public function create(Request $request)
    {
        // Получение ID текущего аутентифицированного модератора
        $moderatorId = Auth::id();
            
        // Получение выбранного ID компании из запроса
        $companyId = $request->input('company_id');
        $inputDate = $request->input('custom_date');
        $customDate = $inputDate ? Carbon::createFromFormat('d.m.Y', $inputDate) : Carbon::now();
           
        // Получение значений из запроса
        $totalTbo = $request->input('tbo_total');
        $tpoTotal = $request->input('tpo_cement', 0) + $request->input('tpo_drevesn', 0) + $request->input('tpo_metall_m', 0) + $request->input('tpo_krishki', 0) + $request->input('tpo_meshki', 0) + $request->input('tpo_plastic', 0) + $request->input('tpo_shini', 0) + $request->input('tpo_vetosh_fi', 0) + $request->input('tpo_makul', 0) + $request->input('tpo_akkum', 0) + $request->input('tpo_tara_met', 0) + $request->input('tpo_tara_pol', 0);
        $poTotal = $request->input('po_neftesh', 0) + $request->input('po_zam_gr', 0) + $request->input('po_bur_shl', 0) + $request->input('po_obr', 0) + $request->input('po_him_reag', 0);

        $customFactorTboFood = $request->input('custom_factor_tbo_food');
        $customFactorTboPlastic = $request->input('custom_factor_tbo_plastic');
        $customFactorTboBumaga = $request->input('custom_factor_tbo_bumaga');
        $customFactorTboDerevo = $request->input('custom_factor_tbo_derevo');
        $customFactorMeshki = $request->input('custom_factor_tbo_meshki');
        $customFactorCement = $request->input('custom_factor_tpo_cement');
        $customFactorKrishki = $request->input('custom_factor_tpo_krishki');
        $customFactorShini = $request->input('custom_factor_tpo_shini');
        $customFactorVetoshFi = $request->input('custom_factor_tbo_vetoshfi');
        $customFactorAkkum = $request->input('custom_factor_tpo_akkum');
        $customFactorTpoTaraMet = $request->input('custom_factor_tpo_tara_met');
        $customFactorTpoTaraPol = $request->input('custom_factor_tpo_tara_pol');
        $customFactorNeftesh = $request->input('custom_factor_neftesh');
        $customFactorZamGr = $request->input('custom_factor_zam_gr');
        $customFactorBSH = $request->input('custom_factor_bsh');
        $customFactorOBR = $request->input('custom_factor_obr');
        $customFactorHimReag = $request->input('custom_factor_him_reag'); // Новое поле
        
        // Создание записи в таблице processing
        $processingData = [
            'moderator_id' => $moderatorId,
            'company_id' => $companyId,
            'car_num' => $request->input('car_num'),
            'driv_name' =>$request->input('driv_name'),
            'tbo_total' => $totalTbo,
            'created_at' => $customDate,
            'updated_at' => $customDate,
            'tbo_food' => $request->input('tbo_food'),
            'tbo_plastic' => $request->input('tbo_plastic'),
            'tbo_bumaga' => $request->input('tbo_bumaga'),
            'tbo_derevo' => $request->input('tbo_derevo'),
            'tbo_meshki' => $request->input('tbo_meshki'),
            'tbo_metal' => $request->input('tbo_metal'),
            'tbo_neutil' => $request->input('tbo_neutil'),
            'bsv' => $request->input('bsv'),
            'tpo_total' => $tpoTotal,
            'tpo_cement' => $request->input('tpo_cement'),
            'tpo_drevesn' => $request->input('tpo_drevesn'),
            'tpo_metall_m' => $request->input('tpo_metall_m'),
            'tpo_krishki' => $request->input('tpo_krishki'),
            'tpo_meshki' => $request->input('tpo_meshki'),
            'tpo_plastic' => $request->input('tpo_plastic'),
            'tpo_shini' => $request->input('tpo_shini'),
            'tpo_vetosh_fi' => $request->input('tpo_vetosh_fi'),
            'tpo_makul' => $request->input('tpo_makul'),
            'tpo_akkum' => $request->input('tpo_akkum'),
            'tpo_tara_met' => $request->input('tpo_tara_met'),
            'tpo_tara_pol' => $request->input('tpo_tara_pol'),
            'po_total' => $poTotal,
            'po_neftesh' => $request->input('po_neftesh'),
            'po_zam_gr' => $request->input('po_zam_gr'),
            'po_bur_shl' => $request->input('po_bur_shl'),
            'po_obr' => $request->input('po_obr'),
            'po_him_reag' => $request->input('po_him_reag'),
        ];

        $tboSum = $request->input('tbo_food', 0) + $request->input('tbo_plastic', 0) + $request->input('tbo_bumaga', 0) + $request->input('tbo_derevo', 0) + $request->input('tbo_meshki', 0) + $request->input('tbo_metal', 0) + $request->input('tbo_neutil', 0);
    
        // Проверка на совпадение сумм
        $errors = [];
        
        if ($tboSum != $totalTbo) {
            $errors[] = "ТБО Сумма не совпадает";
        }
        
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }
            
        $processing = Processing::create($processingData);
    
    // Define the waste categories and their corresponding fields and factors
    $wasteCategories = [
        'Пищевые' => ['field' => 'tbo_food', 'factor' => $customFactorTboFood ?? 1],
        'Пластик' => ['fields' => ['tbo_plastic', 'tpo_plastic'], 'factor' => $customFactorTboPlastic ?? 1],
        'Бумага и Картон' => ['fields' => ['tbo_bumaga', 'tpo_makul'], 'factor' => $customFactorTboBumaga ?? 1],
        'Дерево' => ['fields' => ['tbo_derevo', 'tpo_drevesn'], 'factor' => $customFactorTboDerevo ?? 1],
        'Мешки' => ['fields' => ['tbo_meshki', 'tpo_meshki'], 'factor' => $customFactorMeshki ?? 1],
        'Цемент' => ['field' => 'tpo_cement', 'factor' => $customFactorCement ?? 1],
        'Крышки' => ['field' => 'tpo_krishki', 'factor' => $customFactorKrishki ?? 1],
        'Шины' => ['field' => 'tpo_shini', 'factor' => $customFactorShini ?? 1],
        'Ветошь Фи' => ['field' => 'tpo_vetosh_fi', 'factor' => $customFactorVetoshFi ?? 1],
        'Аккумулятор' => ['field' => 'tpo_akkum', 'factor' => $customFactorAkkum ?? 1],
        'Тара Металлическая' => ['field' => 'tpo_tara_met', 'factor' => $customFactorTpoTaraMet ?? 1],
        'Тара Полимерная' => ['field' => 'tpo_tara_pol', 'factor' => $customFactorTpoTaraPol ?? 1],
        'Нефтеш' => ['field' => 'po_neftesh', 'factor' => $customFactorNeftesh ?? 1, 'type' => 'Переработано'],
        'Зам Гр' => ['field' => 'po_zam_gr', 'factor' => $customFactorZamGr ?? 1, 'type' => 'Переработано'],
        'БШ' => ['field' => 'po_bur_shl', 'factor' => $customFactorBSH ?? 1, 'type' => 'Переработано'],
        'ОБР' => ['field' => 'po_obr', 'factor' => $customFactorOBR ?? 1, 'type' => 'Переработано'],
        'Химические Реагенты' => ['field' => 'po_him_reag', 'factor' => $customFactorHimReag ?? 1],
    ];
        
    $finalProcessingRecords = [];
        
    // Create FinalProcessing records for each waste category
    foreach ($wasteCategories as $name => $category) {
        $value = 0;
        if (isset($category['fields'])) {
            foreach ($category['fields'] as $field) {
                if ($processing->$field !== null) {
                    $value += $processing->$field;
                }
            }
            $value *= $category['factor']; // Apply the appropriate factor for combined values
        } else {
            $fieldValue = $processing->{$category['field']};
            if ($fieldValue !== null) {
                $value = $fieldValue * $category['factor']; // Apply the factor for individual values
            }
        }
    
        // Only add records with non-zero values
        if ($value > 0) {
            $typeOperation = isset($category['type']) ? $category['type'] : 'Передано'; // Use the specified 'type' if present, otherwise 'Передано'
    
            $finalProcessingRecords[] = [
                'kod_othoda' => 0,
                'name_othod' => $name,
                'company_id' => $companyId,
                'value' => $value,
                'type_operation' => $typeOperation,
                'created_at' => $customDate,
                'updated_at' => $customDate,
            ];
        }
    }
    
    FinalProcessing::insert($finalProcessingRecords);
    
    // Return the created records as a JSON response
    return response()->json([
        'processing' => $processing,
        'final_processing' => $finalProcessingRecords,
    ], 201);
}


    public function getByDateRange(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $moderatorId = Auth::id();
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');   
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
    
        $reports = Processing::with('company')
        ->select(
            'company.name as company_name',
            DB::raw('SUM(tbo_total) as tbo_total'),
            DB::raw('SUM(tbo_food) as tbo_food'),
            DB::raw('SUM(tbo_plastic) as tbo_plastic'),
            DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
            DB::raw('SUM(tbo_derevo) as tbo_derevo'),
            DB::raw('SUM(tbo_meshki) as tbo_meshki'),
            DB::raw('SUM(tbo_metal) as tbo_metal'),
            DB::raw('SUM(tbo_neutil) as tbo_neutil'),
            DB::raw('SUM(bsv) as bsv'),
            DB::raw('SUM(tpo_total) as tpo_total'),
            DB::raw('SUM(tpo_cement) as tpo_cement'),
            DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
            DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
            DB::raw('SUM(tpo_krishki) as tpo_krishki'),
            DB::raw('SUM(tpo_meshki) as tpo_meshki'),
            DB::raw('SUM(tpo_plastic) as tpo_plastic'),
            DB::raw('SUM(tpo_shini) as tpo_shini'),
            DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
            DB::raw('SUM(tpo_makul) as tpo_makul'),
            DB::raw('SUM(tpo_akkum) as tpo_akkum'),
            DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
            DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
            DB::raw('SUM(po_total) as po_total'),
            DB::raw('SUM(po_neftesh) as po_neftesh'),
            DB::raw('SUM(po_zam_gr) as po_zam_gr'),
            DB::raw('SUM(po_bur_shl) as po_bur_shl'),
            DB::raw('SUM(po_obr) as po_obr'),
            DB::raw('SUM(po_him_reag) as po_him_reag'))
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.moderator_id', $moderatorId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
            $totals = [
                'company_name' => 'Итого:',
                'tbo_total' => $reports->sum('tbo_total'),
                'tbo_food' => $reports->sum('tbo_food'),
                'tbo_plastic' => $reports->sum('tbo_plastic'),
                'tbo_bumaga' => $reports->sum('tbo_bumaga'),
                'tbo_derevo' => $reports->sum('tbo_derevo'),
                'tbo_meshki' => $reports->sum('tbo_meshki'),
                'tbo_metal' => $reports->sum('tbo_metal'),
                'tbo_neutil' => $reports->sum('tbo_neutil'),
                'bsv' => $reports->sum('bsv'),
                'tpo_total' => $reports->sum('tpo_total'),
                'tpo_cement' => $reports->sum('tpo_cement'),
                'tpo_drevesn' => $reports->sum('tpo_drevesn'),
                'tpo_metall_m' => $reports->sum('tpo_metall_m'),
                'tpo_krishki' => $reports->sum('tpo_krishki'),
                'tpo_meshki' => $reports->sum('tpo_meshki'),
                'tpo_plastic' => $reports->sum('tpo_plastic'),
                'tpo_shini' => $reports->sum('tpo_shini'),
                'tpo_vetosh_fi' => $reports->sum('tpo_vetosh_fi'),
                'tpo_makul' => $reports->sum('tpo_makul'),
                'tpo_akkum' => $reports->sum('tpo_akkum'),
                'tpo_tara_met' => $reports->sum('tpo_tara_met'),
                'tpo_tara_pol' => $reports->sum('tpo_tara_pol'),
                'po_total' => $reports->sum('po_total'),
                'po_neftesh' => $reports->sum('po_neftesh'),
                'po_zam_gr' => $reports->sum('po_zam_gr'),
                'po_bur_shl' => $reports->sum('po_bur_shl'),
                'po_obr' => $reports->sum('po_obr'),
                'po_him_reag' => $reports->sum('po_him_reag')
            ];
            
            // Добавляем итоговую строку в массив данных
            $reports->push($totals);
        
    
        return response()->json($reports);
    }

    public function exportByMonth(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $moderatorId = Auth::id();
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');    
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
        
        $reports = Processing::with('company')
        ->select(
            'company.name as company_name',
            DB::raw('SUM(tbo_total) as tbo_total'),
            DB::raw('SUM(tbo_food) as tbo_food'),
            DB::raw('SUM(tbo_plastic) as tbo_plastic'),
            DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
            DB::raw('SUM(tbo_derevo) as tbo_derevo'),
            DB::raw('SUM(tbo_meshki) as tbo_meshki'),
            DB::raw('SUM(tbo_metal) as tbo_metal'),
            DB::raw('SUM(tbo_neutil) as tbo_neutil'),
            DB::raw('SUM(bsv) as bsv'),
            DB::raw('SUM(tpo_total) as tpo_total'),
            DB::raw('SUM(tpo_cement) as tpo_cement'),
            DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
            DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
            DB::raw('SUM(tpo_krishki) as tpo_krishki'),
            DB::raw('SUM(tpo_meshki) as tpo_meshki'),
            DB::raw('SUM(tpo_plastic) as tpo_plastic'),
            DB::raw('SUM(tpo_shini) as tpo_shini'),
            DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
            DB::raw('SUM(tpo_makul) as tpo_makul'),
            DB::raw('SUM(tpo_akkum) as tpo_akkum'),
            DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
            DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
            DB::raw('SUM(po_total) as po_total'),
            DB::raw('SUM(po_neftesh) as po_neftesh'),
            DB::raw('SUM(po_zam_gr) as po_zam_gr'),
            DB::raw('SUM(po_bur_shl) as po_bur_shl'),
            DB::raw('SUM(po_obr) as po_obr'),
            DB::raw('SUM(po_him_reag) as po_him_reag'))
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.moderator_id', $moderatorId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
            $totals = [
                'company_name' => 'Итого:',
                'tbo_total' => $reports->sum('tbo_total'),
                'tbo_food' => $reports->sum('tbo_food'),
                'tbo_plastic' => $reports->sum('tbo_plastic'),
                'tbo_bumaga' => $reports->sum('tbo_bumaga'),
                'tbo_derevo' => $reports->sum('tbo_derevo'),
                'tbo_meshki' => $reports->sum('tbo_meshki'),
                'tbo_metal' => $reports->sum('tbo_metal'),
                'tbo_neutil' => $reports->sum('tbo_neutil'),
                'bsv' => $reports->sum('bsv'),
                'tpo_total' => $reports->sum('tpo_total'),
                'tpo_cement' => $reports->sum('tpo_cement'),
                'tpo_drevesn' => $reports->sum('tpo_drevesn'),
                'tpo_metall_m' => $reports->sum('tpo_metall_m'),
                'tpo_krishki' => $reports->sum('tpo_krishki'),
                'tpo_meshki' => $reports->sum('tpo_meshki'),
                'tpo_plastic' => $reports->sum('tpo_plastic'),
                'tpo_shini' => $reports->sum('tpo_shini'),
                'tpo_vetosh_fi' => $reports->sum('tpo_vetosh_fi'),
                'tpo_makul' => $reports->sum('tpo_makul'),
                'tpo_akkum' => $reports->sum('tpo_akkum'),
                'tpo_tara_met' => $reports->sum('tpo_tara_met'),
                'tpo_tara_pol' => $reports->sum('tpo_tara_pol'),
                'po_total' => $reports->sum('po_total'),
                'po_neftesh' => $reports->sum('po_neftesh'),
                'po_zam_gr' => $reports->sum('po_zam_gr'),
                'po_bur_shl' => $reports->sum('po_bur_shl'),
                'po_obr' => $reports->sum('po_obr'),
                'po_him_reag' => $reports->sum('po_him_reag')
            ];
            
            // Добавляем итоговую строку в массив данных
            $reports->push($totals);    
    
        $export = new ReportsExport($reports);
    
        return Excel::download($export, 'reports.xlsx');
    }

    public function getByDateAdmin(Request $request)
    {
        $dateString = $request->input('date');
        $moderatorId = $request->input('moderator_id');
        $date = date_create_from_format('d.m.Y', $dateString);
        $formattedDate = $date->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');
    
        $reports = Processing::with('company')
            ->select(
                'company.name as company_name',
                'processing.*'
            )
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.moderator_id', $moderatorId)
            ->whereDate('processing.created_at', $formattedDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->get();
    
        return response()->json($reports);
    }
    public function getByDateRangeAdmin(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $moderatorId = $request->input('moderator_id');
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');
    
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
    
        $reports = Processing::with('company')
            ->select(
                'company.name as company_name',
                DB::raw('SUM(tbo_total) as tbo_total'),
                DB::raw('SUM(tbo_food) as tbo_food'),
                DB::raw('SUM(tbo_plastic) as tbo_plastic'),
                DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
                DB::raw('SUM(tbo_derevo) as tbo_derevo'),
                DB::raw('SUM(tbo_meshki) as tbo_meshki'),
                DB::raw('SUM(tbo_metal) as tbo_metal'),
                DB::raw('SUM(tbo_neutil) as tbo_neutil'),
                DB::raw('SUM(bsv) as bsv'),
                DB::raw('SUM(tpo_total) as tpo_total'),
                DB::raw('SUM(tpo_cement) as tpo_cement'),
                DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
                DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
                DB::raw('SUM(tpo_krishki) as tpo_krishki'),
                DB::raw('SUM(tpo_meshki) as tpo_meshki'),
                DB::raw('SUM(tpo_plastic) as tpo_plastic'),
                DB::raw('SUM(tpo_shini) as tpo_shini'),
                DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
                DB::raw('SUM(tpo_makul) as tpo_makul'),
                DB::raw('SUM(tpo_akkum) as tpo_akkum'),
                DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
                DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
                DB::raw('SUM(po_total) as po_total'),
                DB::raw('SUM(po_neftesh) as po_neftesh'),
                DB::raw('SUM(po_zam_gr) as po_zam_gr'),
                DB::raw('SUM(po_bur_shl) as po_bur_shl'),
                DB::raw('SUM(po_obr) as po_obr'),
                DB::raw('SUM(po_him_reag) as po_him_reag')
            )
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.moderator_id', $moderatorId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
$totals = [
    'company_name' => 'Итого:',
    'tbo_total' => $reports->sum('tbo_total'),
    'tbo_food' => $reports->sum('tbo_food'),
    'tbo_plastic' => $reports->sum('tbo_plastic'),
    'tbo_bumaga' => $reports->sum('tbo_bumaga'),
    'tbo_derevo' => $reports->sum('tbo_derevo'),
    'tbo_meshki' => $reports->sum('tbo_meshki'),
    'tbo_metal' => $reports->sum('tbo_metal'),
    'tbo_neutil' => $reports->sum('tbo_neutil'),
    'bsv' => $reports->sum('bsv'),
    'tpo_total' => $reports->sum('tpo_total'),
    'tpo_cement' => $reports->sum('tpo_cement'),
    'tpo_drevesn' => $reports->sum('tpo_drevesn'),
    'tpo_metall_m' => $reports->sum('tpo_metall_m'),
    'tpo_krishki' => $reports->sum('tpo_krishki'),
    'tpo_meshki' => $reports->sum('tpo_meshki'),
    'tpo_plastic' => $reports->sum('tpo_plastic'),
    'tpo_shini' => $reports->sum('tpo_shini'),
    'tpo_vetosh_fi' => $reports->sum('tpo_vetosh_fi'),
    'tpo_makul' => $reports->sum('tpo_makul'),
    'tpo_akkum' => $reports->sum('tpo_akkum'),
    'tpo_tara_met' => $reports->sum('tpo_tara_met'),
    'tpo_tara_pol' => $reports->sum('tpo_tara_pol'),
    'po_total' => $reports->sum('po_total'),
    'po_neftesh' => $reports->sum('po_neftesh'),
    'po_zam_gr' => $reports->sum('po_zam_gr'),
    'po_bur_shl' => $reports->sum('po_bur_shl'),
    'po_obr' => $reports->sum('po_obr'),
    'po_him_reag' => $reports->sum('po_him_reag')
];

// Добавляем итоговую строку в массив данных
$reports->push($totals);

return response()->json($reports);
    }


    public function exportByAdmin(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $moderatorId = $request->input('moderator_id');
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');
    
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
    
        $reports = Processing::with('company')
        ->select(
            'company.name as company_name',
            DB::raw('SUM(tbo_total) as tbo_total'),
            DB::raw('SUM(tbo_food) as tbo_food'),
            DB::raw('SUM(tbo_plastic) as tbo_plastic'),
            DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
            DB::raw('SUM(tbo_derevo) as tbo_derevo'),
            DB::raw('SUM(tbo_meshki) as tbo_meshki'),
            DB::raw('SUM(tbo_metal) as tbo_metal'),
            DB::raw('SUM(tbo_neutil) as tbo_neutil'),
            DB::raw('SUM(bsv) as bsv'),
            DB::raw('SUM(tpo_total) as tpo_total'),
            DB::raw('SUM(tpo_cement) as tpo_cement'),
            DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
            DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
            DB::raw('SUM(tpo_krishki) as tpo_krishki'),
            DB::raw('SUM(tpo_meshki) as tpo_meshki'),
            DB::raw('SUM(tpo_plastic) as tpo_plastic'),
            DB::raw('SUM(tpo_shini) as tpo_shini'),
            DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
            DB::raw('SUM(tpo_makul) as tpo_makul'),
            DB::raw('SUM(tpo_akkum) as tpo_akkum'),
            DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
            DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
            DB::raw('SUM(po_total) as po_total'),
            DB::raw('SUM(po_neftesh) as po_neftesh'),
            DB::raw('SUM(po_zam_gr) as po_zam_gr'),
            DB::raw('SUM(po_bur_shl) as po_bur_shl'),
            DB::raw('SUM(po_obr) as po_obr'),
            DB::raw('SUM(po_him_reag) as po_him_reag'))
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.moderator_id', $moderatorId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
            $totals = [
                'company_name' => 'Итого:',
                'tbo_total' => $reports->sum('tbo_total'),
                'tbo_food' => $reports->sum('tbo_food'),
                'tbo_plastic' => $reports->sum('tbo_plastic'),
                'tbo_bumaga' => $reports->sum('tbo_bumaga'),
                'tbo_derevo' => $reports->sum('tbo_derevo'),
                'tbo_meshki' => $reports->sum('tbo_meshki'),
                'tbo_metal' => $reports->sum('tbo_metal'),
                'tbo_neutil' => $reports->sum('tbo_neutil'),
                'bsv' => $reports->sum('bsv'),
                'tpo_total' => $reports->sum('tpo_total'),
                'tpo_cement' => $reports->sum('tpo_cement'),
                'tpo_drevesn' => $reports->sum('tpo_drevesn'),
                'tpo_metall_m' => $reports->sum('tpo_metall_m'),
                'tpo_krishki' => $reports->sum('tpo_krishki'),
                'tpo_meshki' => $reports->sum('tpo_meshki'),
                'tpo_plastic' => $reports->sum('tpo_plastic'),
                'tpo_shini' => $reports->sum('tpo_shini'),
                'tpo_vetosh_fi' => $reports->sum('tpo_vetosh_fi'),
                'tpo_makul' => $reports->sum('tpo_makul'),
                'tpo_akkum' => $reports->sum('tpo_akkum'),
                'tpo_tara_met' => $reports->sum('tpo_tara_met'),
                'tpo_tara_pol' => $reports->sum('tpo_tara_pol'),
                'po_total' => $reports->sum('po_total'),
                'po_neftesh' => $reports->sum('po_neftesh'),
                'po_zam_gr' => $reports->sum('po_zam_gr'),
                'po_bur_shl' => $reports->sum('po_bur_shl'),
                'po_obr' => $reports->sum('po_obr'),
                'po_him_reag' => $reports->sum('po_him_reag')
            ];
            
            // Добавляем итоговую строку в массив данных
            $reports->push($totals);
        $export = new ReportsExport($reports);
    
        return Excel::download($export, 'reports.xlsx');
    }


    public function getByDateRangeCompany(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $companyId = Auth::id();
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');   
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
    
        $reports = Processing::with('company')
        ->select(
            'company.name as company_name',
            DB::raw('SUM(tbo_total) as tbo_total'),
            DB::raw('SUM(tbo_food) as tbo_food'),
            DB::raw('SUM(tbo_plastic) as tbo_plastic'),
            DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
            DB::raw('SUM(tbo_derevo) as tbo_derevo'),
            DB::raw('SUM(tbo_meshki) as tbo_meshki'),
            DB::raw('SUM(tbo_metal) as tbo_metal'),
            DB::raw('SUM(tbo_neutil) as tbo_neutil'),
            DB::raw('SUM(bsv) as bsv'),
            DB::raw('SUM(tpo_total) as tpo_total'),
            DB::raw('SUM(tpo_cement) as tpo_cement'),
            DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
            DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
            DB::raw('SUM(tpo_krishki) as tpo_krishki'),
            DB::raw('SUM(tpo_meshki) as tpo_meshki'),
            DB::raw('SUM(tpo_plastic) as tpo_plastic'),
            DB::raw('SUM(tpo_shini) as tpo_shini'),
            DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
            DB::raw('SUM(tpo_makul) as tpo_makul'),
            DB::raw('SUM(tpo_akkum) as tpo_akkum'),
            DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
            DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
            DB::raw('SUM(po_total) as po_total'),
            DB::raw('SUM(po_neftesh) as po_neftesh'),
            DB::raw('SUM(po_zam_gr) as po_zam_gr'),
            DB::raw('SUM(po_bur_shl) as po_bur_shl'),
            DB::raw('SUM(po_obr) as po_obr'),
            DB::raw('SUM(po_him_reag) as po_him_reag'))
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.company_id', $companyId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
    
        return response()->json($reports);
    }


    public function exportByMonthCompany(Request $request)
    {
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        $companyId = Auth::id();
        $startDate = date_create_from_format('d.m.Y', $startDateString);
        $formattedStartDate = $startDate->format('Y-m-d');
        $carNum = $request->input('car_num');
        $driveName = $request->input('driv_name');    
        // Преобразование конечной даты
        $endDate = date_create_from_format('d.m.Y', $endDateString);
        $formattedEndDate = $endDate->format('Y-m-d');
        
        $reports = Processing::with('company')
        ->select(
            'company.name as company_name',
            DB::raw('SUM(tbo_total) as tbo_total'),
            DB::raw('SUM(tbo_food) as tbo_food'),
            DB::raw('SUM(tbo_plastic) as tbo_plastic'),
            DB::raw('SUM(tbo_bumaga) as tbo_bumaga'),
            DB::raw('SUM(tbo_derevo) as tbo_derevo'),
            DB::raw('SUM(tbo_meshki) as tbo_meshki'),
            DB::raw('SUM(tbo_metal) as tbo_metal'),
            DB::raw('SUM(tbo_neutil) as tbo_neutil'),
            DB::raw('SUM(bsv) as bsv'),
            DB::raw('SUM(tpo_total) as tpo_total'),
            DB::raw('SUM(tpo_cement) as tpo_cement'),
            DB::raw('SUM(tpo_drevesn) as tpo_drevesn'),
            DB::raw('SUM(tpo_metall_m) as tpo_metall_m'),
            DB::raw('SUM(tpo_krishki) as tpo_krishki'),
            DB::raw('SUM(tpo_meshki) as tpo_meshki'),
            DB::raw('SUM(tpo_plastic) as tpo_plastic'),
            DB::raw('SUM(tpo_shini) as tpo_shini'),
            DB::raw('SUM(tpo_vetosh_fi) as tpo_vetosh_fi'),
            DB::raw('SUM(tpo_makul) as tpo_makul'),
            DB::raw('SUM(tpo_akkum) as tpo_akkum'),
            DB::raw('SUM(tpo_tara_met) as tpo_tara_met'),
            DB::raw('SUM(tpo_tara_pol) as tpo_tara_pol'),
            DB::raw('SUM(po_total) as po_total'),
            DB::raw('SUM(po_neftesh) as po_neftesh'),
            DB::raw('SUM(po_zam_gr) as po_zam_gr'),
            DB::raw('SUM(po_bur_shl) as po_bur_shl'),
            DB::raw('SUM(po_obr) as po_obr'),
            DB::raw('SUM(po_him_reag) as po_him_reag'))
            ->join('company', 'processing.company_id', '=', 'company.id')
            ->where('processing.company_id', $companyId)
            ->whereDate('processing.created_at', '>=', $formattedStartDate)
            ->whereDate('processing.created_at', '<=', $formattedEndDate)
            ->when($carNum, function ($query, $carNum) {
                return $query->where('processing.car_num', $carNum);
            })
            ->when($driveName, function ($query, $driveName) {
                return $query->where('processing.driv_name', $driveName);
            })
            ->groupBy('processing.company_id', 'company.name')
            ->get();
    
        $export = new ReportsExport($reports);
    
        return Excel::download($export, 'reports.xlsx');
    }

    public function updateById(Request $request, $id)
{
    $data = $request->all();
    
    $processing = Processing::find($id);
    
    if (!$processing) {
        return response()->json(['error' => 'Report not found'], 404);
    }
    $processing->update($data);
    
    return response()->json($processing);
}


public function getById($id)
{
    $processing = Processing::find($id);
    
    if (!$processing) {
        return response()->json(['error' => 'Report not found'], 404);
    }
    
    return response()->json($processing);
}

public function updateByIdModerator(Request $request, $id)
{
    $data = $request->all();
    
    $company = Moderator::find($id);
    
    if (!$company) {
        return response()->json(['error' => 'Company not found'], 404);
    }
    
    $company->fill($data);
    
    // Update the password if provided
    if (isset($data['password'])) {
        $company->password = bcrypt($data['password']);
    }
    
    $company->save();
    
    return response()->json($company);
}

public function getByModerator($id)
{
$processing = Moderator::find($id);

if (!$processing) {
    return response()->json(['error' => 'Report not found'], 404);
}

return response()->json($processing);
}

public function getFinalProcessingData(Request $request)
{
    $startDateString = $request->input('start_date');
    $startDate = date_create_from_format('d.m.Y', $startDateString);
    $formattedStartDate = $startDate->format('Y-m-d');


    $endDateString = $request->input('end_date');
    $endDate = date_create_from_format('d.m.Y', $endDateString);
    $formattedEndDate = $endDate->format('Y-m-d');
    // List of waste categories in the desired order
    $wasteCategoriesOrder = [
        'Пищевые',
        'Пластик',
        'Бумага и Картон',
        'Дерево',
        'Мешки',
        'Цемент',
        'Крышки',
        'Шины',
        'Ветошь Фи',
        'Аккумулятор',
        'Тара Металлическая',
        'Тара Полимерная',
        'Нефтеш',
        'Зам Гр',
        'БШ',
        'ОБР',
        'Химические Реагенты',
    ];

    // Get the final processing records from the database with bin_company and type_operation
    $finalProcessingRecords = DB::table('final_processing')
        ->select('name_othod', 'company.bin_company', 'type_operation', DB::raw('SUM(value) as total_value'))
        ->join('company', 'final_processing.company_id', '=', 'company.id')
        ->whereBetween('final_processing.created_at', ["$formattedStartDate 00:00:00", "$formattedEndDate 23:59:59"])
        ->groupBy('name_othod', 'company.bin_company', 'type_operation')
        ->orderByRaw("FIELD(name_othod, '" . implode("','", $wasteCategoriesOrder) . "'), type_operation")
        ->get();

    $finalProcessingRecordsWithTotals = [];
    $currentCategory = null;
    $currentTypeOperation = null;
    $categoryTotal = 0;

    foreach ($finalProcessingRecords as $record) {
        if ($currentCategory !== $record->name_othod || $currentTypeOperation !== $record->type_operation) {
            if ($currentCategory !== null) {
                $categoryTotalRecord = [
                    'name_othod' => 'Итого',
                    'bin_company' => '', // Оставляем пустое поле для итогов
                    'type_operation' => $currentTypeOperation,
                    'total_value' => $categoryTotal,
                ];
                $finalProcessingRecordsWithTotals[] = (object)$categoryTotalRecord;
            }

            $currentCategory = $record->name_othod;
            $currentTypeOperation = $record->type_operation;
            $categoryTotal = 0;
        }

        $categoryTotal += $record->total_value;
        $finalProcessingRecordsWithTotals[] = $record;
    }

    if ($currentCategory !== null) {
        $categoryTotalRecord = [
            'name_othod' => 'Итого',
            'bin_company' => '', // Оставляем пустое поле для итогов
            'type_operation' => $currentTypeOperation,
            'total_value' => $categoryTotal,
        ];
        $finalProcessingRecordsWithTotals[] = (object)$categoryTotalRecord;
    }

    return response()->json($finalProcessingRecordsWithTotals, 200);
}


public function exportFinalProcessingData(Request $request)
{
    $startDateString = $request->input('start_date');
    $startDate = date_create_from_format('d.m.Y', $startDateString);
    $formattedStartDate = $startDate->format('Y-m-d');


    $endDateString = $request->input('end_date');
    $endDate = date_create_from_format('d.m.Y', $endDateString);
    $formattedEndDate = $endDate->format('Y-m-d');
    // List of waste categories in the desired order
    $wasteCategoriesOrder = [
        'Пищевые',
        'Пластик',
        'Бумага и Картон',
        'Дерево',
        'Мешки',
        'Цемент',
        'Крышки',
        'Шины',
        'Ветошь Фи',
        'Аккумулятор',
        'Тара Металлическая',
        'Тара Полимерная',
        'Нефтеш',
        'Зам Гр',
        'БШ',
        'ОБР',
        'Химические Реагенты',
    ];

    // Get the final processing records from the database with bin_company and type_operation
    $finalProcessingRecords = DB::table('final_processing')
        ->select('name_othod', 'company.bin_company', 'type_operation', DB::raw('SUM(value) as total_value'))
        ->join('company', 'final_processing.company_id', '=', 'company.id')
        ->whereBetween('final_processing.created_at', ["$formattedStartDate 00:00:00", "$formattedEndDate 23:59:59"])
        ->groupBy('name_othod', 'company.bin_company', 'type_operation')
        ->orderByRaw("FIELD(name_othod, '" . implode("','", $wasteCategoriesOrder) . "'), type_operation")
        ->get();

    $export = new FinalExport($finalProcessingRecords);

    return Excel::download($export, 'reports.xlsx');
}


public function getByDateAdminFinal(Request $request)
{
    $dateString = $request->input('date');
    $date = date_create_from_format('d.m.Y', $dateString);
    $formattedDate = $date->format('Y-m-d');

    $reports = FinalProcessing::with('company')
        ->select('final_processing.id', 'company.bin_company as bin_company',  'name_othod', 'value', 'type_operation')
        ->join('company', 'final_processing.company_id', '=', 'company.id')
        ->get();

    $reports->transform(function ($item) {
        unset($item->company);
        return $item;
    });

    return response()->json($reports);
}

public function getByIdFinal($id)
{
    $processing = FinalProcessing::find($id);
    
    if (!$processing) {
        return response()->json(['error' => 'Report not found'], 404);
    }
    
    return response()->json($processing);
}


public function updateByIdFinal(Request $request, $id)
{
    $data = $request->all();
    $data['value'] = (double) $data['value'];

    $result = DB::table('final_processing')->where('id', $id)->update($data);

    if (!$result) {
        return response()->json(['error' => 'Update failed'], 400);
    }

    $processing = FinalProcessing::find($id);

    return response()->json($processing);
}
    public function DeleteByRangeFinal(Request $request)
{
    $startDateString = $request->input('start_date');
    $startDate = date_create_from_format('d.m.Y', $startDateString);
    $formattedStartDate = $startDate->format('Y-m-d');

    $endDateString = $request->input('end_date');
    $endDate = date_create_from_format('d.m.Y', $endDateString);
    $formattedEndDate = $endDate->format('Y-m-d');

    // Здесь предполагается, что ваша модель имеет поле 'date' для сравнения с датами.
    // Замените 'date' на имя поля, которое хранит даты в вашей модели.
    FinalProcessing::whereBetween('final_processing.created_at', [$formattedStartDate, $formattedEndDate])->delete();

    return response()->json(['message' => 'Записи успешно удалены']);
}

public function DeleteByRangeProcessing(Request $request)
{
    $startDateString = $request->input('start_date');
    $startDate = date_create_from_format('d.m.Y', $startDateString);
    $formattedStartDate = $startDate->format('Y-m-d');

    $endDateString = $request->input('end_date');
    $endDate = date_create_from_format('d.m.Y', $endDateString);
    $formattedEndDate = $endDate->format('Y-m-d');

    // Здесь предполагается, что ваша модель имеет поле 'date' для сравнения с датами.
    // Замените 'date' на имя поля, которое хранит даты в вашей модели.
    Processing::whereBetween('processing.created_at', [$formattedStartDate, $formattedEndDate])->delete();

    return response()->json(['message' => 'Записи успешно удалены']);
}
};

