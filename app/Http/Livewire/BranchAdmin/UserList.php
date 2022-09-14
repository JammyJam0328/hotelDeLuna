<?php

namespace App\Http\Livewire\BranchAdmin;

use App\Models\Role;
use App\Models\User;
use App\Models\RoomBoy;
use Livewire\Component;
use WireUi\Traits\Actions;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination, Actions;
    public $search='',$filter='all';
    public $roles=[];
    public $showModal=false;
    public $mode='create';
    public $name,$email,$password,$role_id;
    public $edit_id=null;
    public function getModeTitle()
    {
        return $this->mode=='create' ? 'Create User' : 'Update User';
    }
    public function add()
    {
        $this->reset('name', 'email', 'password', 'role_id');
        $this->mode='create';
        $this->showModal=true;
    }
    public function edit($edit_id)
    {
        $this->mode = 'update';
        $this->edit_id = $edit_id;
        $user = User::find($edit_id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->showModal = true;
    }
    public function save()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$this->edit_id,
            'password' => $this->mode=='create' ? 'required' : 'nullable',
            'role_id' => 'required',
        ]);
        if ($this->mode=='create') {
            $this->create();
        }else{
            $this->update();
        }
    }
    public function create()
    {
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' =>  bcrypt($this->password),
            'role_id' => $this->role_id,
            'branch_id' => auth()->user()->branch_id,
        ]);
        if ($this->role_id == 5) {
            RoomBoy::create([
                'user_id' => $user->id,
            ]);
        }
        $this->showModal = false;
        $this->reset('name','email','role_id');
        $this->notification()->success(
            $title = 'Success',
            $description = 'User created successfully',
        );
    }
    public function update()
    {
        User::find($this->edit_id)->update([
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ]);
        $this->showModal = false;
        $this->reset('name','email','role_id');
        $this->notification()->success(
            $title = 'Success',
            $description = 'User updated successfully',
        );
    }
    public function mount()
    {
        $this->roles = Role::where('id','!=',7)->get();
    }
    public function render()
    {
        return view('livewire.branch-admin.user-list',[
            'users'=>User::query()
                    ->where('branch_id',auth()->user()->branch_id)
                    ->when($this->search!='',function($query){
                        $query->where('name','like','%'.$this->search.'%')
                            ->orWhere('email','like','%'.$this->search.'%');
                    })
                    ->when($this->filter!='all',function($query){
                        $query->where('role_id',$this->filter);
                    })
                    ->paginate(10)
        ]);
    }
}