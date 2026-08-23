<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/** User management for privileged ERP users. */
class UserManagementController extends BaseController
{
    protected UserModel $um;

    public function __construct() { $this->um = new UserModel(); }
    private function currentRole(): string { return (string) session()->get('user_role'); }
    private function isSuperadmin(): bool { return $this->currentRole() === 'superadmin'; }
    private function canManageUser(array $user): bool { return $this->isSuperadmin() || ($user['role'] !== 'superadmin' && in_array($this->currentRole(), ['admin','manager'], true)); }
    private function activeSuperadmins(): int { return (int) $this->um->where('role','superadmin')->where('is_active',1)->countAllResults(); }

    public function index()
    {
        $query=$this->um->whereIn('role',['superadmin','admin','manager']);
        if (!$this->isSuperadmin()) $query->where('role !=','superadmin');
        return view('admin/users/index',['title'=>'User Management','users'=>$query->orderBy('name','ASC')->findAll()]);
    }

    public function create()
    {
        $roles=['admin'=>'Admin','manager'=>'Manager'];
        if ($this->isSuperadmin()) $roles=['superadmin'=>'Super Admin']+$roles;
        return view('admin/users/create',['title'=>'Add User','roles'=>$roles]);
    }

    public function store()
    {
        $role=trim((string)$this->request->getPost('role'));
        if ($role==='superadmin' && !$this->isSuperadmin()) return redirect()->back()->withInput()->with('error','Only a superadmin can create another superadmin.');
        $rules=['name'=>'required|min_length[2]|max_length[100]','email'=>'required|valid_email','role'=>'required|in_list[superadmin,admin,manager]','password'=>'required|min_length[8]','password_confirm'=>'required|matches[password]'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $email=strtolower(trim((string)$this->request->getPost('email')));
        if ($this->um->where('email',$email)->countAllResults()>0) return redirect()->back()->withInput()->with('error','Email address is already registered.');
        $active=$this->request->getPost('is_active');
        if (!$this->um->insert(['name'=>trim((string)$this->request->getPost('name')),'email'=>$email,'role'=>$role,'password'=>password_hash((string)$this->request->getPost('password'),PASSWORD_DEFAULT),'is_active'=>$active===null?1:(int)$active])) return redirect()->back()->withInput()->with('error','Unable to create user.');
        $id=(int)$this->um->getInsertID();
        $this->logActivity('users',$id,'create','Created user: '.$email);
        return redirect()->to('admin/users')->with('success','User created successfully!');
    }

    public function edit($id)
    {
        $user=$this->um->find((int)$id);
        if (!$user||!$this->canManageUser($user)) return redirect()->to('admin/users')->with('error','User not found or access denied.');
        $roles=['admin'=>'Admin','manager'=>'Manager']; if ($this->isSuperadmin()) $roles=['superadmin'=>'Super Admin']+$roles;
        return view('admin/users/edit',['title'=>'Edit User','user'=>$user,'roles'=>$roles]);
    }

    public function update($id)
    {
        $id=(int)$id; $user=$this->um->find($id);
        if (!$user||!$this->canManageUser($user)) return redirect()->to('admin/users')->with('error','User not found or access denied.');
        $isSelf=$id===(int)session()->get('user_id');
        $requestedActive=$this->request->getPost('is_active');
        if ($isSelf && $requestedActive==='0') return redirect()->back()->withInput()->with('error','You cannot deactivate your own account.');
        $role=trim((string)$this->request->getPost('role'));
        if ($role==='superadmin'&&!$this->isSuperadmin()) return redirect()->back()->withInput()->with('error','Only a superadmin can assign the superadmin role.');
        if ($user['role']==='superadmin'&&$role!=='superadmin'&&!$this->isSuperadmin()) return redirect()->back()->withInput()->with('error','Only a superadmin can change a superadmin role.');
        $effectiveActive=$requestedActive===null?(int)$user['is_active']:(int)$requestedActive;
        if ($user['role']==='superadmin'&&(int)$user['is_active']===1&&($role!=='superadmin'||$effectiveActive===0)&&$this->activeSuperadmins()<=1) return redirect()->back()->withInput()->with('error','At least one active superadmin must remain.');
        $email=strtolower(trim((string)$this->request->getPost('email')));
        $rules=['name'=>'required|min_length[2]|max_length[100]','email'=>'required|valid_email','role'=>'required|in_list[superadmin,admin,manager]'];
        if ($this->request->getPost('password')) {$rules['password']='min_length[8]';$rules['password_confirm']='matches[password]';}
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        if ($this->um->where('email',$email)->where('id !=',$id)->countAllResults()>0) return redirect()->back()->withInput()->with('error','Email address is already registered.');
        $data=['name'=>trim((string)$this->request->getPost('name')),'email'=>$email,'role'=>$role,'is_active'=>$effectiveActive];
        if ($this->request->getPost('password')) $data['password']=password_hash((string)$this->request->getPost('password'),PASSWORD_DEFAULT);
        if (!$this->um->update($id,$data)) return redirect()->back()->withInput()->with('error','Unable to update user.');
        $this->logActivity('users',$id,'update','Updated user: '.$email);
        return redirect()->to('admin/users')->with('success','User updated successfully!');
    }

    public function delete($id)
    {
        $id=(int)$id; if ($id===(int)session()->get('user_id')) return $this->jsonError('You cannot delete your own account.');
        $user=$this->um->find($id); if (!$user||!$this->canManageUser($user)) return $this->jsonError('User not found or access denied.');
        if ($user['role']==='superadmin'&&!$this->isSuperadmin()) return $this->jsonError('Only a superadmin can delete a superadmin.');
        if ($user['role']==='superadmin'&&(int)$user['is_active']===1&&$this->activeSuperadmins()<=1) return $this->jsonError('At least one active superadmin must remain.');
        if (!$this->um->delete($id)) return $this->jsonError('Unable to delete user.');
        $this->logActivity('users',$id,'delete','Deleted user: '.$user['email']); return $this->jsonSuccess('User deleted.');
    }

    public function toggleActive($id)
    {
        $id=(int)$id; $user=$this->um->find($id); if (!$user||!$this->canManageUser($user)) return $this->jsonError('User not found or access denied.');
        if ($id===(int)session()->get('user_id')) return $this->jsonError('You cannot deactivate your own account.');
        if ($user['role']==='superadmin'&&!$this->isSuperadmin()) return $this->jsonError('Only a superadmin can change a superadmin account.');
        if ($user['role']==='superadmin'&&(int)$user['is_active']===1&&$this->activeSuperadmins()<=1) return $this->jsonError('At least one active superadmin must remain.');
        $newStatus=$user['is_active']?0:1;
        if (!$this->um->update($id,['is_active'=>$newStatus])) return $this->jsonError('Unable to update user status.');
        $this->logActivity('users',$id,'status',($newStatus?'Activated: ':'Deactivated: ').$user['email']); return $this->jsonSuccess($newStatus?'User activated.':'User deactivated.');
    }
}