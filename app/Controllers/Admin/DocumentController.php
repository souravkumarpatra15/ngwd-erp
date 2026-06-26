<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\DocumentModel;

class DocumentController extends BaseController
{
    protected $dm;
    public function __construct() { $this->dm = new DocumentModel(); }

    public function index() {
        return view('admin/documents/index', ['title'=>'Documents','documents'=>$this->dm->orderBy('created_at','DESC')->findAll()]);
    }

    public function upload() {
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error','Invalid file');
        $rules = ['file'=>['rules'=>'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,png,jpg,jpeg,xlsx,xls,csv,txt,zip]','errors'=>['ext_in'=>'File type not allowed']]];
        if (!$this->validate($rules)) return redirect()->back()->with('error', $this->validator->getError('file'));
        $newName  = $file->getRandomName();
        $folder   = FCPATH.'uploads/'.date('Y/m/');
        $file->move($folder, $newName);
        $this->dm->insert(['client_id'=>$this->request->getPost('client_id')?:null,'project_id'=>$this->request->getPost('project_id')?:null,'category'=>$this->request->getPost('category')??'other','title'=>$this->request->getPost('title'),'file_name'=>$file->getClientName(),'file_path'=>'uploads/'.date('Y/m/').$newName,'file_size'=>$file->getSize(),'file_type'=>$file->getMimeType(),'created_by'=>session()->get('user_id')]);
        return redirect()->back()->with('success','File uploaded!');
    }

    public function download($id) {
        $doc = $this->dm->find($id);
        if (!$doc) return redirect()->back();
        $path = FCPATH.$doc['file_path'];
        if (!file_exists($path)) return redirect()->back()->with('error','File not found');
        return $this->response->download($path, null)->setFileName($doc['file_name']);
    }

    public function delete($id) { $this->dm->delete($id); return $this->jsonSuccess('Deleted'); }
}
