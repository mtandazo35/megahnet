

let url = "interfaces.php"
fetch(url)
.then(response=>response.json())
.then(data=>mostrarData(data))
.catch(error =>console.log(error))
//console.log(response)

const mostrarData=(data)=>{
console.log(data)
 let datos = ""
 for (var i = 0; i < data.length; i++) {
datos +=` <tr>
<td> ${data[i].name} </td>



</tr>` 
 }

 document.getElementById("data").innerHTML=datos
}