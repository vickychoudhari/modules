var editButton=document.getElementById('miniorange_2fa_edit_backdoor');
if(editButton!=null)
{
    editButton.onclick = function() {
  let v=document.getElementById('miniorange_2fa_backdoor_table');
  v.hidden=!v.hidden;
  if(v.hidden)
  {
    var save_btn=document.getElementById('miniorange_2fa_save_config_btn');
    save_btn.click();
    document.getElementById('miniorange_2fa_edit_backdoor').innerHTML='Edit';
  }
  else
    document.getElementById('miniorange_2fa_edit_backdoor').innerHTML='Save';
  }
}
var editBox=document.getElementById('miniorange_2fa_backdoor_textbox1');
if(editBox!=null)
editBox.onkeyup = function() {
  let backdoor_textbox=document.getElementById('miniorange_2fa_backdoor_textbox1');
  let base_url=document.getElementById('miniorange_2fa_backdoor_base_url_to_append');
  let backdoor_url=document.getElementById('miniorange_2fa_backdoor_url');
  backdoor_url.innerText=base_url.innerText+backdoor_textbox.value;
}
