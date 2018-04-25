<?php
/**
 * Created by PhpStorm.
 * User: Ngoc Quy
 * Date: 4/16/2018
 * Time: 11:26 AM
 */

namespace App\Http\Controllers\User\Employee;


use App\Service\ChartService;
use Illuminate\Support\Facades\Auth;
use App\Export\InvoicesExport;
use App\Service\SearchEmployeeService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\EmployeeAddRequest;
use App\Http\Requests\EmployeeEditRequest;
use App\Models\Employee;
use App\Models\Team;
use App\Models\Role;
use App\Models\EmployeeType;
use DateTime;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use App\Service\SearchService;
use App\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    /**
     * @var SearchEmployeeServiceProvider
     */
    private $searchEmployeeService;
    protected $searchService;
    private $chartService;

    public function __construct(SearchService $searchService, SearchEmployeeService $searchEmployeeService, ChartService $chartService)
    {
        $this->searchService = $searchService;
        $this->searchEmployeeService = $searchEmployeeService;
        $this->chartService = $chartService;
    }

    public function index(Request $request)
    {
        $employees = $this->searchEmployeeService->searchEmployee($request)->get();
        $roles = Role::pluck('name','id');
        $teams = Team::pluck('name','id');
        return view('employee.list', compact('employees','roles','teams'));
    }

    public function create()
    {
        $dataTeam = Team::select('id', 'name')->get()->toArray();
        $dataRoles = Role::select('id', 'name')->get()->toArray();
        $dataEmployeeTypes = EmployeeType::select('id', 'name')->get()->toArray();
        return view('admin.module.employees.add', ['dataTeam' => $dataTeam, 'dataRoles' => $dataRoles, 'dataEmployeeTypes' => $dataEmployeeTypes]);
    }

    public function store(EmployeeAddRequest $request)
    {
        $objEmployee = Employee::select('email')->where('email', 'like', $request->email)->get()->toArray();
        $employee = new Employee;
        $employee -> email = $request -> email;
        $employee -> password = bcrypt($request -> password);
        $employee -> name = $request -> name;
        $employee -> birthday = $request -> birthday;  
        $employee -> gender = $request -> gender;
        $employee -> mobile = $request -> mobile;
        $employee -> address = $request -> address;
        $employee -> marital_status = $request -> marital_status;
        $employee -> startwork_date = $request -> startwork_date;
        $employee -> endwork_date = $request -> endwork_date;
        $employee -> is_employee = 1;
        $employee -> company = $request -> company;
        $employee -> employee_type_id = $request -> employee_type_id;
        $employee -> team_id = $request -> team_id;
        $employee -> role_id = $request -> role_id;
        $employee -> created_at = new DateTime();
        $employee -> delete_flag = 0;
        if($objEmployee != null){ 
            \Session::flash('msg_fail', 'Add failed!!! Email already exists!!!');
            return redirect('employee/create') -> with(['employee' => $employee]);
        }else{
            $employee ->save();
            \Session::flash('msg_fail', 'Account successfully created!!!');
            return redirect('employee');
        }
    }


    public function show($id, SearchRequest $request)
    {
        $data = $request->only([
                'id' => null,
                'project_name' => $request->get('project_name'),
                'role' => $request->get('role'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'project_status' => $request->get('project_status')
            ]
        );
        $data['id']=$id;

        $processes = $this->searchService->search($data)->paginate(config('settings.paginate'));

        $processes->setPath('');

        $param = (Input::except('page'));

        //set employee info
        $employee = Employee::find($id);

        $roles = Role::pluck('name', 'id');

        if (!isset($employee)) {
            return abort(404);
        }

        //set chart
        $year = date('Y');
        $listValue = $this->chartService->getListValueOfMonth($employee, $year);

        //set list years
        $listYears = $this->chartService->getListYear($employee);

        return view('employee.detail', compact('employee', 'processes' , 'listValue', 'listYears', 'roles', 'param'))->render();
    }

    public function edit($id)
    {
        $objEmployee = Employee::findOrFail($id)->toArray();
        $dataTeam = Team::select('id','name')->get()->toArray();
        $dataRoles = Role::select('id','name')->get()->toArray();
        $dataEmployeeTypes = EmployeeType::select('id','name')->get()->toArray();

        return view('admin.module.employees.edit',['objEmployee' => $objEmployee,'dataTeam' => $dataTeam, 'dataRoles' => $dataRoles, 'dataEmployeeTypes' => $dataEmployeeTypes]);
    }

    public function update(EmployeeEditRequest $request, $id)
    {
        $objEmployee = Employee::select('email')->where('email','like',$request -> email)->where('id','<>',$id)->get()->toArray();
        $pass = $request -> password;
        $employee = Employee::find($id);
        $employee -> email = $request -> email;
        if($pass != null){
            if(strlen($pass) < 6){
                return back()->with(['minPass' => 'The Password must be at least 6 characters.' , 'employee'=>$employee]);
            }else{
                $employee -> password = bcrypt($request -> password);
            }
        }  
        $employee -> name = $request -> name;
        $employee -> birthday = $request -> birthday;  
        $employee -> gender = $request -> gender;
        $employee -> mobile = $request -> mobile;
        $employee -> address = $request -> address;
        $employee -> marital_status = $request -> marital_status;
        $employee -> startwork_date = $request -> startwork_date;
        $employee -> endwork_date = $request -> endwork_date;
        $employee -> company = $request -> company;
        $employee -> employee_type_id = $request -> employee_type_id;
        $employee -> team_id = $request -> team_id;
        $employee -> role_id = $request -> role_id;
        $employee -> updated_at = new DateTime();
        if($objEmployee != null){
            \Session::flash('msg_fail', 'Edit failed!!! Email already exists!!!');
            return back()->with(['employee'=>$employee]);
            // return redirect('employee/'.$id.'/edit') -> with(['msg_fail' => 'Edit failed!!! Email already exists']);
        }else{
            $employee ->save();
            \Session::flash('msg_success', 'Account successfully edited!!!');
            return redirect('employee');    
        }
    }

    public function destroy($id, Request $request)
    {
        if ($request->ajax()) {
            $employees = Employee::where('id', $id)->where('delete_flag', 0)->first();
            $employees->delete_flag = 1;
            $employees->save();

            return response(['msg' => 'Product deleted', 'status' => 'success', 'id' => $id]);
        }
        return response(['msg' => 'Failed deleting the product', 'status' => 'failed']);
    }



    public function showChart($id, Request $request){
        $year = $request->year;
        $employee = Employee::find($id);
        $listValue = $this->chartService->getListValueOfMonth($employee, $year);
        return response(['listValue' => $listValue]);
    }


    public function  export(Request $request){
        return Excel::download(new InvoicesExport($this->searchEmployeeService, $request), 'invoices.csv');
    }
    /*
            ALL DEBUG
            echo "<pre>";
            print_r($employees);
            die;
            var_dump(): user in view;
            dd(); view array
    */
}