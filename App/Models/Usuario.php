<?php

namespace App\Models;

use MF\Model\Model;

class Usuario extends Model {

  private $id;
  private $nome;
  private $email;
  private $senha;

  public function __get($atributo){
    return $this->$atributo;
  }
// 15:49
  public function __set($atributo, $valor){
    $this->$atributo = $valor;
  }

  //salvar
  public function salvar(){

    $query = "INSERT into usuarios(nome,email,senha)values(:nome,:email,:senha)";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nome', $this->__get('nome'));
    $stmt->bindValue(':email', $this->__get('email'));
    $stmt->bindValue(':senha', $this->__get('senha')); //md5 p/ criptogrfar -> hash 32 caracteres
    $stmt->execute();
    
    return $this;
  }

  //validar se um cadastro pode ser feito
  public function validarCadastro(){
    $valido = true;

    if(strlen($this->__get('nome')) < 3){
      $valido = false;
    }
    if(strlen($this->__get('email')) < 3){
      $valido = false;
    }
    if(strlen($this->__get('senha')) < 3){
      $valido = false;
    }

    return $valido;
  }

  //recuperar um usuario por email
  public function getUsuarioPorEmail(){
    $query = "select nome, email from usuarios where email = :email";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':email',$this->__get('email'));
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  public function autenticar(){
    $query = "select id, nome, email from usuarios where email = :email and senha = :senha";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':email', $this->__get('email'));
    $stmt->bindValue(':senha', $this->__get('senha'));
    $stmt->execute();

    $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

    if($usuario){
      if($usuario['id'] != '' && $usuario['nome'] != ''){
         $this->__set('id', $usuario['id']);
         $this->__set('nome', $usuario['nome']);
      }
  }

    return $this;
  }

  public function getAll(){
    $query = "SELECT 
      u.id, u.nome, u.email,
      (
        select 
          count(*)
        from
          usuarios_seguidores as us
        where
          us.id_usuario = :id_usuario and us.id_usuario_seguindo = u.id
      ) as seguindo_sn
      from 
        usuarios as u
      where 
        u.nome like :nome and u.id != :id_usuario
    ";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nome', '%'.$this->__get('nome').'%');
    $stmt->bindValue(':id_usuario', $this->__get('id'));
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);

  }

  public function seguirUsuario($id_usuario_seguindo){
    $query = 
    "INSERT into 
      usuarios_seguidores(id_usuario, id_usuario_seguindo)
    values
      (:id_usuario, :id_usuario_seguindo)";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario', $this->__get('id'));
    $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
    $stmt->execute();

    return true;
    // 19:15
  }
  
  public function deixarSeguirUsuario($id_usuario_seguindo){
    $query = 
    "DELETE from 
      usuarios_seguidores
    where
      id_usuario = :id_usuario and id_usuario_seguindo = :id_usuario_seguindo";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario', $this->__get('id'));
    $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
    $stmt->execute();

    return true;
  }

  //Informações do usuario
  public function getInfoUsuario(){
    $query = 
    "SELECT
      nome from usuarios where id = :id_usuario
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario',$this->__get('id'));
    $stmt->execute();

    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  
  //Total de Tweets
  public function getTotalTweets(){
    $query = 
    "SELECT
      count(*) as total_tweet from tweets where id_usuario = :id_usuario
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario',$this->__get('id'));
    $stmt->execute();

    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }
  
  //Total de usuarios que estamos seguindo
  public function getTotalSeguindo(){
    $query = 
    "SELECT
      count(*) as total_seguindo from usuarios_seguidores where id_usuario = :id_usuario
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario',$this->__get('id'));
    $stmt->execute();

    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  //Total de seguidores
  public function getTotalSeguidores(){
    $query = 
    "SELECT
      count(*) as total_seguidores from usuarios_seguidores where id_usuario_seguindo = :id_usuario
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_usuario',$this->__get('id'));
    $stmt->execute();

    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }
}
//bug 8:13 pra tras 
// 11:44
?>