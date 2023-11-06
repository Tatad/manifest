<!DOCTYPE html>
<html>
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <head>
    <title>Sign-In or Enroll via Face Recognition</title>
  </head>
  <body>
    <input type="email" id="email">
    <button onclick="enrollNewUser()">Enroll New User</button>
    <button onclick="authenticateUser()">Authenticate User</button>
    <div id="faceio-modal"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://cdn.faceio.net/fio.js"></script>
    <script type="text/javascript">
        // Instantiate fio.js with your application Public ID
        const faceio = new faceIO("fioa8bba");
        
        function enrollNewUser(){
            let request;
            // console.log($('#email').val());
           // Start the facial enrollment process
            faceio.enroll({
                "locale": "auto", // Default user locale
                "payload": {
                /* The payload we want to associate with this user
                * which is forwarded back to us upon future
                * authentication of this particular user.*/
                "email": $('#email').val()
                }
            }).then(userInfo => {
                // User Successfully Enrolled!
                // console.log("Hooray, it worked!");
                //         alert(
                //         `User Successfully Enrolled! Details:
                //         Unique Facial ID: ${userInfo.facialId}
                //         Enrollment Date: ${userInfo.timestamp}
                //         Gender: ${userInfo.details.gender}
                //         Age Approximation: ${userInfo.details.age}`
                //     );

                request = $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "/account",
                    type: "post",
                    data: {
                        'name' : 'manifest user',
                        'email': $('#email').val(),
                        "faceio_id": userInfo.facialId,
                        'password' : 'test12345'
                    }
                });

                request.done(function (response, textStatus, jqXHR){
                    window.location.replace("/manifest");
                });


                
                // handle success, save the facial ID, redirect to dashboard...
            }).catch(errCode => {
                // handle enrollment failure. Visit:
                // https://faceio.net/integration-guide#error-codes
                // for the list of all possible error codes
            })
           
        }
        function authenticateUser(){
           // Authenticate a previously enrolled user
            let request;
            faceio.authenticate({
                "locale": "auto" // Default user locale
            }).then(userData => {
                console.log("Success, user identified")
                // Grab the facial ID linked to this particular user which will be same
                // for each of his successful future authentication. FACEIO recommend 
                // that your rely on this Facial ID if you plan to uniquely identify 
                // all enrolled users on your backend for example.
                console.log("Linked facial Id: " + userData.facialId)
                // Grab the arbitrary data you have already linked (if any) to this particular user
                // during his enrollment via the payload parameter of the enroll() method.
                console.log("Payload: " + JSON.stringify(userData.payload)) 
                // {"whoami": 123456, "email": "john.doe@example.com"} set via enroll()

                request = $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "/authenticate",
                    type: "post",
                    data: {
                        "faceio_id": userData.facialId,
                    }
                });
                console.log('test')
                request.done(function (response, textStatus, jqXHR){
                    // Log a message to the console
                    // console.log("Hooray, it worked!");
                    //     alert(
                    //     `User Successfully Enrolled! Details:
                    //     Unique Facial ID: ${userInfo.facialId}
                    //     Enrollment Date: ${userInfo.timestamp}
                    //     Gender: ${userInfo.details.gender}
                    //     Age Approximation: ${userInfo.details.age}`
                    // );
                    window.location.replace("/manifest");
                });
            }).catch(errCode => {
                // handle authentication failure. Visit:
                // https://faceio.net/integration-guide#error-codes
                // for the list of all possible error codes
            })
        }
    </script>
  </body>
</html>