<?php

namespace Usuario\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Usuario\Form\TipoUsuario as formTipoUsuario;
use Usuario\Form\VincularRecurso as formRecurso;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

class TipousuarioController extends AbstractActionController
{

    public function indexAction()
    {

        $serviceTipousuario = $this->getServiceLocator()->get('UsuarioTipo');
        $tiposUsuario = $serviceTipousuario->fetchAll();
        
        $Paginator = new Paginator(new ArrayAdapter($tiposUsuario->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);
        
        return new ViewModel(array(
                                'tiposUsuario'      => $Paginator
                            ));
    }

    public function novoAction()
    { 
    	$formTipo = new formTipoUsuario();
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceTipoUsuario = $this->getServiceLocator()->get('UsuarioTipo');

            //validar form
            $formTipo->setData($dados);
            if($formTipo->isValid()){
                $result = $serviceTipoUsuario->insert($formTipo->getData());
                if($result){
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Tipo de usuário inserido com sucesso!');                
                    return $this->redirect()->toRoute('tipousuarioAlterar', array('id' => $result));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir tipo de usuário!'); 
                }
            }

        }

    	return new ViewModel(array('formTipo' => $formTipo));
    }

    public function alterarAction(){
        //Pesquisar cliente
        $idTipo = $this->params()->fromRoute('id');
        $serviceTipoUsuario = $this->getServiceLocator()->get('UsuarioTipo');
        $tipoUsuario = $serviceTipoUsuario->getRecordFromArray(array('id' => $idTipo));
        //Popular form
        $formTipo = new formTipoUsuario();
        $formTipo->setData($tipoUsuario);

        $formRecurso = new formRecurso('formRecurso', $this->getServiceLocator());
        $formRecurso->setData(array('usuario_tipo' => $tipoUsuario->id));
        $serviceUsuarioRecurso = $this->getServiceLocator()->get('UsuarioRecurso');
        //pesquisa recurso e popula form
        $usuarioRecurso = $this->params()->fromRoute('recurso');
        if($usuarioRecurso){
            $usuarioRecurso = $serviceUsuarioRecurso->getRecordFromArray(array('id' => $usuarioRecurso));
            $formRecurso->setData($usuarioRecurso);
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->recurso)){
                $formRecurso->setData($dados);
                //Unidade
                if($formRecurso->isValid()){                
                    if($usuarioRecurso){
                        //UPDATE
                        $res = $serviceUsuarioRecurso->update($formRecurso->getData(), array('id' => $usuarioRecurso->id));
                        if($res){
                            $this->flashMessenger()->addSuccessMessage('Recurso alterado com sucesso!');
                        }else{
                            $this->flashMessenger()->addErrorMessage('Erro ao alterar recurso!');
                        }
                        return $this->redirect()->toRoute('tipousuarioAlterar', array(
                                                                                    'id' => $tipoUsuario->id, 
                                                                                    'recurso' => $usuarioRecurso->id
                                                                                ));
                    }else{
                        //INSERIR
                        $res = $serviceUsuarioRecurso->save($formRecurso->getData());
                        if($res){
                            $this->flashMessenger()->addSuccessMessage('Recurso vinculado com sucesso!');
                            return $this->redirect()->toRoute('tipousuarioAlterar', array('id' => $tipoUsuario->id));
                        }
                        $this->flashMessenger()->addErrorMessage('Erro ao vincular recurso!');
                        return $this->redirect()->toRoute('tipousuarioAlterar', array('id' => $tipoUsuario->id));
                    }
                    
                }
            }else{
                if(isset($dados->perfil)){
                    //tipo usuário
                   $serviceTipoUsuario->update($dados->toArray(), array('id'  =>  $tipoUsuario->id));
                   $this->flashMessenger()->addSuccessMessage('Tipo de usuário alterado com sucesso!'); 
                   return $this->redirect()->toRoute('tipousuarioAlterar', array('id' => $tipoUsuario->id));
                }
            }
        }

        //Pesquisar unidades
        $recursosVinculados = $serviceUsuarioRecurso->getRecursosByTipoRecurso(array('usuario_tipo' => $tipoUsuario->id));
        
    	return new ViewModel(array(
                                'formTipo'          => $formTipo,
                                'tipoUsuario'       => $tipoUsuario,
                                'formRecurso'       => $formRecurso,
                                'recursos'          => $recursosVinculados
                                )
                            );
    }

    public function deletartipousuarioAction(){
        $serviceTipoUsuario = $this->getServiceLocator()->get('UsuarioTipo');
        $serviceTipoUsuario->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Tipo de usuário excluído com sucesso!');
        return $this->redirect()->toRoute('tipousuario');
    }

    public function deletarrecursoAction(){
        $serviceRecursoUsuario = $this->getServiceLocator()->get('UsuarioRecurso');
        $serviceRecursoUsuario->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Recurso desvinculado com sucesso!');
        return $this->redirect()->toRoute(
                                    'tipousuarioAlterar', 
                                    array('id' => $this->params()->fromRoute('tipousuario'))
                                );
    }

    public function descricaorecursoAction(){
        $recurso = $this->getRequest()->getPost('recurso');
        $recurso = $this->getServiceLocator()->get('Recurso')->getRecord($recurso);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('recurso' => $recurso));

        return $view;
    }

    public function moduloAction()
    {   
        $params = $this->getRequest()->getPost();
        //instanciar form
        $formRecurso = new formRecurso('formRecurso', $this->getServiceLocator());
        $modulo = $formRecurso->setRecursosByModulo($params->modulo);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('modulo' => $modulo));
        return $view;
    }



}

