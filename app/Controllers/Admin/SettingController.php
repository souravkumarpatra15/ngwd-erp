<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\SettingModel;

class SettingController extends BaseController
{
    public function index() {
        return view('admin/settings/index', ['title'=>'Settings','settings'=>$this->settings]);
    }
    public function save($group) {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        if ($group === 'company') {
            $logo = $this->request->getFile('company_logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                $name = 'company_logo.'.$logo->getExtension();
                $logo->move(FCPATH.'assets/images/', $name, true);
                $data['company_logo'] = 'assets/images/'.$name;
            } else unset($data['company_logo']);
        }
        (new SettingModel())->saveGroup($group, $data);
        return redirect()->to('admin/settings#'.$group)->with('success', ucfirst($group).' settings saved!');
    }
}
