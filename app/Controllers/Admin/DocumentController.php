<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\ClientModel;

class DocumentController extends BaseController
{
    protected $dm;

    public function __construct() { $this->dm = new DocumentModel(); }
    private function storageRoot(): string { return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR; }
    private function storagePath(string $relativePath): string { $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\')); return $this->storageRoot() . $relativePath; }

    public function index() {
        $documents=$this->db->table('documents')->select('documents.*, clients.name as client_name, projects.name as project_name')->join('clients','clients.id = documents.client_id','left')->join('projects','projects.id = documents.project_id','left')->orderBy('documents.created_at','DESC')->get()->getResultArray();
        return view('admin/documents/index',['title'=>'Documents','documents'=>$documents,'clients'=>(new ClientModel())->orderBy('name')->findAll()]);
    }

    public function upload() {
        $file=$this->request->getFile('file');
        if(!$file || !$file->isValid()) return redirect()->back()->with('error','Invalid file.');
        if(!$this->validate(['file'=>['rules'=>'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,png,jpg,jpeg,xlsx,xls,csv,txt,zip]','errors'=>['ext_in'=>'File type not allowed.','max_size'=>'Max file size is 10MB.']]])) return redirect()->back()->with('error',$this->validator->getError('file'));

        $clientId=(int)$this->request->getPost('client_id'); $projectId=(int)$this->request->getPost('project_id');
        if($clientId && !(new ClientModel())->find($clientId)) return redirect()->back()->with('error','Selected client does not exist.');
        if($projectId){$project=$this->db->table('projects')->where('id',$projectId)->where('client_id',$clientId)->get()->getRowArray(); if(!$project)return redirect()->back()->with('error','Selected project does not belong to the selected client.');}

        $originalName=$file->getClientName(); $mimeType=$file->getClientMimeType(); $size=$file->getSize(); $newName=$file->getRandomName();
        $relativeDirectory='documents/'.date('Y/m/'); $folder=$this->storageRoot().str_replace('/',DIRECTORY_SEPARATOR,$relativeDirectory);
        if(!is_dir($folder)&&!mkdir($folder,0755,true)&&!is_dir($folder))return redirect()->back()->with('error','Unable to create secure document storage directory.');
        try{$file->move($folder,$newName);}catch(\Throwable $e){log_message('error','Document upload failed: {message}',['message'=>$e->getMessage()]);return redirect()->back()->with('error','Unable to store the uploaded document.');}
        $storedPath=$relativeDirectory.$newName;
        if(!$this->dm->insert(['client_id'=>$clientId?:null,'project_id'=>$projectId?:null,'category'=>$this->request->getPost('category')?:'other','title'=>$this->request->getPost('title')?:$originalName,'file_name'=>$originalName,'file_path'=>$storedPath,'file_size'=>$size,'file_type'=>$mimeType,'notes'=>$this->request->getPost('notes')?:'','created_by'=>session()->get('user_id')])){@unlink($folder.DIRECTORY_SEPARATOR.$newName);return redirect()->back()->with('error','Unable to save document information.');}
        return redirect()->back()->with('success','Document uploaded successfully.');
    }

    public function download($id) {
        $doc=$this->dm->find((int)$id); if(!$doc)return redirect()->back()->with('error','Document not found.');
        $path=$this->storagePath($doc['file_path']); $storageRoot=realpath($this->storageRoot()); $realPath=realpath($path);
        if(!$storageRoot||!$realPath||!is_file($realPath)||!str_starts_with($realPath,$storageRoot.DIRECTORY_SEPARATOR))return redirect()->back()->with('error','File no longer exists on the server.');
        return $this->response->download($realPath,null)->setFileName($doc['file_name']);
    }

    public function delete($id) {
        $doc=$this->dm->find((int)$id); if(!$doc)return $this->jsonError('Document not found.');
        $path=$this->storagePath($doc['file_path']); if(is_file($path))@unlink($path); $this->dm->delete((int)$id); $this->logActivity('documents',(int)$id,'delete',"Deleted: {$doc['file_name']}"); return $this->jsonSuccess('Document deleted.');
    }
}