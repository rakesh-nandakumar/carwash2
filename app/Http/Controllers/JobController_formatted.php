<?php namespace App\Http\Controllers
; use App\Models\{Job,Customer,Vehicle,Service,Product,JobService as JS,JobPart}
; use App\Services\{JobService,InventoryService,CommunicationService,ApprovalService,PricingService}
; use App\Enums\JobStatus
; use Illuminate\Http\Request
;
class JobController extends Controller {public function __construct(private JobService $jobs,private InventoryService $inventory,private CommunicationService $communication,private ApprovalService $approvals,private PricingService $pricing,private \App\Services\InvoiceService $invoicing){} public function index(){ $jobs=Job::with(['customer','vehicle','technician'])->latest()->paginate(20)
;return view('jobs.index',compact('jobs'))
;} public function board(){ $kanbanColumns=[['id'=>JobStatus::WAITING_FOR_CHECKIN->value,'title'=>'Waiting for Check-in','color'=>'yellow'],['id'=>JobStatus::CHECKED_IN->value,'title'=>'Checked In','color'=>'blue'],['id'=>JobStatus::INSPECTION_PENDING->value,'title'=>'Inspection Pending','color'=>'yellow'],['id'=>JobStatus::INSPECTION_COMPLETED->value,'title'=>'Inspection Completed','color'=>'purple'],['id'=>JobStatus::CUSTOMER_APPROVAL_PENDING->value,'title'=>'Waiting Approval','color'=>'yellow'],['id'=>JobStatus::APPROVED->value,'title'=>'Approved','color'=>'blue'],['id'=>JobStatus::WAITING_FOR_PARTS->value,'title'=>'Waiting for Parts','color'=>'orange'],['id'=>JobStatus::IN_SERVICE->value,'title'=>'In Service','color'=>'blue'],['id'=>JobStatus::QUALITY_CHECK->value,'title'=>'Quality Check','color'=>'purple'],['id'=>JobStatus::READY_FOR_PAYMENT->value,'title'=>'Ready for Payment','color'=>'cyan']]
;$statuses=collect($kanbanColumns)->pluck('id')->toArray()
;$jobs=Job::with(['customer','vehicle','technician','branch'])->whereIn('status',$statuses)->where('status','!=',JobStatus::DELIVERED->value)->where('status','!=',JobStatus::CANCELLED->value)->get()->groupBy('status')
;$branchId=auth()->user()->branch_id
;if($branchId){$jobs=$jobs->map(function($group)use($branchId){return $group->where('branch_id',$branchId)
;})
;}if(request()->ajax()){return view('jobs.board',compact('jobs','kanbanColumns'))
;}return view('jobs.board',compact('jobs','kanbanColumns'))
;} public function create(){return view('jobs.create',['customers'=>Customer::orderBy('full_name')->get(),'vehicles'=>Vehicle::select('id','registration_number','make','model','customer_id')->orderBy('registration_number')->get(),'services'=>Service::where('active',true)->orderBy('name')->get()])
;} public function store(Request $r){$d=$r->validate(['customer_id'=>'required','vehicle_id'=>'required','priority'=>'required','customer_complaint'=>'nullable','service_ids'=>'array'])
;$serviceIds=$d['service_ids']??[]
;unset($d['service_ids'])
;$d['business_id']=auth()->user()->business_id
;$d['branch_id']=auth()->user()->branch_id
;$d['status']=JobStatus::WAITING_FOR_CHECKIN->value
;$job=$this->jobs->create($d)
;foreach($serviceIds as $serviceId){$service=Service::find($serviceId)
;JS::create(['job_id'=>$job->id,'service_id'=>$serviceId,'name_snapshot'=>$service->name,'unit_price'=>$service->base_price,'quantity'=>1,'approval_status'=>'approved'])
;}$this->communication->notifyVehicleReceived($job)
;return redirect()->route('jobs.show',$job)->with('success','Job Card created.')
;} public function show(Job $job){$job->load(['customer','vehicle','services.service','parts.product','inspection','invoice','statusHistory','additionalWorkRequests'])
;$services=Service::where('active',true)->get()
;$products=Product::where('active',true)->get()
;$pendingApprovals=$this->approvals->getPendingApprovals($job->id)
;$calculation=$this->pricing->calculateFinalInvoice($job->id)
;return view('jobs.show',compact('job','services','products','pendingApprovals','calculation'))
;} public function edit(Job $job){return redirect()->route('jobs.show',$job)
;} public function update(Request $r,Job $job){$job->update($r->validate(['priority'=>'required','customer_complaint'=>'nullable','notes'=>'nullable','technician_id'=>'nullable']))
;return redirect()->route('jobs.show',$job)->with('success','Job updated.')
;} public function status(Request $r,Job $job){$r->validate(['status'=>'required|in:'.implode(',',array_column(JobStatus::cases(),'value')),'reason'=>'nullable'])
;$newStatus=JobStatus::from($r->status)
;if(!$job->status->canTransitionTo($newStatus)){return back()->withErrors(['status'=>'Invalid status transition from '.$job->status->value.' to '.$newStatus->value])
;}$job->transitionTo($newStatus,auth()->user(),$r->reason)
;if($newStatus===JobStatus::READY_FOR_PAYMENT){$this->invoicing->generate($job->id)
;}if($newStatus===JobStatus::PAID && $job->invoice){$this->communication->notifyPaymentReceived($job,$job->invoice->total)
;}return back()->with('success','Job status updated.')
;} public function additionalWork(Request $r,Job $job){$d=$r->validate(['title'=>'required','description'=>'required','estimated_cost'=>'required|numeric'])
;$this->approvals->requestAdditionalWork($job->id,$d)
;return back()->with('success','Additional work requested.')
;} public function approve(Request $r,Job $job){$job->transitionTo(JobStatus::APPROVED,auth()->user())
;return back()->with('success','Job approved.')
;} public function consumePart(Request $r,Job $job){$d=$r->validate(['product_id'=>'required','quantity'=>'required|numeric|min:0.001'])
;$product=Product::find($d['product_id'])
;$availability=$this->inventory->checkAvailability($product,$job->branch_id,$d['quantity'])
;if(!$availability['sufficient']){return back()->with('error','Insufficient stock for '.$product->name.'. Available: '.$availability['available'].', Required: '.$d['quantity'])
;}$this->inventory->consume($product,$job->branch_id,$d['quantity'],$job->id)
;JobPart::create(['job_id'=>$job->id,'product_id'=>$d['product_id'],'quantity'=>$d['quantity'],'unit_price'=>$product->selling_price,'cost_price'=>$product->cost_price,'source'=>'inventory','approved'=>true])
;return back()->with('success','Part consumed.')
;} public function destroy(Job $job){$job->delete()
;return redirect()->route('jobs.index')->with('success','Job deleted.')
;}}
